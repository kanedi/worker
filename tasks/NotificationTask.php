<?php
/**
 * Created by PhpStorm.
 * User: martinadiyono
 * Date: 7/10/14
 * Time: 1:55 PM
 */

use Phalcon\Validation\Validator\PresenceOf,
    Phalcon\Validation\Validator\Email;

class NotificationTask extends \Phalcon\CLI\Task{

    public function mainAction(){
        global $config;
        $client = Aws\Sqs\SqsClient::factory(array(
            'key'    => $config->aws->key,
            'secret' => $config->aws->secret,
            'region' => $config->aws->region
        ));

        $result = $client->createQueue(array('QueueName' => 'notification'));
        $url = $result->get('QueueUrl');

        while(true) {
            try{
                $res = $client->receiveMessage(array(
                    'QueueUrl'          => $url,
                    'WaitTimeSeconds'   => 10
                ));
                echo ".";
                if ($res->getPath('Messages')) {

                    foreach ($res->getPath('Messages') as $msg) {
                        $this->sendNotification($msg['Body']);
                        // Do something useful with $msg['Body'] here
                        $res = $client->deleteMessage(array(
                            'QueueUrl'      => $url,
                            'ReceiptHandle' => $msg['ReceiptHandle']
                        ));
                    }
                }
            }catch (Aws\SqsException $e1){
                echo "\n SqsException " . $e1->getMessage() . "\n";
            }catch(Exception $e2){
                echo "\n Exception " . $e2->getMessage() . "\n";
            }
        }
    }

    public function sendNotification($msg){
        global $config;
        $notification = json_decode($msg);
        echo "\n" . date("Y-m-d H:i:s") ."\t";
        if(isset($notification->email)){
            echo "sending Emial: ";
            $validation = new Phalcon\Validation();

            $validation->add('email', new Email(array(
                'message' => 'invalid email format'
            )));

            $messages = $validation->validate(array('email' => strtolower($notification->email->to)));
            if (count($messages)) {
                foreach ($messages as $message) {
                    echo $message . strtolower($notification->email->to);
                }
            }else{
                $mail = new Mail();

                if(isset($notification->email->attachment)){
                    if($notification->email->attachment->type == "html2pdf"){
                        $kPathUrl = '';
                        require_once(APPLICATION_PATH . '/library/html2pdf_v4.03/html2pdf.class.php');

                        if(isset($notification->email->attachment->paper)){
                            $html2pdf = new HTML2PDF(
                                $notification->email->attachment->paper->orientation,
                                explode("x",$notification->email->attachment->paper->size), 'fr');
                        }else{
                            $html2pdf = new HTML2PDF('L', 'A4', 'fr');
                        }

                        $html2pdf->writeHTML($notification->email->attachment->html);

                        $shaContent = sha1($notification->email->attachment->html);
                        $filename = 'donation_' . $shaContent . '.pdf';

                        $html2pdf->Output($config->path_doc . $filename, 'F');
                        $attachment = array(
                            'data' => $config->path_doc . $filename,
                            'filename' => "Donation Receipt.pdf"
                        );
                    }

                    $mail->send(
                        array(strtolower($notification->email->to)),
                        $notification->email->subject,
                        "empty",
                        array("content" => $notification->email->msg),
                        $attachment
                    );
                    unlink($config->path_doc . $filename);
                    echo "OK";
                }else{
                    $mail->send(
                        array(strtolower($notification->email->to)),
                        $notification->email->subject,
                        "empty",
                        array("content" => $notification->email->msg)
                    );
                    echo "OK";
                }
            }
            echo "\t";
        }

        if(isset($notification->sms)){
            echo "Sending SMS: ";
            $smsResults = simplexml_load_file("https://reguler.zenziva.net/apps/smsapi.php?userkey=exvj4c&passkey=92528&nohp=".$notification->sms->to."&pesan=".urlencode($notification->sms->msg));
            $status = $smsResults->message[0]->text;
            $balance = trim($smsResults->message[0]->balance);
            echo $status . " Balance: " . $balance;
            if($balance != ''){
                if($balance <= 500){
                    $mail = new Mail();
                    $mail->send(
                        array(strtolower("martin@dompetdhuafa.org")),
                        "Quota SMS SISA " . $balance,
                        "empty",
                        array("content" => "Sisa sms tinggal " . $balance . " Segera isi ulang lagi")
                    );
                }
            }

        }
        echo "\n";
    }
} 