<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mariano
 * Date: 4/15/13
 * Time: 11:07 AM
 * To change this template use File | Settings | File Templates.
 */

class salesByEmployeeWidget extends CWidget {
	public function run() {
		$data = SalesByEmployee::getData();

        $sort = new CSort();
        $sort->defaultOrder = 'totalPaid DESC';
        $sort->attributes = array(
            'totalPaid' => array(
                'asc' => 'totalPaid ASC',
                'desc' => 'totalPaid DESC',
            ),
            'totalReturn' => array(
                'asc' => 'totalReturn ASC',
                'desc' => 'totalReturn DESC',
            ),
            'userName' => array(
                'asc' => 'userName ASC',
                'desc' => 'userName DESC',
            ),
        );

        $dataProvider = new CArrayDataProvider($data, array(
            'id' => 'salesByEmployee',
            'sort' => $sort,
        ));

		$this->render('salesByEmployee',array(
			'dataProvider' => $dataProvider
		));
	}

}
