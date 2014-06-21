<?php
/**
 * Created by PhpStorm.
 * User: martinadiyono
 * Date: 2/18/14
 * Time: 4:36 PM
 */

namespace Library\Ozip;


class StringHelper {
    
    public $dasar = array(1=>'satu','dua','tiga','empat','lima','enam','tujuh','delapan','sembilan');
    public $angka = array(1000000000,1000000,1000,100,10,1);
    public $satuan = array('milyar','juta','ribu','ratus','puluh',''); 

    public function eja($n) { 
        $str = '';
        $i=0; 
        while($n!=0){ 
            $count = (int)($n/$this->angka[$i]); 
    
            if($count>=10) $str .= $this->eja($count). " ".$this->satuan[$i]." "; 
            else if($count > 0 && $count < 10) 
                $str .= $this->dasar[$count] . " ".$this->satuan[$i]." "; 


            $n -= $this->angka[$i] * $count; 
            $i++; 
        }
        $str = preg_replace("/satu puluh (\w+)/i","\\1 belas",$str); 
        $str = preg_replace("/satu (ribu|ratus|puluh|belas)/i","se\\1",$str); 
        return strtoupper($str); 
    } 
    public function test(){
        echo "Test";
    }
} 