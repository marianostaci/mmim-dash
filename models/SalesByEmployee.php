<?php

/**
 * This is the model class for table "salesByEmployee".
 *
 * The followings are the available columns in table 'salesByEmployee':
 * @property integer $practiceID
 * @property integer $userID
 * @property string $userName
 * @property string $totalPaid
 * @property string $totalReturn
 */
class SalesByEmployee extends CActiveRecord
{
    const CRON_LAST_RUN_FILE = 'cron_sales_by_employee_last_run.txt';
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return SalesByEmployee the static model class
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
		return 'salesByEmployee';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('practiceID, userID', 'required'),
			array('practiceID, userID', 'numerical', 'integerOnly'=>true),
			array('userName', 'length', 'max'=>255),
			array('totalPaid, totalReturn', 'length', 'max'=>11),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('practiceID, userID, userName, totalPaid, totalReturn', 'safe', 'on'=>'search'),
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
			'userID' => 'User',
			'userName' => 'User Name',
			'totalPaid' => 'Total Paid',
			'totalReturn' => 'Total Return',
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
		$criteria->compare('userID',$this->userID);
		$criteria->compare('userName',$this->userName,true);
		$criteria->compare('totalPaid',$this->totalPaid,true);
		$criteria->compare('totalReturn',$this->totalReturn,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    public static function setData(){
        $connection=Yii::app()->db;
        $transaction=$connection->beginTransaction();
        try{


            $practices = Practice::model()->findAll('type = :type AND inventoryManagementAccess = 1', array(':type' => 'practice'));

            // drop table data
            //$connection->createCommand( "TRUNCATE TABLE `salesByEmployee`" )->execute();
            $connection->createCommand( "DELETE FROM `salesByEmployee`" )->execute();

            //save to DB
            $insertSql = "INSERT INTO salesByEmployee(practiceID, userID, userName, totalPaid, totalReturn ) VALUES ( :practiceID, :userID, :userName, :totalPaid, :totalReturn )";
            $insertCommand=$connection->createCommand($insertSql);

            foreach($practices as $practice) {
                Yii::app()->db->createCommand("SET time_zone = '" . $practice->timeZone . "';")->execute();
                date_default_timezone_set($practice->timeZone);

                $date = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), 1, date('Y')));
                $practiceID = $practice->id;

                $command = $connection->createCommand()
                    ->select('
                    SUM(IF(o.orderStatus = \'paid\', l.lineTotal, 0)) AS totalPaid,
                    SUM(IF(o.orderStatus = \'returned\', l.lineTotal, 0)) AS totalReturn,
                    CONCAT_WS(" ", u.firstName, u.middleName, u.lastName) AS userName,
                    o.practiceID AS practiceID,
                    u.id AS userID'
                    )
                    ->from('order o')
                    ->join('lineItem l', 'o.id = l.orderID')
                    ->join('user u', 'l.userID = u.id')
                    ->where('o.practiceID = u.practiceID AND o.orderStatus IN(\'paid\',\'returned\', \'completed\') AND o.dispenseOn >= :date AND o.practiceID = :practiceID')
                    ->group('u.id');
                $param = array(
                    ':practiceID' => $practiceID,
                    ':date' => $date
                );

                $command->bindValues($param);
                $dbResults = $command->queryAll();
                $command->reset();

                foreach($dbResults as $item) {
                    $insertCommand->bindParam(":practiceID", $practiceID, PDO::PARAM_INT);
                    $insertCommand->bindParam(":userID", $item['userID'],PDO::PARAM_INT);
                    $insertCommand->bindParam(":userName", $item['userName'],PDO::PARAM_STR);
                    $insertCommand->bindParam(":totalPaid", $item['totalPaid']);
                    $insertCommand->bindParam(":totalReturn", $item['totalReturn']);
                    $insertCommand->execute();
                }

            }

            $transaction->commit();
            file_put_contents( Yii::getPathOfAlias( 'application.runtime' ) . '/' . self::CRON_LAST_RUN_FILE, date( 'Y-m-d H:i:s' ));
            return TRUE;
        }
        catch(Exception $e){
            $transaction->rollback();
            return FALSE;
        }
    }

    public static function getData(){
        $practiceID = Yii::app ()->user->model->practiceID;
        $connection=Yii::app()->db;
        $sql = "SELECT *, CONCAT( practiceID, userID ) AS id FROM salesByEmployee WHERE practiceID = :practiceID";
        $dependency = new CFileCacheDependency( Yii::getPathOfAlias( 'application.runtime' ) . '/' . self::CRON_LAST_RUN_FILE );
        $command = $connection->cache( 3600, $dependency )->createCommand($sql);

        $command->bindParam(":practiceID", $practiceID, PDO::PARAM_INT);
        $results = $command->queryAll();
        return $results;


    }
}