<?php

/**
 * This is the model class for table "totalSalesByProduct".
 *
 * The followings are the available columns in table 'totalSalesByProduct':
 * @property integer $practiceID
 * @property string $productName
 * @property string $totalSales30
 * @property string $totalSales60
 * @property string $totalReturn30
 * @property string $totalReturn60
 */
class SalesByProduct extends CActiveRecord
{
    const CRON_LAST_RUN_FILE = 'cron_sales_by_product_last_run.txt';

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return TotalSalesByProduct the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'salesByProduct';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('practiceID, productName', 'required'),
			array('practiceID', 'numerical', 'integerOnly'=>true),
			array('productName', 'length', 'max'=>255),
			array('totalSales30, totalSales60, totalReturn30, totalReturn60', 'length', 'max'=>11),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('practiceID, productName, totalSales30, totalSales60, totalReturn30, totalReturn60', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'practiceID' => 'Practice',
			'productID' => 'Product',
			'productName' => 'Product Name',
			'totalSales30' => 'Total Sales30',
			'totalSales60' => 'Total Sales60',
			'totalReturn30' => 'Total Return30',
			'totalReturn60' => 'Total Return60',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('practiceID',$this->practiceID);
		$criteria->compare('productName',$this->productName,true);
		$criteria->compare('totalSales30',$this->totalSales30,true);
		$criteria->compare('totalSales60',$this->totalSales60,true);
		$criteria->compare('totalReturn30',$this->totalReturn30,true);
		$criteria->compare('totalReturn60',$this->totalReturn60,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    public static function setData(){
        $connection = Yii::app()->db;
        $transaction=$connection->beginTransaction();
        try{
            $last_30_days_results = self::getSales( 30 );
            $last_60_days_results = self::getSales( 60 );

            $sales = array();
            foreach( $last_30_days_results as $result ){
                $sales[ $result[ 'practiceID' ] ][ $result[ 'productID' ] ][30] = $result;

            }

            foreach( $last_60_days_results as $result ){
                $sales[ $result[ 'practiceID' ] ][ $result[ 'productID' ] ][60] = $result;
            }



            // drop table data
            //$connection->createCommand( "TRUNCATE TABLE `salesByProduct`" )->execute();
            $connection->createCommand( "DELETE FROM `salesByProduct`" )->execute();

            //save to DB
            $insertSql = "INSERT INTO salesByProduct(practiceID, productID , packageID  ,  productName, totalSales30, totalSales60, totalReturn30, totalReturn60, vendorID ) VALUES ( :practiceID, :productID, :packageID,:productName, :totalSales30, :totalSales60, :totalReturn30, :totalReturn60, :vendorID )";
            $insertCommand=$connection->createCommand($insertSql);

            $packages = array();
            foreach ($sales as $practiceID => $products) {
                foreach( $products as $productID => $product ){

                    $sales30 = isset( $product[30]['totalSales'] )? $product[30]['totalSales'] : 0;
                    $sales60 = isset( $product[60]['totalSales'] )? $product[60]['totalSales'] : 0;

                    $return30 = isset( $product[30]['totalReturn'] )? $product[30]['totalReturn'] : 0;
                    $return60 = isset( $product[60]['totalReturn'] )? $product[60]['totalReturn'] : 0;

                    $productName = isset( $product[ 30 ][ 'productName' ] ) ? $product[ 30 ][ 'productName' ] : ( isset( $product[ 60 ][ 'productName' ] ) ? $product[ 60 ][ 'productName' ] : '' );
                    $packageID = isset( $product[ 30 ][ 'packageID' ] ) ? $product[ 30 ][ 'packageID' ] : ( isset( $product[ 60 ][ 'packageID' ] ) ? $product[ 60 ][ 'packageID' ] : NULL );
	                $vendorID = isset( $product[ 30 ][ 'vendorID' ] ) ? $product[ 30 ][ 'vendorID' ] : ( isset( $product[ 60 ][ 'vendorID' ] ) ? $product[ 60 ][ 'vendorID' ] : NULL );

                    $insertCommand->bindParam(":practiceID", $practiceID,PDO::PARAM_INT);
                    $insertCommand->bindParam(":productID", $productID,PDO::PARAM_INT);
                    $insertCommand->bindParam(":packageID", $packageID, PDO::PARAM_INT);
                    $insertCommand->bindParam(":productName", $productName, PDO::PARAM_STR);
                    $insertCommand->bindParam(":totalSales30", $sales30);
                    $insertCommand->bindParam(":totalSales60", $sales60);
                    $insertCommand->bindParam(":totalReturn30", $return30);
                    $insertCommand->bindParam(":totalReturn60", $return60);
                    $insertCommand->bindParam(":vendorID", $vendorID);
                    $insertCommand->execute();
                }
            }
            $transaction->commit();
            file_put_contents( Yii::getPathOfAlias( 'application.runtime' ) . '/' . self::CRON_LAST_RUN_FILE, date( 'Y-m-d H:i:s' ));
            return TRUE;
        }
        catch(Exception $e) // an exception is raised if a query fails
        {
            $transaction->rollback();
            return FALSE;
        }
    }

    public static function getData($practiceID = FALSE, $vendor = FALSE){
	    if($practiceID) $practiceID = Yii::app ()->user->model->practiceID;
        $connection=Yii::app()->db;

        $sql = "SELECT s.practiceID, s.packageID, v.name AS vendorName, s.vendorID, s.productID, s.productName, SUM(s.totalSales30 ) AS totalSales30, SUM(s.totalSales60 ) AS totalSales60, SUM(s.totalReturn30 ) AS totalReturn30, SUM(s.totalReturn60 ) AS totalReturn60 FROM salesByProduct s
        LEFT JOIN practice v ON s.vendorID = v.id AND v.type = 'vendor' ";
	    if($practiceID) $sql.= " WHERE practiceID = :practiceID";
	    if($vendor) $sql.= " GROUP BY vendorID ORDER BY totalSales30  DESC LIMIT 10;";
	    else $sql.= " GROUP BY packageID ORDER BY totalSales30  DESC LIMIT 10;";

        $dependency = new CFileCacheDependency( Yii::getPathOfAlias( 'application.runtime' ) . '/' . self::CRON_LAST_RUN_FILE );
        $command = $connection->cache( 3600, $dependency )->createCommand($sql);

        if($practiceID) $command->bindParam(":practiceID", $practiceID, PDO::PARAM_INT);
        $results = $command->queryAll();
        return $results;
    }

    public static function getSales( $days = 30 ){
        if( $days == 30 ){
            $fromDate = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d')-30, date('Y')));
            $toDate = date( 'Y-m-d H:i:s' );

        }

        if( $days == 60 ){
            $fromDate = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d')-60, date('Y')));
            $toDate = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d')-31, date('Y')));

        }

        $connection=Yii::app()->db;

        $command = $connection->createCommand()
            ->select('p.name AS productName,
                      pV.vendorID as vendorID,
                      p.id AS productID,
                      p.packageID AS packageID,
                      o.practiceID as practiceID,
                      SUM(IF(o.orderStatus=:orderStatusReturned, 0, t.lineTotal)) AS totalSales,
		              SUM(IF(o.orderStatus=:orderStatusReturned, t.lineTotal, 0)) AS totalReturn
                     ')
            ->from('lineItem t')
            ->join('order o', 't.orderID=o.id')
            ->leftJoin('product p', 't.productID = p.id')
            ->leftJoin('package pk', 'p.packageID = pk.id')
            ->leftJoin('preferredPackageVendor pV', 'pk.id = pV.packageID AND p.practiceID = pV.practiceID')
            ->group('practiceID, productID')
            ->where('(o.orderStatus=:orderStatusReturned OR o.orderStatus=:orderStatusPaid OR o.orderStatus=:orderStatusCompleted) AND o.dispenseOn BETWEEN :fromDate AND :toDate AND t.productID IS NOT NULL AND p.packageID IS NOT NULL');

        $commandParams = array(
            ':orderStatusReturned' => 'returned',
            ':orderStatusPaid'     => 'paid',
            ':orderStatusCompleted' => 'completed',
            ':fromDate' => $fromDate,
            ':toDate' => $toDate,
        );
        $command->bindValues($commandParams);

        $dbResults = $command->queryAll();
        $command->reset();
        return $dbResults;
    }
}