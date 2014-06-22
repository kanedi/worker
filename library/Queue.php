<?php
/**
 * Created by PhpStorm.
 * User: martinadiyono
 * Date: 6/22/14
 * Time: 10:09 PM
 */

namespace Library;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Queue {

    public static function queue($channel_name, $message){
        global $config;
        $connection = new AMQPConnection(
            $config->rabbitmq->host,
            $config->rabbitmq->port,
            $config->rabbitmq->username,
            $config->rabbitmq->password,
            $config->rabbitmq->vhost
        );
        $channel = $connection->channel();

        $channel->queue_declare($channel_name, false, true, false, false);

        $msg = new AMQPMessage($message);
        $channel->basic_publish($msg, '', $channel_name);
    }

} 