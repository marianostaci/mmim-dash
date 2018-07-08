<?php

/**
 * This is the model class for table "daysOnHand".
 *
 * The followings are the available columns in table 'daysOnHand':
 * @property string $practiceID
 * @property string $packageID
 * @property string $packageName
 * @property integer $days
 */
class DaysOnHand extends CActiveRecord
{
    const CRON_LAST_RUN_FILE = 'cron_days_on_hand_last_run.txt';
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return DaysOnHand the static model class
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
		return 'daysOnHand';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('locationID, practiceID, packageID, packageName, days', 'required'),
			array('days, locationID, practiceID, packageID', 'numerical', 'integerOnly'=>true),
			array('practiceID, packageID', 'length', 'max'=>10),
			array('packageName', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('practiceID, packageID, packageName, days', 'safe', 'on'=>'search'),
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
			'packageID' => 'Package',
			'packageName' => 'Package Name',
			'days' => 'Days',
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

		$criteria->compare('practiceID',$this->practiceID,true);
		$criteria->compare('packageID',$this->packageID,true);
		$criteria->compare('packageName',$this->packageName,true);
		$criteria->compare('days',$this->days);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    public static function setData(){
        $connection=Yii::app()->db;
        $transaction=$connection->beginTransaction();
        try{
            $from = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d')-90, date('Y')));
            //Get total quantity dispense units for last 90 days
            $command = $connection->createCommand()
                ->select ('SUM(t.quantity) AS unitsDispensed, p.name, pk.id as packageID, o.practiceID AS practiceID, o.updated, p.id as productID')
                ->from ('lineItem t')
                ->join ('order o', 't.orderID = o.id')
                //->join ('stock s', 't.stockID = s.id')
                ->join ('product p', 't.productID = p.id')
                ->join ('package pk', 'p.packageID = pk.id')
                ->where ('o.orderStatus IN(:orderPaid, :orderCompleted) AND o.updated >= :from AND o.type = :patient')
                ->group ('o.practiceID, pk.id')
                ->having('unitsDispensed > 0');

            $param[':from'] = $from;
            $param[':orderPaid'] = Order::STATUS_PAYED;
            $param[':orderCompleted'] = Order::STATUS_COMPLETED;
            $param[':patient'] = Order::TYPE_PATIENT;

            $command->bindValues($param);
            $dbResults = $command->queryAll ();
            //$command->reset();

            $results = array();
            foreach( $dbResults as $item ){
                $results[ $item['practiceID'] ][] = $item;
            }

            // drop table data
            //$connection->createCommand( "TRUNCATE TABLE `daysOnHand`" )->execute();
            $connection->createCommand( "DELETE FROM `daysOnHand`" )->execute();

            //Get dispense units quantity per day and package total stock and save to DB
            $insertSql = "INSERT INTO daysOnHand(practiceID, packageID ,packageName, days, unitsDispensed, productID ) VALUES ( :practiceID, :packageID,:packageName, :days, :unitsDispensed, :productID )";
            $insertCommand=$connection->createCommand($insertSql);
            foreach ($results as $practiceID => $packages) {
                foreach( $packages as $package ){
                    $totalPackageStock = Product::model()->getProductTotalStock ($package['practiceID'], $package['packageID'], false);
                    if( $totalPackageStock > 0 ){
                        //if(!empty($package['unitsDispensed']) && abs($package['unitsDispensed']) != 0) {
                            $unitsDispensedPerDay = $package['unitsDispensed'] / 90;
                            $days = ceil($totalPackageStock/$unitsDispensedPerDay);
                        /*} else {
                            $unitsDispensedPerDay = 0;
                            $days = $totalPackageStock;
                        } */



                        $insertCommand->bindParam(":practiceID",$package['practiceID'],PDO::PARAM_INT);
                        $insertCommand->bindParam(":packageID",$package['packageID'],PDO::PARAM_INT);
                        $insertCommand->bindParam(":productID",$package['productID'],PDO::PARAM_INT);
                        $insertCommand->bindParam(":packageName",$package['name'],PDO::PARAM_STR);
                        $insertCommand->bindParam(":days",$days, PDO::PARAM_INT);
                        $insertCommand->bindParam(":unitsDispensed", $package['unitsDispensed'], PDO::PARAM_STR);
                        $insertCommand->execute();
                    }

                }
            }
            $transaction->commit();
            file_put_contents( Yii::getPathOfAlias( 'application.runtime' ) . '/' . self::CRON_LAST_RUN_FILE, date( 'Y-m-d H:i:s' ));
            return TRUE;
        }
        catch(Exception $e){

            //Log error
            $errorLog = new ErrorLog();
            $errorLog->model = 'Days On Hand';
            $errorLog->error = $e;
            $errorLog->save();

            $transaction->rollback();
            return FALSE;
        }
    }

    public static function getData(){
        $practiceID = Yii::app ()->user->model->practiceID;
        $connection=Yii::app()->db;
        $sql = "SELECT * FROM daysOnHand WHERE practiceID = :practiceID ORDER BY unitsDispensed DESC LIMIT 10";
        $dependency = new CFileCacheDependency( Yii::getPathOfAlias( 'application.runtime' ) . '/' . self::CRON_LAST_RUN_FILE );
        $command = $connection->cache( 3600, $dependency )->createCommand($sql);

        $command->bindParam(":practiceID", $practiceID, PDO::PARAM_INT);
        $results = $command->queryAll();
        return $results;


    }
}