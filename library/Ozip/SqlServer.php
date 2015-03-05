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
    //public $nameserver = '10.10.10.102'; //<- coba
    public $username ='sa';
    public $password ='enozs';
    public $db='dbdonatur';
    public $link;
    
    public function connect(){
        $link1 = \mssql_pconnect($this->nameserver,$this->username,$this->password);
        \mssql_select_db($this->db);
        $this->link = $link1;
        return $link1;
    }
    
    
    public function query($sql){
        $rt = \mssql_query($sql);
        return $rt;
    }
    
    public function close(){
        mssql_close($this->link);
    }
    
    public function angka2huruf($nilai){
        $jadi = false;
        if($nilai > 0){
            $angka = array(1 => 'A',2 => 'B',3 => 'C',4 => 'D',5 => 'E',6 => 'F', 7 => 'G',8 => 'H',9 => 'I',10 => 'J',11 => 'K',
                12 => 'L',13 => 'M',14 => 'N',15 => 'O',16 => 'P',17 => 'Q',18 => 'R',19 => 'S',20 => 'T', 21 => 'U',22 => 'V',
                23 => 'W',24 => 'X',25 => 'Y',26 => 'Z',
            );
            $jadi = array();
            if($nilai > 26){
                while($nilai > 26){
                    $mod = $nilai % 26;
                    if($mod == 0){
                        $mod = 26;
                        $nilai = $nilai -1;
                    }
                    $eks = $angka[$mod];
                    $nilai = round($nilai / 26);
                    array_push($jadi,$eks);
                }
                $jadi = $angka[$nilai].implode('',$jadi);
            }else{
                $jadi = $angka[$nilai];
            } 
        }        
        return $jadi;
    }
     public function kd_cc(){
        $lastid = 0;
        $year = date('Y', time());
        //$this->db='dbdonatur';
        $q = "select TOP 1 id_donatur from tb_donatur where left(id_donatur,4) = '".$year."'
             order by id_donatur desc";            
        $con = $this->connect();
        $rs = $this->query($q);
        while ($row = mssql_fetch_array($rs, MSSQL_NUM)) {
             $lastid=$row[0];
        }
        if($lastid == 0){
            $nilai = $lastid;   
        }else{
            $nilai = substr($lastid,-5);            
        }        
        //$this->close();
        $tahun = substr($year,1,3);        
        $akhirnilai = $nilai + 1;
        //generate id_donatur dan kd_cc
        $data = array(
            'id_donatur' =>  $year.str_pad($akhirnilai, 5, "0", STR_PAD_LEFT),
            'kd_cc' => 'D-'.$tahun.$this->angka2huruf($akhirnilai)
        );
        //print_r($data);
        return $data;
    }
    
    public function cekdonorsandra($data){
        $hasil = false;
        if(is_array($data)){
            //$this->db='dbdonatur';
            $q = "select kd_cc from tb_donatur
                where id_desi =
                    '".$data['id']."'";
            //echo $data['id_donatur'];
            $con = $this->connect();
            $rs = $this->query($q);
            if($rs){
                while ($row = mssql_fetch_array($rs, MSSQL_NUM)) {
                        $hasil=$row[0];
                }   
            }
            //$this->close();
        }
        return $hasil;
    }
    
    public function donorsandra($data){
        $hasil = false;
        if(is_array($data)){
             //$this->db='dbdonatur';
             $q = "insert into tb_donatur (id_donatur, nama, alamatrumah1, kotarumah, propinsi, hp, email, kd_cc, kd_cabang, id_desi, impor_oris)
                 values (
                     '".$data['id_donatur']."',
                     '".$data['nama']."',
                     '".$data['alamatrumah1']."',
                     '".$data['kotarumah']."',
                     '".$data['propinsi']."',
                     '".$data['hp']."',
                     '".$data['email']."',
                     '".$data['kd_cc']."',
                     '".$data['kd_cabang']."',
                     '".$data['id']."',
                     'Y'
                 )";
             //echo $data['id_donatur'];
             $con = $this->connect();
             $rs = $this->query($q);
             if($rs){
                 $hasil = true;
             }            
             //$this->close();
         } 
        return $hasil;
    }
    
     public function donationsandra($data){
        $hasil = false;
        if(is_array($data)){
            //$this->db='dbdonatur';
            $q = "insert into tb_donasi (tanggal_input, tanggal, donasi, kd_via, kd_jenisdonasi, kd_konter, kd_cc, kd_transaksi, desi_donation_id)
                values (
                    '".$data['created']."',
                    '".$data['trx_date']."',
                    '".$data['amount']."',
                    '".$data['via']."',
                    '".$data['fa_fund_type_id']."',
                    '".$data['cr_counter_id']."',
                    '".$data['kd_cc']."',
                    '".$data['kd_transaksi']."',
                    '".$data['cr_donation_header_id']."'
                )";
            //echo $data['id_donatur'];
            $con = $this->connect();
            $rs = $this->query($q);
            if($rs){
                $hasil = true;
            }            
            //$this->close();
        }
        return $hasil;
    }
    
    public function cekdonationsandra($data){
        $hasil = false;
        if(is_array($data)){
            //$this->db='dbdonatur';
            $q = "select desi_donation_id from tb_donasi
                where kd_transaksi =
                    '".$data['kd_transaksi']."'";
            //echo $data['id_donatur'];
            $con = $this->connect();
            $rs = $this->query($q);
            if($rs){
                while ($row = mssql_fetch_array($rs, MSSQL_NUM)) {
                        $hasil=$row[0];
                }   
            }
            //$this->close();
        }
        return $hasil;
    }
    
}