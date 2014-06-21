<?php
/**
 * Created by PhpStorm.
 * User: martinadiyono
 * Date: 2/18/14
 * Time: 4:36 PM
 */

namespace Library\Ozip;

use  Phalcon\Mvc\Model\Query,
    MsSeq;

class Id {
    public function main(){
        echo "Test";
    }
    
    public function getSeq($name){
        
        $seq = MsSeq::findFirst('name = "'.$name.'"');
        if($seq){
            $s = $seq->seq +1;  
        }else{
            $seq = new MsSeq();
            $s = 1;              
        }
        $seq->seq = $s;
        $seq->name = $name;
        $seq->save();         
        //
        return $s;
    }
    
    public function generateDonorId(){
        //max id +1;
    }
} 