<?php
/**
 * Created by Ascendro Web Technologies SRL
 * User: mariano
 * Date: 5/10/13
 * Time: 11:09 AM
 */

class daysOnHandWidget extends CWidget {
	public function run() {

		$data = DaysOnHand::getData();

		$this->render('daysOnHand',array(
			'data' => $data,
		));
	}

}