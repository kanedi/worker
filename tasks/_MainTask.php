<?php

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class MainTask extends \Phalcon\CLI\Task
{
    public function mainAction() {
        echo "\nThis is the default task and the default action \n";
    }

	public function amqAction(){
		$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
		$channel = $connection->channel();

		$channel->queue_declare('donation', false, false, false, false);

		$msg = new AMQPMessage(time() . ' Hello World!');
		$channel->basic_publish($msg, '', 'hello');

		echo " [x] Sent 'Hello World!'\n";
		$channel->close();
		$connection->close();	
	}
	
	public function receiverAction(){
		$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
		$channel = $connection->channel();

		$channel->queue_declare('donation_entry', false, true, false, false);

		echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";
		
		$callback = function($msg) {
			//sleep(10);
  			echo " [x] Received ", $msg->body, "\n";
		};

		$channel->basic_consume('donation_entry', '', false, true, false, false, $callback);

		while(count($channel->callbacks)) {
    			$channel->wait();
		}

	}
	
    private function sendemail($header_id,$token){
        $header = CrDonationHeader::findFirst($header_id);
        $detail = CrDonationDetail::find('cr_donation_header_id = '.$header_id);
        $gt=0;
        $date = date_create($header->trx_date);
        $ddd = date_format($date, 'd F Y');
	$localConfig = include('../app/config/local.php');
        $fp=$localConfig['fullPath'];
        $path = $fp.'api/crdonation/print/?token='.$token.'&id='.$header_id;
        $fn = 'donasi_'.$header->CrDonor->public_id.'_'.$header->id.'.pdf';
        $attach = array(
            'data' => $path,
            'filename' => $fn
        );
        foreach($detail as $dd){
            $gt = $gt+$dd->amount;
        }
        setlocale(LC_MONETARY, 'id_ID');
        $gt = money_format('%(#10n',$gt);
        if($header->CrDonor->email!='' || $header->CrDonor->email != null){
            $mail = new Mail();
            $mail->send(
                array($header->CrDonor->email),
                "Dompet Dhuafa Donation",
                "donationentry",
                array("header" => $header,"detail"=>$detail,'grandtotal'=>$gt,'date'=>$ddd),
                $attach
            );
        }        
    }
    
    private function sendsms($header_id){
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
}
