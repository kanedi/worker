<?php

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Phalcon\Validation\Validator\PresenceOf,
    Phalcon\Validation\Validator\Email;

class ReportDonorTask extends \Phalcon\CLI\Task
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

    public function entryRMQAction()
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

    public function entryAction(){
        global $config;
        $client = Aws\Sqs\SqsClient::factory(array(
            'key'    => $config->aws->key,
            'secret' => $config->aws->secret,
            'region' => $config->aws->region
        ));

        $result = $client->createQueue(array('QueueName' => 'donor_report'));
        $url = $result->get('QueueUrl');

        while(true) {
            $res = $client->receiveMessage(array(
                'QueueUrl'          => $url,
                'WaitTimeSeconds'   => 10
            ));

            if ($res->getPath('Messages')) {

                foreach ($res->getPath('Messages') as $msg) {
                    $this->sendEmailDonor($msg['Body']);
                    // Do something useful with $msg['Body'] here
                    $res = $client->deleteMessage(array(
                        'QueueUrl'      => $url,
                        'ReceiptHandle' => $msg['ReceiptHandle']
                    ));
                }

            }
        }
    }

    public function sendEmailDonor($msg)
    {
        echo date("Y-m-d H:i:s") ."\t".$msg->body."\tSending Email:";
        $this->sendEmails($msg);
        echo "\n";
    }
    
    public function sendEmails($donor_id){
	try{
	    global $config;
	    $bulan = date('F', time());
	    $year = date('Y', time());
	    $response = array('success' => false, 'msg' =>'param required');
	    if(isset($year) && $year !='' && isset($donor_id) && $donor_id != ''){
		$response = array('success' => false, 'msg' =>'param required, email not found');
		$jadi = $this->generate($bulan,$year,$donor_id);
		$email = $jadi['email'];
		if($email != ''){
		    require_once(APPLICATION_PATH . '/library/html2pdf_v4.03/html2pdf.class.php');
		    $param = $jadi['data'];
		    $name = 'donor';
		    $t=$this->view->getRender('reports', $name, $param,
			function($view){
			    $view->setRenderLevel(View::LEVEL_LAYOUT);
			});
		    $html2pdf = new HTML2PDF('P', 'Letter', 'fr',true,'UTF-8',array(0, 0, 0, 0));
		    $html2pdf->writeHTML($t);
		    ob_end_clean();
		    $html2pdf->Output($config->path_doc.'reportdonor_' . $param['donor_id'] . '.pdf', 'F');
		    //end create file pdf
		    $fn = 'reportdonor_' . $param['donor_id'] . '.pdf';
		    $attach = array(
			'data' => $config->path_doc.'reportdonor_'. $param['donor_id'] .'.pdf',
			'filename' => $fn
		    );
		    
		    $validation = new Phalcon\Validation();
	
		    $validation->add('email', new Email(array(
			'message' => 'invalid email format'
		    )));
	
		    $messages = $validation->validate(array('email' => $email));
		    if (count($messages)) {
			foreach ($messages as $message) {
			    $response = array('success' => false, 'msg' =>'error, '.$message);
			}
		    }else{
			$mail = new Mail();
			$mail->send(
			    array(strtolower($email)),
			    "Dompet Dhuafa Donation Report",
			    "donorreport",
			    array("param" => $param , "hasil" => $param['data']),
			    $attach
			);
			echo "Send To Donor ID :".$donor_id.' , name :'.$param['donor_name'];
		    }
		    unlink($config->path_doc.'reportdonor_'. $param['donor_id'].'.pdf');   
		}
	    }       
	}catch (PDOException $e){
            echo $e->getMessage();
        }catch(Exception $e){
            echo $e->getMessage();
        }    
    }
    
    private function generate($bulan,$year,$donor_id){
        $donor = CrDonor::findFirst($donor_id);
        $par = array(
            'year' => $year,
            'cr_donor_id' => $donor_id,
        );
        $tembak = CrDonationHeader::getReportByDonor($this->request->get('token'),$par);
        $branch = MsBranch::getDetailId($donor->branch_current);
        $city = '';
        $prov = '';
        if($donor->city != ''){
            $city = ', '.$donor->city;
        }
        if($donor->province !=''){
            $prov = ', '.$donor->province;
        }
        $alamat = $donor->address.$city.$prov;
        $param = array(
            'donor_id' => $donor->public_id,
            'data' => $tembak['hasil'],
            'detail' => $tembak['detail'],
            'detail2' => $tembak['detail2'],
            'donor_name' => strtoupper($donor->name),
            'branch_name' => strtoupper($branch['name']),
            'branch_state' => strtoupper($branch['state']),
            'address' => strtoupper($alamat),
            'donor_npwp' => $donor->npwp,
            'gt'=>number_format($tembak['gt'],0,',','.'),
            'gtotal' => $tembak['gtotal'],
            'kata' => $tembak['kata'],
            'bg' => APPLICATION_PATH.'/views/reports/donasirekap.jpg',
            'year' => $year,
            'month' => $bulan
        );
        $response = array(
            'success' => true,
            'email' => $donor->email,
            'data'=>$param ,
            'hasil'=>$param['data'],
            'detail' => $param['detail'],
            'donor_id' => $donor->public_id,
            'donor_name' => $donor->name,
            'donor_npwp' => $donor->npwp,
            'gt'=>$tembak['gt'],
            'year' => $year,
            'month' => $bulan
        );
        return $response;
    }    
}
