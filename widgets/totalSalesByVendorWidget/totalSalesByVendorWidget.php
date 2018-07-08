<?php
	/**
	 * Created by Ascendro Web Technologies SRL
	 * User: mariano
	 * Date: 8/13/13
	 * Time: 1:54 PM
	 */

	class TotalSalesByVendorWidget extends CWidget {

		public function run() {

			//If Widget is called from Aesyntix Admin pull Total Sales for all Practices, else pull for the specific practice
			if(Yii::app()->user->isAesyntixAdmin()) $data = SalesByProduct::getData(false,true);
			else $data = SalesByProduct::getData(true, true);


			$rows = array();
			foreach ($data as $item) {
				$salesPercent = ($item['totalSales60'] > 0) ? abs(($item['totalSales30'] - $item['totalSales60'])/$item['totalSales60'])*100 : 100;
				$salesClass = ($item['totalSales30'] >= $item['totalSales60']) ? 'sales-up' : 'sales-down';
				$salesSign = ($item['totalSales30'] >= $item['totalSales60']) ? '+' : '-';

				$returnsPercent = ($item['totalReturn60'] > 0) ? abs(($item['totalReturn30'] - $item['totalReturn60'])/$item['totalReturn60'])*100 : (($item['totalReturn30'] > 0) ?  100 : 0);
				$returnsClass = ($item['totalReturn30'] >= $item['totalReturn60']) ? 'sales-up' : 'sales-down';
				$returnsSign = ($item['totalReturn30'] >= $item['totalReturn60']) ? '+' : '-';

				$rows[] = array(
					'vendor' => $item['vendorName'],
					'sales' => '$' . number_format( $item['totalSales30'] , 2),
					'salesTrend' => '<div class="' . $salesClass . '">' . $salesSign . number_format($salesPercent, 2) . '%</div>',
					'returns' => '$' . number_format( $item['totalReturn30'] , 2),
					'returnsTrend' => '<div class="' . $returnsClass . '">' . $returnsSign . number_format($returnsPercent, 2) . '%</div>',
					'net' => '$' . number_format( $item['totalSales30'] - $item['totalReturn30'] , 2),
				);
			}

			$this->render('totalSalesByVendor', compact( 'rows' ));

		}

	}