<?php
/**
 * Created by PhpStorm.
 * User: martinadiyono
 * Date: 2/18/14
 * Time: 4:38 PM
 */

namespace Library\Ozip;


class DateHelper {

    public $datetimeFormat = "Y-m-d H:i:s";

    public function getCurrentTimestamp(){
        return date($this->datetimeFormat);
    }

    public function strToDateTime($str){
        return date($this->datetimeFormat, strtotime($str));
    }
} 