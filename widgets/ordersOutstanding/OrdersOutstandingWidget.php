<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mariano
 * Date: 6/13/13
 * Time: 3:29 PM
 * To change this template use File | Settings | File Templates.
 */

class OrdersOutstandingWidget extends CWidget{

    public function init(){
        Yii::import( 'application.components.vendorOrder.*' );
    }

    public function run(){

        $practiceID = Yii::app ()->user->model->practiceID;
        $data = Order::getDataForOrdersOutstandingWidget( $practiceID );

        $dataProvider = new CArrayDataProvider($data, array(
            'id' => 'orders-oustanding',
            'sort'=>array(
                'attributes'=>array(
                    'id', 'updated',
                ),
            ),
            'pagination'=>array(
                'pageSize'=> 5,
            ),
        ));
        $this->render( 'ordersOutstanding', array( 'dataProvider' => $dataProvider ) );
    }

}