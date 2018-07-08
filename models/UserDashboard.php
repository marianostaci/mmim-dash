<?php

/**
 * This is the model class for table "userDashboard".
 *
 * The followings are the available columns in table 'userDashboard':
 * @property integer $id
 * @property integer $userID
 * @property string $settings
 * @property string $created
 * @property string $updated
 */
class UserDashboard extends MyActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'userDashboard';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that 
        // will receive user inputs. 
        return array(
            array('userID, settings', 'required'),
            array('userID', 'numerical', 'integerOnly'=>true),
            array('created, updated', 'safe'),
            // The following rule is used by search(). 
            // @todo Please remove those attributes that should not be searched. 
            array('id, userID, settings, created, updated', 'safe', 'on'=>'search'),
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
            'id' => 'ID',
            'userID' => 'User',
            'settings' => 'Settings',
            'created' => 'Created',
            'updated' => 'Updated',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched. 

        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('userID',$this->userID);
        $criteria->compare('settings',$this->settings,true);
        $criteria->compare('created',$this->created,true);
        $criteria->compare('updated',$this->updated,true);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return UserDashboard the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public static function getContent($id) {
        $content = array(
            'tasksWidget' => 'application.widgets.tasksWidget.tasksWidget',
            'TotalSalesByProduct' => 'application.widgets.totalSalesByProduct.TotalSalesByProduct',
            'daysOnHandWidget' => 'application.widgets.daysOnHandWidget.daysOnHandWidget',
            'alertSystemWidget' => 'application.widgets.alertSystemWidget.alertSystemWidget',
            'OrdersOutstandingWidget' => 'application.widgets.ordersOutstanding.OrdersOutstandingWidget',
            'salesByEmployeeWidget' => 'application.widgets.salesByEmployeeWidget.salesByEmployeeWidget',
            'alertRCMSystemWidget' => 'application.widgets.alertRCMSystemWidget.alertRCMSystemWidget',
        );

        return isset($content[$id]) ? $content[$id] : FALSE;
    }
} 