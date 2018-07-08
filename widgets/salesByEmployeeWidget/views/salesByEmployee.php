<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mariano
 * Date: 4/15/13
 * Time: 11:08 AM
 * To change this template use File | Settings | File Templates.
 */
?>
<div class="widgetContent">

    <header class="boxHeader clear">
        <h4 class="title">Sales by employee - month to date</h4>
    </header>

    <?php
    $this->widget ('zii.widgets.grid.CGridView', array(
        'id' => 'salesByEmployeeGrid',
        'htmlOptions' => array('class' => 'table-responsive'),
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
        'columns' => array(
            array(
                'name' => 'userName',
                'header' => 'EMPLOYEE',
                'type' => 'raw'
            ),
            array(
                'header' => 'SALES',
                'name' => 'totalPaid',
                'type' => 'raw'
            ),
            array(
                'name' => 'totalReturn',
                'header' => 'RETURNS',
                'type' => 'raw'
            ),
            array(
                'name' => 'NET',
                'value' => '$data["totalPaid"] - abs($data["totalReturn"])',
                'type' => 'raw'
            ),
        ),
    )); ?>

</div>