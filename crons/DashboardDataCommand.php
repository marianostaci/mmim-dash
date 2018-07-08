<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mariano
 * Date: 7/23/13
 * Time: 12:09 PM
 * To change this template use File | Settings | File Templates.
 */

class DashboardDataCommand extends CConsoleCommand{
    public function run( $args ){
        Cron::log( "DASHBOARD DATA : CRON STARTED");
        $this->setDaysOnHandData();
        $this->setSalesByEmployeeData();
        $this->setSalesByProductData();
        Cron::log( " DASHBOARD DATA : CRON FINISHED");
    }

    private function setDaysOnHandData(){
        $succeded =  DaysOnHand::setData();
        $status = $succeded ? 'succeded' : 'failed';
        $message = sprintf( "[DaysOnHand] : data refresh %s.", $status );
        Cron::log( $message );
    }

    private function setSalesByEmployeeData(){
        $succeded = SalesByEmployee::setData();
        $status = $succeded ? 'succeded' : 'failed';
        $message = sprintf( "[SalesByEmployee] : data refresh %s.", $status );
        Cron::log( $message );
    }

    private function setSalesByProductData(){
        $succeded = SalesByProduct::setData();
        $status = $succeded ? 'succeded' : 'failed';
        $message = sprintf( "[SalesByProduct] : data refresh %s.", $status );
        Cron::log( $message );
    }



}