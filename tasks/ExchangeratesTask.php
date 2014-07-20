<?php
/**
 * Created by PhpStorm.
 * User: martinadiyono
 * Date: 7/20/14
 * Time: 11:47 AM
 */

class ExchangeratesTask extends \Phalcon\CLI\Task {

    public function mainAction(){
        $json = file_get_contents("http://api.ozip.co.id/currency/getbi");
        $currency = json_decode($json);

        foreach($currency->data as $currency_code => $currency_data){

            $mid_rate = ($currency_data->buy + $currency_data->sell)/2;

            $ms_currency = new MsCurrency();
            $ms_currency->renew($currency_code,$mid_rate);

        }
    }

}