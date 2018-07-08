<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mariano
 * Date: 4/15/13
 * Time: 8:59 AM
 * To change this template use File | Settings | File Templates.
 */

class tasksWidget extends CWidget {
	public function run() {

		$model=new Tasks('dashboardMyTasks');
		$model->unsetAttributes();
		$model->isDashboardCall = true;

		//Display tasks depending on user roles
        $model->dueDate = date('Y-m-d');

		$this->render('tasks',array(
			'model'=>$model,
		));
	}

}
