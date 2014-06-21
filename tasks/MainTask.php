<?php

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class MainTask extends \Phalcon\CLI\Task
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
			sleep(10);
  			echo " [x] Received ", $msg->body, "\n";
		};

		$channel->basic_consume('hello', '', false, true, false, false, $callback);

		while(count($channel->callbacks)) {
    			$channel->wait();
		}

	}

    public function sendEmail(){
        $mail = new Mail();
        $mail->send(
            array("martin@adiyono.com"),
            "Dompet Dhuafa Donation",
            "donationentry",
            array("header" => $header,"detail"=>$detail,'grandtotal'=>$gt,'date'=>$ddd)
        //$attach
        );

    }
}
