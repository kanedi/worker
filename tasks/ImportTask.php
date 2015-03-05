<?php
/**
     *parameter branch cabang :
     *1: pusat
     *3: SUMSEL
     *4: BANTEN
     *5: JATENG
     *6: JATIM
     *7: KALTIM
     *8: SINGGALANG
     *9: MAKASAR
     *10: JOGJAKARTA
*/

class ImportTask extends \Phalcon\CLI\Task
{
    public function mainAction()
    {
        echo "\nThis is the default task and the default action \n";
    }

    //db = database donatur cabang
    //branch = ms_branch_id di desi
    //loop = kelipatan 1000 yang ingin di loop
    public function aksiAction($db,$branch){
        $sqlserver = new Library\Ozip\SqlServer();
        $sqlserver->db=$db;
        $total = $this->itung($db);
        $loop = 1;
        if($total > 100){
            $sisa = $total % 100;
            if($sisa != 0){
                $total = $total - $sisa;
            }
            $loop = $total / 100;
            if($sisa != 0){
                $loop = $loop + 1;
            }
        }
        try {
            $transactionManager = new Phalcon\Mvc\Model\Transaction\Manager();
            $transaction = $transactionManager->get();
            for($i=1; $i<=$loop; $i++){
                $row=100;
                $pagenumber=$i;               
                $q ="
                    SELECT *
                    FROM (
                    SELECT *, 
                    ROW_NUMBER() OVER (ORDER BY id_donatur) AS RowNum
                    FROM tb_donatur ) AS SOD
                    WHERE SOD.RowNum BETWEEN ((".$pagenumber."-1)*".$row.")+1
                    AND ".$row."*(".$pagenumber.")
                
                ";
                $con = $sqlserver->connect();
                $rs = $sqlserver->query($q);            
                $data = array();
                if($rs){
                    $hasil = true;                
                    while ($row = mssql_fetch_array($rs)) {
                       $data[] = $row;
                       $genid = new Library\Ozip\Id();
                       $seq = $genid->getSeq('donor');
                       $donor = new CrDonor;
                       $donor->setTransaction($transaction);
                       $donor->name = $row['nama'];
                       $donor->address = $row['alamatrumah1'];
                       $donor->city = $row['kotarumah'];
                       $donor->province = $row['propinsi'];
                       $donor->hp = $row['hp'];
                       $donor->npwp = $row['npwp'];
                       if($row['email']!=''){
                           $donor->email = $row['email'];
                       }
                       $donor->kd_cc = $row['kd_cc'];
                       $donor->branch_origin = $branch;
                       $donor->branch_current = $branch;
                       $donor->is_deleted = 0;
                       $donor->type = "PERSONAL";
                       $donor->country = 'INDONESIA';
                       $donor->public_id = $seq;
                       $donor->created = date("Y-m-d H:i:s",strtotime($row['tanggal_input']));
                       if($donor->save() == false){
                            $errors = array();
                                   foreach ($donor->getMessages() as $message) {
                                       $transaction->rollback($message->getMessage());
                                       break;
                                   }
                       }
                       echo '.';
                   } 
                   $tt = $i * 100;
                   echo "\n import From db: ".$db." record count ".$tt." Done"," \n ";
                }else{
                    $transaction->rollback(mssql_get_last_message());    
                }
                $sqlserver->close();
            }
            $transaction->commit();    
        } catch (Phalcon\Mvc\Model\Transaction\Failed $e) {
            echo $e->getMessage(), "\n";
        }              
    }
    
    public function hitungAction($db){
        $sqlserver = new Library\Ozip\SqlServer();
        $sqlserver->db=$db;   
        $q = "SELECT COUNT(kd_donatur) FROM tb_donatur";
        $con = $sqlserver->connect();
        $rs = $sqlserver->query($q);            
        $c = 0;
        if($rs){
            $hasil = true;
            while ($row = mssql_fetch_array($rs)) {
                $c = $row[0];
            }                
        }                
        $sqlserver->close();
        echo $c;        
    }
    
    public function itung($db){
        $sqlserver = new Library\Ozip\SqlServer();
        $sqlserver->db=$db;   
        $q = "SELECT COUNT(kd_donatur) FROM tb_donatur";
        $con = $sqlserver->connect();
        $rs = $sqlserver->query($q);            
        $c = 0;
        if($rs){
            $hasil = true;
            while ($row = mssql_fetch_array($rs)) {
                $c = $row[0];
            }                
        }                
        $sqlserver->close();
        return $c;        
    }
    
    public function tesAction(){
        $s = 7789;
        $t = $s % 1000;
        $j = $s - $t;
        $total = $j / 1000;
        echo $total;
    }
}
