<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mariano
 * Date: 6/6/13
 * Time: 9:11 AM
 * To change this template use File | Settings | File Templates.
 */

class TotalSalesByProduct extends CWidget{

    public function run(){
	    //If Widget is called from Aesyntix Admin pull Total Sales for all Practices, else pull for the specific practice
		if(Yii::app()->user->isAesyntixAdmin()) $data = SalesByProduct::getData();
	    else $data = SalesByProduct::getData(true);


        $rows = array();
        foreach ($data as $item) {
            $salesPercent = ($item['totalSales60'] > 0) ? abs(($item['totalSales30'] - $item['totalSales60'])/$item['totalSales60'])*100 : 100;
            $salesClass = ($item['totalSales30'] >= $item['totalSales60']) ? 'sales-up' : 'sales-down';
            $salesSign = ($item['totalSales30'] >= $item['totalSales60']) ? '+' : '-';

            $returnsPercent = ($item['totalReturn60'] > 0) ? abs(($item['totalReturn30'] - $item['totalReturn60'])/$item['totalReturn60'])*100 : (($item['totalReturn30'] > 0) ?  100 : 0);
            $returnsClass = ($item['totalReturn30'] >= $item['totalReturn60']) ? 'sales-up' : 'sales-down';
            $returnsSign = ($item['totalReturn30'] >= $item['totalReturn60']) ? '+' : '-';

            $rows[] = array(
                'product' => Yii::app()->user->isAesyntixAdmin() ? $item['productName'] : CHtml::link($item['productName'], array("/inventory/inventory/", "dashboardPID" => $item['productID'])),
                'sales' => '$' . number_format( $item['totalSales30'] , 2),
                'salesTrend' => '<div class="' . $salesClass . '">' . $salesSign . number_format($salesPercent, 2) . '%</div>',
                'returns' => '$' . number_format( $item['totalReturn30'] , 2),
                'returnsTrend' => '<div class="' . $returnsClass . '">' . $returnsSign . number_format($returnsPercent, 2) . '%</div>',
                'net' => '$' . number_format( $item['totalSales30'] - abs($item['totalReturn30']) , 2),
            );
        }

        $this->render('totalSalesByProduct', compact( 'rows' ));
    }
}