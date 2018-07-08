<?php
class DashboardController extends Controller {

	/**
	 * @return array action filters
	 */
	public function filters() {
		return array(
			'accessControl', // perform access control actions
		);
	}

	public function accessRules() {
		return array(
            array('deny',
                'expression' => '(isset(Yii::app()->user->model->practice) && Yii::app()->user->model->practice->inventoryManagementAccess == 0 && Yii::app()->user->model->practice->rcm != "") ? true : false',
            ),
			array('allow',
				'expression' => '$user->isPracticeMember() ? TRUE : FALSE',
			),
            array ('deny'),
		);
	}

	public $layout = '//layouts/column2';

    public function init(){
        Yii::import( 'application.components.vendorOrder.*' );
        Yii::app()->clientscript->registerScriptFile(Yii::app()->request->baseUrl . '/js/vendorOrder.js');
    }

	public function actionIndex()
	{


		//Register js files
		Yii::app()->clientScript->registerScriptFile('/js/tasks.js', CClientScript::POS_END);
		Yii::app()->clientScript->registerScriptFile('/js/RCMTasks.js', CClientScript::POS_END);

		//Set dashboard default settings
		$defaultSettings = array(
			(object)array(
				'row'     => 1,
				'col'     => 1,
				'size_x'  => 1,
				'size_y'  => 1,
				'content' => $this->widget('application.widgets.tasksWidget.tasksWidget', array(), TRUE),
				'id'      => DashboardSetting::TASKS_WIDGET,

			),

			(object)array(
				'row'    => 1,
				'col'    => 2,
				'size_x' => 2,
				'size_y' => 1,
				'id'     => DashboardSetting::ORDERS_OUTSTANDING_WIDGET,
			),

			(object)array(
				'row'    => 1,
				'col'    => 3,
				'size_x' => 2,
				'size_y' => 1,
				'id'     => DashboardSetting::ALERT_SYSTEM_WIDGET,
			),

			(object)array(
				'row'    => 2,
				'col'    => 1,
				'size_x' => 1,
				'size_y' => 1,
				'id'     => DashboardSetting::DAYS_ON_HAND_WIDGET,

			),

			(object)array(
				'row'    => 2,
				'col'    => 2,
				'size_x' => 2,
				'size_y' => 1,
				'id'     => DashboardSetting::TOTAL_SALES_BY_PRODUCT_WIDGET,
			),

			(object)array(
				'row'    => 2,
				'col'    => 3,
				'size_x' => 1,
				'size_y' => 1,
				'id'     => DashboardSetting::SALES_BY_EMPLOYEE_WIDGET,
			),
			(object)array(
				'row'    => 3,
				'col'    => 1,
				'size_x' => 2,
				'size_y' => 1,
				'id'     => DashboardSetting::ALERT_RCM_SYSTEM_WIDGET
			));

		//Get Practice settings, if no settings exists than set default settings.
		if ($model = UserDashboard::model()->findByAttributes(array('userID' => Yii::app()->user->id))) {
			$practiceSettings = unserialize($model->settings);
			foreach ($practiceSettings as &$setting) {
				$setting = (object)$setting;
			}
			$settings = $practiceSettings;

		} else {
			$settings = $defaultSettings;
		}

		//Set widgets content and remove widgets if conditions are not meet.
		foreach ($settings as $id => $widget) {

			//Remove Claims Alert System if practice has not Claims access
			if ($widget->id == DashboardSetting::ALERT_RCM_SYSTEM_WIDGET && empty(Yii::app()->user->model->practice->rcm)) {
				unset($settings[$id]);
				continue;
			}
			//Remove tasks widget and alert system widget if practice is not allowed to access Task Management
			if ($widget->id == DashboardSetting::TASKS_WIDGET || $widget->id == DashboardSetting::ALERT_SYSTEM_WIDGET) {
				if (!Practice::getPracticePackageAccess('taskManagement')) {
					unset($settings[$id]);
				}
			}

			//Remove orders outstanding widget if practice is not allowed to access Order Management
			if ($widget->id == DashboardSetting::ORDERS_OUTSTANDING_WIDGET) {
				if (!Practice::getPracticePackageAccess('orderTracking')) {
					unset($settings[$id]);
				}
			}

			//Check widget permissions and remove widget if user has not access to it
			if(Yii::app()->user->role != User::USER_ROLE_OWNER) {
				if(!WidgetsPermissions::getWidgetAccess(Yii::app()->user->model->practiceID, $widget->id, Yii::app()->user->role)){
					unset($settings[$id]);
				}
			}

			$widget->content = $this->widget(UserDashboard::getContent($widget->id), array(), TRUE);
		}

		//Render dashboard view
		$this->render('index', array('settings' => $settings));
	}

	//Get dashboard

    public function actionSaveSettings() {
        if(!Yii::app()->request->isAjaxRequest) {
            throw new CHttpException(400, 'Page not found');
        }

        $settings = $_POST['settings'];
        // save the settings :
        if($model = UserDashboard::model()->findByAttributes(array('userID' => Yii::app()->user->id))) {
            $model->settings = serialize($settings);
        } else {
            $model = new UserDashboard();
            $model->userID = Yii::app()->user->id;
            $model->settings = serialize($settings);

        }

        if($model->save()) {
			Yii::app()->elephant->systemMessage('success', 'Dashboard configuration saved.');
		}
		else {
			Yii::app()->elephant->systemMessage('error', 'Dashboard configuration not saved. Try again.');
		}
    }

    /*
     * Display Task details
     */
    public function actionDisplayTaskDetails($id) {

        Yii::app()->clientScript->scriptMap['jquery.js'] = false;
        Yii::app()->clientScript->scriptMap['jquery.min.js'] = false;
        Yii::app()->clientScript->scriptMap['jquery-ui.min.js'] = false;
        Yii::app()->clientScript->scriptMap['jquery-ui.js'] = false;

        $model = Tasks::model()->findByPk($id);
        $relatedModel = NULL;
        $destination = Yii::app()->controller->id;

        $actions = array();

        //Edit Task
        $actions[] = CHtml::link( 'Edit',
            Yii::app()->createUrl('/tasks/update', array( 'id' => $model->id )),
            array(
                'class' => 'action edit-action',
                //'title' => 'Edit',
                'id' => 'task-edit-' . $model->id,
                'data-task' => CJSON::encode( array('taskID' => $model->id, 'action' => 'update', 'destination' => $destination) )) );


        // delegate task
        if($model->status == Tasks::OPEN ){
            $actions[] = CHtml::link( 'Delegate',
                Yii::app()->createUrl('/tasks/delegate', array( 'id' => $model->id )),
                array(
                    'class' => 'action delegate-action',
                    //'title' => 'Delegate',
                    'id' => 'task-delegate-' . $model->id,
                    'data-task' => CJSON::encode( array('taskID' => $model->id, 'action' => 'delegate', 'destination' => $destination) )) );
        }

        // load related entity to put specific actions
        if( $model->relatedEntityType == 'order' ){
            $order = Order::model()->findByPk( $model->relatedEntityID );
            if( !empty( $order ) ){
                if( $order->type == Order::TYPE_VENDOR ){
                    switch ( $order->orderStatus ){
                        case Order::STATUS_APPROVED:
                            $actions[] = VendorOrder::createPlaceLink( array( 'id' => $order->id), false, false );
                            break;
                        case Order::STATUS_COMPLETED:
                            break;
                        case Order::STATUS_FORWARDED:
                            break;
                        case Order::STATUS_PENDING:
                            $actions[] = VendorOrder::createApproveLink( array( 'id' => $order->id), false, false );
                            break;
                        case Order::STATUS_PLACED:
                        case ORDER::STATUS_SHIPPED:
                        $actions[] = VendorOrder::createReceiveLink( array( 'id' => $order->id), false, false );
                            break;
                    }
                    $actions[] = CHtml::link( 'Print', Yii::app()->createUrl("/order/printVendorOrder", array('id' => $order->id)),  array('class' => '', 'title' => '', 'target' => '_blank') );
                }
            }
        };

        if( $model->relatedEntityType == 'transaction' ){
            $transaction = Transaction::model()->with(array('mainProduct', 'fromCabinet', 'fromCabinet.location' => array('alias' => 'fromLocation'), 'toCabinet', 'toCabinet.location' => array('alias' => 'toLocation')))->findByPk( $model->relatedEntityID );
            if( !empty( $transaction ) ){
                $relatedModel = $transaction;
            }

        }

        $this->renderPartial('_tasksDashboardDetailsDisplay', array('model' => $model, 'actions' => $actions, 'relatedModel' => $relatedModel));
        Yii::app()->end();

    }

	/*
	 * Complete a task
	 * @param $id - taskID
	 */
	public function actionCompleteTask($id, $isClaimTask = FALSE)
	{
		//Complete AIM Task
		if (!$isClaimTask) {
			$model = Tasks::model()->findByPk($id);
			if ($model->practiceID != Yii::app()->user->model->practiceID) throw new CHttpException(404, 'Invalid request');
			if ($model && $model->status != Tasks::COMPLETE) {
				$model->status        = Tasks::COMPLETE;
				$model->dateCompleted = date('Y-m-d H:i:s');
				$model->updated       = date('Y-m-d H:i:s');
				if ($model->save(FALSE)) {
					Yii::app()->elephant->systemMessage('success', 'The task was successfully set as completed.');

					return TRUE;
				}
			}
			Yii::app()->elephant->systemMessage('success', 'Task update failed.');
		} //Complete Claims Task
		else {
			RCMTask::model()->updateByPk($id, array('status' => RCMTask::STATUS_COMPLETE, 'dateCompleted' => date('Y-m-d H:i:s')));
			Yii::app()->elephant->systemMessage('success', 'The task was successfully set as completed.');

			return TRUE;
		}

		return FALSE;
	}

    public function actionTest() {


        $this->render('test');
    }

}