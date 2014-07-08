<?php
/**
 * Created by PhpStorm.
 * User: martinadiyono
 * Date: 7/8/14
 * Time: 1:54 PM
 */

use Aws\Sqs\SqsClient;

class SqsTask extends \Phalcon\CLI\Task {

    public function donationAction(){

        global $config;
        $client = SqsClient::factory(array(
            'key'    => $config->aws->key,
            'secret' => $config->aws->secret,
            'region' => $config->aws->region
        ));

        $result = $client->createQueue(array('QueueName' => 'desique'));
        $url = $result->get('QueueUrl');

        while(true) {
            $res = $client->receiveMessage(array(
                'QueueUrl'          => $url,
                'WaitTimeSeconds'   => 10
            ));

            echo "+\n";

            if ($res->getPath('Messages')) {

                foreach ($res->getPath('Messages') as $msg) {
                    echo "Received Msg: ".$msg['Body'];
                }
                // Do something useful with $msg['Body'] here
                $res = $client->deleteMessage(array(
                    'QueueUrl'      => $url,
                    'ReceiptHandle' => $msg['ReceiptHandle']
                ));
            }
        }

    }

} 