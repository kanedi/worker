<?php
/**
 * Created by PhpStorm.
 * User: martinadiyono
 * Date: 6/21/14
 * Time: 6:42 PM
 */

class ExampleTask extends \Phalcon\CLI\Task {

    public function sendEmailAction(){
        $mail = new Mail();
        $mail->send(
            array("martin@adiyono.com"),
            "Dompet Dhuafa Donation",
            "example",
            array("param" => "value")
        //$attach
        );
    }

} 