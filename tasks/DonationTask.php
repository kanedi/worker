<?php

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Phalcon\Validation\Validator\PresenceOf,
    Phalcon\Validation\Validator\Email;

class DonationTask extends \Phalcon\CLI\Task
{
    public function mainAction()
    {
        echo "\nThis is the default task and the default action \n";
    }

    public function simAction($params){
        global $config;
        $connection = new AMQPConnection($config->rabbitmq->host, $config->rabbitmq->port, $config->rabbitmq->username, $config->rabbitmq->password, $config->rabbitmq->vhost);
        $channel = $connection->channel();

        $channel->queue_declare('donation_entry', false, true, false, false);

        $msg = new AMQPMessage($params);
        $channel->basic_publish($msg, '', 'donation_entry');

        echo " [x] Sent '".$params."'\n";
        $channel->close();
        $connection->close();
    }

    public function entryAction()
    {
        global $config;
        $connection = new AMQPConnection($config->rabbitmq->host, $config->rabbitmq->port, $config->rabbitmq->username, $config->rabbitmq->password,$config->rabbitmq->vhost);
        $channel = $connection->channel();

        $channel_name = "donation_entry";

        $channel->queue_declare($channel_name, false, true, false, false);

        echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

        $callback = function ($msg) {
            //sleep(10);
            echo " [x] Received ", $msg->body, "\n";
        };

        $channel->basic_consume($channel_name, '', false, true, false, false, array($this, 'callback'));

        while (count($channel->callbacks)) {
            $channel->wait();
        }

    }

    public function callback($msg)
    {
        echo date("Y-m-d H:i:s") ."\t".$msg->body."\tSending Email:";
        $this->sendEmail($msg->body);
        echo "\tSending SMS:";
        $this->sendSms($msg->body);
        echo "\n";
    }

    public function sendEmail($header_id)
    {
        global $config;

        $header = CrDonationHeader::findFirst($header_id);
        if($header){
            $detail = CrDonationDetail::find('cr_donation_header_id = ' . $header_id);
            $gt = 0;
            $date = date_create($header->trx_date);
            $ddd = date_format($date, 'd F Y');

            $fn = 'donation_' . $header->id . '.pdf';

            $this->kwitansi($header_id);

            $attach = array(
                'data' => $config->path_doc . $fn,
                'filename' => $fn
            );

            foreach ($detail as $dd) {
                $gt = $gt + $dd->amount;
            }
            setlocale(LC_MONETARY, 'id_ID');
            $gt = money_format('%(#10n', $gt);
            if ($header->CrDonor->email != '' || $header->CrDonor->email != null) {
                $validation = new Phalcon\Validation();

                $validation->add('email', new Email(array(
                    'message' => 'invalid email format'
                )));

                $messages = $validation->validate(array('email' => $email));
                if (count($messages)) {
                    foreach ($messages as $message) {
                        echo $message . " DonorID:" . $header->CrDonor;
                    }
                }else{
                    $mail = new Mail();
                    $mail->send(
                        array(strtolower($header->CrDonor->email)),
                        "Dompet Dhuafa Donation",
                        "donationentry",
                        array("header" => $header, "detail" => $detail, 'grandtotal' => $gt, 'date' => $ddd),
                        $attach
                    );
                }
            }
            unlink($config->path_doc . $fn);
        }else{
            echo "No Donation Found";
        }

    }

    public function sendSms($header_id)
    {
        $header = CrDonationHeader::findFirst($header_id);
        $detail = CrDonationDetail::find('cr_donation_header_id = ' . $header_id);
        $gt = 0;
        foreach ($detail as $dd) {
            $gt = $gt + $dd->amount;
        }
        setlocale(LC_MONETARY, 'id_ID');
        $gt = money_format('%(#1n', $gt);
        $pesan = 'Yang Terhormat ' . $header->CrDonor->name . ', Donasi anda senilai' . $gt . ' sudah kami terima, semoga diberikan keberkahan atas harta yang tersisa';
        $pesan = base64_encode($pesan);
        $no = $header->CrDonor->hp;
        //$kd_cabang = 1;	
	$kd_cabang = $header->User->HcEmployee->MsDepartment->MsDirectorate->ms_branch_id;
        if ($no != '' || $no != null) {
            $ch = curl_init();

            curl_setopt_array(
                $ch, array(
                CURLOPT_URL => 'http://donatur.dompetdhuafa.org/smsserver/insertsms.php?nomor=' . $no . '&pesan=' . $pesan . '&kd_cabang=' . $kd_cabang,
                CURLOPT_RETURNTRANSFER => true
            ));
            $output = curl_exec($ch);
            echo $output;
            curl_close($ch);
        }
    }

    public function genKwitansiAction(){
        $this->kwitansi('1164');
    }

    public function kwitansi($header_id)
    {
        global $config;

        $kPathUrl = '';

        require_once(APPLICATION_PATH . '/library/html2pdf_v4.03/html2pdf.class.php');
        //$header_id = $this->request->get('id');
        $header = CrDonationHeader::findFirst($header_id);
        $detail = CrDonationDetail::find('cr_donation_header_id = ' . $header_id);
        $gt = 0;
        foreach ($detail as $dd) {
            $gt = $gt + $dd->amount;
        }
        setlocale(LC_MONETARY, 'id_ID');

        $date = date_create($header->trx_date);
        $ddd = date_format($date, 'd F Y');

        $content = $this->view->getRender('pdfTemplates', "donation", array(
            'ddd' => $ddd,
            'header' => $header,
            'detail' => $detail,
            'gt' => $gt,
            'logo' => APPLICATION_PATH . "/assets/logo4pdf.jpg"
        ));

        $html2pdf = new HTML2PDF('L', 'A4', 'fr');

        $html2pdf->writeHTML($content);
        //$html2pdf->Output('donation.pdf');
        $html2pdf->Output($config->path_doc . 'donation_' . $header_id . '.pdf', 'F');
    }
}
