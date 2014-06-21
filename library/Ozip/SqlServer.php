<?php
/**
 * Created by Komodo.
 * User: Kanedi
 * Date: 5/13/14
 * Time: 9:15 AM
 */

namespace Library\Ozip;

class SqlServer {
    
    public $nameserver ='10.10.10.21';
    public $username ='sa';
    public $password ='enozs';
    public $db='dbdonatur';
    public $link;
    
    public function connect(){
        $link1 = \mssql_pconnect($this->nameserver,$this->username,$this->password);
        \mssql_select_db($this->db, $link1);
        $this->link = $link1;
        return $link1;
    }
    
    
    public function query($sql){
        $rt = \mssql_query($sql,$this->link);
        return $rt;
    }
    
    public function close(){
        mssql_close($this->link);
    }
    
}