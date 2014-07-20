<?php
/**
 * Created by PhpStorm.
 * User: martinadiyono
 * Date: 7/20/14
 * Time: 12:28 PM
 */

class SecurityTask extends \Phalcon\CLI\Task  {
    public function clearloginAction(){
        $ut = new UserToken();
        $ut->clearLogin();
    }
} 