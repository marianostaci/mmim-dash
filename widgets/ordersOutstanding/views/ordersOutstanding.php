<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mariano
 * Date: 6/13/13
 * Time: 3:31 PM
 * To change this template use File | Settings | File Templates.
 */
?>
<div class="widgetContent widgetOrders">
    <header class="boxHeader clearfix">
        <h4 class="title fLeft">Orders outstanding</h4>
        <div class="fRight">
            <?php /*echo CHtml::link( 'New order', Yii::app()->createUrl( '/order/admin', array( 'action' => 'create', 'destination' => USER::PREFERRED_PAGE_DASHBOARD ) ) , array( 'class' => 'smallActionsLink' ) ); */?>

            <?php
            EQuickDlgs::iframeButton(
                array(
                    'controllerRoute' => '/order/create',
                    'actionParams'    => array( 'destination' => USER::PREFERRED_PAGE_DASHBOARD ),
                    'dialogTitle' => 'Add new order',
                    'dialogWidth' => '90%',
                    'dialogHeight' => 500,
                    'id' => 'order-create-'.rand(),
                    'openButtonText' => '',
                    'closeButtonText' => '',
                    'closeOnAction' => true,
                    'refreshGridId' => 'order-grid', //the grid with this id will be refreshed after closing
                    'openButtonHtmlOptions' => array(
                        'class' => 'tipS icon-plus',
                        'title' => 'Add new order',
                        'original-title' => 'Add new order'
                    ),
                    'dialogAttributes' => array(
                        'options' => array(
                            'resizable' => false,
                            'draggable' => false,
                            'show' => array('effect' => 'fade', 'duration' => 500),
                            'hide' => array('effect' => 'fade', 'duration' => 300),
                        )
                    )
                )
            );
            ?>
        </div>
    </header>

    <?php
    $this->widget ('zii.widgets.grid.CGridView', array(
        'id' => 'order-grid',
        'htmlOptions' => array('class' => 'table-responsive', 'style' => 'overflow: hidden;'),
        'itemsCssClass' => 'newTable',
        'summaryText' => '',
        'pagerCssClass' => 'pagination',
        'pager'=>array(
            'firstPageLabel' => '&#60;&#60;',
            'prevPageLabel'  => '<',
            'nextPageLabel'  => '>',
            'lastPageLabel'  => '&#62;&#62;',
            'hiddenPageCssClass' => 'disable',
        ),
        'dataProvider' => $dataProvider,
        'ajaxUrl' => array( '/' . Yii::app()->controller->getRoute() ),
        'summaryText' => false,
        'columns' => array(
            array(
                'name' => 'id',
                'header' => 'Order',
                'value' => 'CHtml::link(\'Order \' . $data["id"], array("/order/admin", "filterType" => "unsetFilters", "orderID" => $data["id"]), array("class" => "orderDetails", "data-divid" => "orderDetails"))',
                'type' => 'raw',
            ),
            array(
                'name' => 'updated',
                'header' => 'Last updated',
                'value'  => 'date("m/d/Y", strtotime($data["updated"]))'
            ),
            array(
                'name' => 'days',
                'header' => 'Days',
                'value' => 'round((time() - strtotime($data["updated"]))/86400, 0);'

            ),
            array(
                'class' => 'CButtonColumn',
                'template' => '{approve}{edit}{place}{receive}{print}',
                'header' => 'Actions',
                'buttons' => array(
                    'approve' => array(
                        'label' => false,
                        'url' => array( 'VendorOrder', 'createApproveLink' ),
                        'visible' => '( Yii::app()->user->model->role != User::USER_ROLE_STAFF  && $data["orderStatus"] == Order::STATUS_PENDING ) ? TRUE : FALSE ',
                    ),
                    'place' => array(
                        'label' => false,
                        'url' => array( 'VendorOrder', 'createPlaceLink' ),
                        'visible' => 'in_array( $data["orderStatus"], array( Order::STATUS_APPROVED )) ? TRUE : FALSE ',
                    ),
                    'receive' => array(
                        'label' => false,
                        'url' => array( 'VendorOrder', 'createReceiveLink' ),
                        'visible' => 'in_array( $data["orderStatus"], array( Order::STATUS_PLACED, ORDER::STATUS_SHIPPED )) ? TRUE : FALSE ',
                    ),
                    'print' => array(
                        'label' => 'Print',
                        'url' => 'Yii::app()->createUrl("/order/printVendorOrder", array("id" => $data["id"]))',
                        'options' => array('target' => '_blank', 'class' => 'actionLink'),
                    ),

                    'edit' => array(
                        'label' => false,
                        'url' => array( 'VendorOrder', 'createEditLink' ),
                        'visible' => '($data["orderStatus"] == Order::STATUS_PENDING )? TRUE : FALSE ',
                    ),
                ),
            ),
        ),
    )); ?>
</div>
<div id="order-iframe-wrapper"></div>
