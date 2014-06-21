<?php

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class MainTask extends \Phalcon\CLI\Task
{
    public function mainAction() {
        echo "\nThis is the default task and the default action \n";
    }

	public function amqAction(){
		$connection = new AMQPConnection('192.168.56.101', 5672, 'martin', 'martinadi');
		$channel = $connection->channel();

		$channel->queue_declare('hello', false, false, false, false);

		$msg = new AMQPMessage(time() . ' Hello World!');
		$channel->basic_publish($msg, '', 'hello');

		echo " [x] Sent 'Hello World!'\n";
		$channel->close();
		$connection->close();	
	}
	
	public function receiverAction(){
		$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
		$channel = $connection->channel();

		$channel->queue_declare('hello', false, false, false, false);

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
