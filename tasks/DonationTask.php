<?php

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class DonationTask extends \Phalcon\CLI\Task
{
    public function mainAction() {
        echo "\nThis is the default task and the default action \n";
    }

	public function amqAction(){
	    global $config;
		$connection = new AMQPConnection($config->rabbitmq->host, $config->rabbitmq->port, $config->rabbitmq->username, $config->rabbitmq->password);
		$channel = $connection->channel();

		$channel->queue_declare('donation', false, true, false, false);

		$msg = new AMQPMessage();
		$channel->basic_publish($msg, '', 'hello');

		echo " [x] Sent 'Hello World!'\n";
		$channel->close();
		$connection->close();	
	}
	
	public function receiverAction(){
	    global $config;
		$connection = new AMQPConnection($config->rabbitmq->host, $config->rabbitmq->port, $config->rabbitmq->username, $config->rabbitmq->password);
		$channel = $connection->channel();

		$channel->queue_declare('donation_entry', false, true, false, false);

		echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";
		
		$callback = function($msg) {
			//sleep(10);			
  			echo " [x] Received ", $msg->body, "\n";
		};

		$channel->basic_consume('donation_entry', '', false, true, false, false, array($this,'callback'));

		while(count($channel->callbacks)) {
    			$channel->wait();
		}

	}
	
	public function callback($msg){
	    $this->sendEmail($msg->body);
	    //$this->sendSms($msg->body);
	    echo 'fungsi';
	}

    public function sendEmail($header_id){
	global $config;
	//$this->kwitansi($header_id);
	
        //$mail = new Mail();
        //$mail->send(
        //    array("martin@adiyono.com"),
        //    "Dompet Dhuafa Donation",
        //    "donationentry",
        //    array("header" => $header,"detail"=>$detail,'grandtotal'=>$gt,'date'=>$ddd)
        ////$attach
        //);	
	$header = CrDonationHeader::findFirst($header_id);
        $detail = CrDonationDetail::find('cr_donation_header_id = '.$header_id);
        $gt=0;
        $date = date_create($header->trx_date);
        $ddd = date_format($date, 'd F Y');
	
        $fn = 'donasi_'.$header->CrDonor->public_id.'_'.$header->id.'.pdf';
        //$attach = array(
        //    'data' => $config->path_doc.'donation'.$header_id.'.pdf',
        //    'filename' => $fn
        //);
        foreach($detail as $dd){
            $gt = $gt+$dd->amount;
        }
        setlocale(LC_MONETARY, 'id_ID');
        $gt = money_format('%(#10n',$gt);
        if($header->CrDonor->email!='' || $header->CrDonor->email != null){
            $mail = new Mail();
            $mail->send(
                array(strtolower($header->CrDonor->email)),
                "Dompet Dhuafa Donation",
                "donationentry",
                array("header" => $header,"detail"=>$detail,'grandtotal'=>$gt,'date'=>$ddd)
                //,$attach
            );
        }   
    }
    
    public function sendSms($header_id){
	$header = CrDonationHeader::findFirst($header_id);
        $detail = CrDonationDetail::find('cr_donation_header_id = '.$header_id);
        $gt=0;
        foreach($detail as $dd){
            $gt = $gt+$dd->amount;
        }
         setlocale(LC_MONETARY, 'id_ID');
        $gt = money_format('%(#1n',$gt);
        $pesan = 'Yang Terhormat '.$header->CrDonor->name.', Donasi anda senilai'.$gt.' sudah kami terima, semoga diberikan keberkahan atas harta yang tersisa';
        $pesan = base64_encode($pesan);
        $no = $header->CrDonor->hp;
        $kd_cabang = 1;
        if($no != '' || $no !=null){            
            $ch = curl_init();
            
            curl_setopt_array(
                $ch, array( 
                CURLOPT_URL => 'http://donatur.dompetdhuafa.org/smsserver/insertsms.php?nomor='.$no.'&pesan='.$pesan.'&kd_cabang='.$kd_cabang,
                CURLOPT_RETURNTRANSFER => true
            )); 
            $output = curl_exec($ch);
            curl_close($ch);
        }
    }
    
    public function kwitansi($header_id)
    {
	global $config;
	
        require_once(APPLICATION_PATH.'/library/html2pdf_v4.03/html2pdf.class.php');
        //$header_id = $this->request->get('id');
        $header = CrDonationHeader::findFirst($header_id);
        $detail = CrDonationDetail::find('cr_donation_header_id = '.$header_id);
        $gt=0;
        foreach($detail as $dd){
            $gt = $gt+$dd->amount;
        }
        setlocale(LC_MONETARY, 'id_ID');
        $polos = $gt;
        //$gt = money_format('%(#10n',$gt);
        
        $date = date_create($header->trx_date);
        $ddd = date_format($date, 'd F Y');
        $terbilang= new Library\Ozip\StringHelper();
        $eja = $terbilang->eja($polos);
        //$eja = $polos;
        ?>
        <!-- CSS goes in the document HEAD or added to your external stylesheet -->
        <style type="text/css">
        i{
            font-size:10px; 
        }
        .gede {
            font-size:30px;
        }
        .doa {
            font-size:13px;
        }
        table.gridtable {
                /*font-family: verdana,arial,sans-serif;*/
                font-size:15px;
                color:#333333;
                border-width: 1px;
                border-color: #666666;
                border-collapse: collapse;
        }
        table.gridtable th {
                border-width: 1px;
                padding: 8px;
                border-style: solid;
                border-color: #666666;
                /*background-color: #dedede;*/
                background-color: #ffffff;
        }
        table.gridtable td {
                border-width: 1px;
                padding: 8px;
                border-style: solid;
                border-color: #666666;
                background-color: #ffffff;
        }
        </style>
        <!-- Table goes in the document BODY -->
        <page style="font-size: 12px; font-family: arial;" >
            <table>
                <tr>
                    <!--<td width=500><img width='200' src='https://desi.dompetdhuafa.org/ui/dd-full.jpg'></td>-->
                    <td width=950 align="center" colspan="2"><h3>BUKTI PENERIMAAN DONASI</h3><I> DONATION RECEIPT</I></td>
                </tr>
                            <tr><td height='1'></td></tr>  
                <tr>
                    <td>
                        <table>
                            <tr>
                                <td>Tanggal / <i>date</i></td>
                                <td width='20'></td>
                                <td><?php echo $ddd ?></td>
                            </tr>
                            <tr><td height='3'></td></tr>  
                             <tr>
                                <td>No Trx / <i>Trx ID</i></td>
                                <td width='20'></td>
                                <td><?php echo $header->id ?></td>
                            </tr>
                            <tr><td height='3'></td></tr>  
                              <tr>
                                <td>No Urut / <i>batch</i></td>
                                <td width='20'></td>
                                <td><?php echo $header->batch_user ?></td>
                            </tr>
                        </table>
                    </td>
                    <td>
                         <table>
                            <tr>
                                <td>Cabang / <i>BRANCH</i></td>
                                <td width='20'></td>
                                <td><?php echo $header->CrCounter->MsBranch->name ?> - <?php echo $header->CrCounter->name ?></td>
                            </tr>
                            <tr><td height='3'></td></tr>  
                             <tr>
                                <td>FUNDRAISER</td>
                                <td width='20'></td>
                                <td><?php echo $header->User->HcEmployee->name ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>        
        <hr>
        <table valign='top'>
         <tr>
          <td style="vertical-align: top;">
              <table>
                <tr>
                    <td>ID/NAMA</td>
                    <td width='20'></td>
                    <td rowspan=2 valign='middle' width='280'><?php echo $header->CrDonor->public_id.' / '.$header->CrDonor->name ?> </td>
                </tr>
                <tr>
                    <td><i>ID/NAME</i></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr><td height='10'></td></tr>      
                <tr>
                    <td>HP/EMAIL</td>
                    <td width='20'></td>
                    <td rowspan=2 valign='middle' width='280'><?php echo $header->CrDonor->hp.' / '.$header->CrDonor->email ?> </td>
                </tr>
                <tr>
                    <td><i>HP/EMAIL</i></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr><td height='10'></td></tr>     
                <tr>
                    <td>ALAMAT</td>
                    <td width='20'></td>
                    <td rowspan=2 valign='middle' width='280'><?php echo $header->CrDonor->address ?> </td>
                </tr>
                <tr>
                    <td><i>ADDRESS</i></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr><td height='10'></td></tr>
                <tr>
                    <td colspan=3>
                        <table class='gridtable'>
                            
                            <tr>
                                <td width='280'> 
                                    NPWP: <?php echo $header->CrDonor->npwp ?>
                                </td>
                            </tr>
                             <tr>
                                <td width='280'> 
                                    Diisi sebagai lampiran SPT Tahunan Pajak Penghasilan, untuk
                                    pengurang Penghasilan Kena Pajak (PKP), sesuai keputusan Dirjen
                                    Pajak No. KEP-163/PJ/2003.
                                </td>
                            </tr>                             
                        </table>
                        <table>
                            <tr>
                                <td align="center">Tanda Tangan Penyetor</td>
                                <td width='40'></td>
                                <td align="center">Pengesahan Petugas Amil</td>
                            </tr>
                            <tr>
                                <td align="center"><i>Authorized signatured</i></td>
                                <td width='40'></td>
                                <td align="center"><i>Amil officer's Authorized</i></td>                                
                            </tr>
                            <tr><td height='65'></td></tr>
                            <tr>
                                <td align="center"><?php echo $header->CrDonor->name ?></td>
                                <td width='40'></td>
                                <td align="center"><?php echo $header->User->HcEmployee->name ?></td>                                
                            </tr>
                        </table>
                    </td>
                </tr>
              </table>
          </td>
          <td>
            <table class='gridtable'>
            <tr>
              <th align='center'>TIPE DONASI <br><i>DONATION TYPE</i></th>         
              <th align='center'>MATA UANG <br><i>CURRENCY</i></th>          
              <th align='center'>RATE<br><i>RATE</i></th>          
              <th align='center'>NILAI <br><i>AMOUNT</i></th>         
              <th align='center'>JUMLAH <br><i>TOTAL</i></th>
            </tr>
            <?php
            foreach($detail as $dd){
                $fundtype = $dd->FaFundType->FaFundCategorySub->FaFundCategory->name.' - '.$dd->FaFundType->FaFundCategorySub->name.' - '.$dd->FaFundType->name;
              ?>
              
            <tr>
              <td width='180'><?php echo $fundtype ?></td>         
              <td><?php echo $dd->ms_currency_code ?></td>          
              <td><?php echo number_format($dd->currency_rate,0,',','.') ?></td>          
              <td align='right'><?php echo number_format($dd->currency_amount,0,',','.') ?></td>         
              <td align='right'><?php echo number_format($dd->amount,0,',','.') ?></td>
            </tr>           
              <?php
              }
              ?>
              <tr>
              <td colspan=4><b>GRAND TOTAL</b>(Rp)</td>
              <!--<td colspan=3></td>-->
              <td align='right'><?php echo number_format($gt ,0,',','.')?></td>
              </tr>
               <tr>
                <td colspan=5>Terbilang / <i>Be Calculated</i> &nbsp;:<b><?php echo $eja ?> RUPIAH</b></td>
            </tr>              
            </table>
            <table>
                <tr>
            <td width='550' class='doa'> <i class='doa'>
            Aajarakallaahu fii maa a’thoita, wa baaraka laka abqaita, waja’ala maalaka thohuuraa
            </i> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; artinya:            
            Semoga Allah memberikan pahala dari harta yang tertunai, memberi keberkahan atas harta yang tersisa, dan semoga Allah menjadikan harta menjadi suci.  Aamiin..
            </td>
          </tr>
            </table>
          </td>          
         </tr>
        </table>
        <br>
            
        </page>				
                
        <?php	
        
        $content = ob_get_clean();		
        
        $html2pdf = new HTML2PDF('L', 'A4', 'fr');
        
        $html2pdf->writeHTML($content);
        //$html2pdf->Output('donation.pdf');
        $html2pdf->Output($config->path_doc.'donation_'.$header_id.'.pdf','F');
        exit(0);	
    }
}
