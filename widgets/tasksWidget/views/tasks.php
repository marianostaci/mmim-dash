<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mariano
 * Date: 4/15/13
 * Time: 9:00 AM
 * To change this template use File | Settings | File Templates.
 */
?>
<div class="widgetContent">

        <header class="boxHeader clear">
            <h4 class="title">My Tasks</h4>
        </header>

        <?php
        $this->widget('zii.widgets.grid.CGridView', array(
            'id'=>'tasksWidget-grid',
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
            'dataProvider'=>$model->dashboardMyTasks(),
            'enableSorting' => true,
            'ajaxUrl' => array( '/' . Yii::app()->controller->getRoute() ),
            'columns'=>array(
                array(
                    'name' => 'dueDate',
                    'header' => 'Date',
                    'value' => 'date("m/d/Y", strtotime($data->dueDate))'
                ),
                array(
                    'name' => 'name',
                    'header' => 'Task',
                    'value' => 'CHtml::link($data->name, array("dashboard/displayTaskDetails/", "id" => $data->id), array("class" => $data->dueDate < date("Y-m-d") ? "expired showDashboardTasksDetails" : "showDashboardTasksDetails"))',
                    'type' => 'raw'
                ),
            ),
        )); ?>

    <div id="taskDetails-wrapper" style="display:none"></div>
    <div id="task-iframe-wrapper"></div>
</div>