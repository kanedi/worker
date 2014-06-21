<?php
/**
 * Created by PhpStorm.
 * User: martinadiyono
 * Date: 2/18/14
 * Time: 5:27 PM
 */

namespace Library\Ozip;

use Phalcon\Exception;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager,
    Phalcon\Mvc\Model\Transaction\Failed as TxFailed,
    Phalcon\Mvc\Model\Query,
    UserToken, UserTokenLog;

class Auth {

    public $token;

    public $user_id;

    public function generateToken($user_id, $ip_address, $data){
        try{
            $DateHelper = new DateHelper();
            $token = sha1($user_id . $ip_address . time());
            $manager = new TxManager();
            $transaction = $manager->get();

            try{
                $deleteToken = $this->deleteToken($user_id);
            }catch (Exception $de){
                $transaction->rollback($de->getMessage());
            }

            $userToken = new UserToken();
            $userToken->setTransaction($transaction);
            $userToken->user_id = $user_id;
            $userToken->token = $token;
            $userToken->data = $data;
            $userToken->expired = $DateHelper->strToDateTime("now +3hour");
            if($userToken->save() == false){
                $transaction->rollback("Cannot save user token");
            }

            $userTokenLog = new UserTokenLog();
            $userTokenLog->setTransaction($transaction);
            $userTokenLog->user_id = $user_id;
            $userTokenLog->token = $token;
            $userTokenLog->created = $DateHelper->strToDateTime("now");
            if($userTokenLog->save() == false){
                $transaction->rollback("Cannot save user token log");
            }

            $transaction->commit();

            return $token;

        }catch (TxFailed $e){
            throw new Exception("Failed, reason: " . $e->getMessage());
        }
    }

    public function deleteToken($user_id){

        try{
            $DateHelper = new DateHelper();
            $manager = new TxManager();
            $transaction = $manager->get();

            $lastToken = UserToken::findFirst(array(
                'user_id = :user_id:',
                'bind' => array(
                    'user_id' => $user_id
                )
            ));

            if($lastToken){
                $lastToken->setTransaction($transaction);
                $lastTokenStr = $lastToken->token;
                if($lastToken->delete() == false){
                    $transaction->rollback("Cannot delete user token");
                }

                $lastTokenLog = UserTokenLog::findFirst(array(
                    'token = :token:',
                    'bind' => array(
                        'token' => $lastTokenStr
                    )
                ));
                if($lastTokenLog){
                    $lastTokenLog->setTransaction($transaction);

                    if($lastTokenLog->update(array('deleted' => $DateHelper->strToDateTime('now'))) == false){
                        $transaction->rollback("Cannot delete user token log");
                    }
                }else{
                    $transaction->rollback("You Don't have token log. please contact administrator");
                }
            }
            $transaction->commit();
            return true;
        }catch (TxFailed $e){
            throw new Exception($e->getMessage());
        }
    }

    public function getUserData($token){
        $this->token = $token;
        $tokenData = UserToken::findFirst(array(
            'token = :token:',
            'bind' => array(
                'token' => $token
            )
        ));
        if($tokenData){

            $this->user_id = $tokenData->user_id;

            $userData = array(
                'user_id' => $tokenData->user_id
            );

            $tokenDataData = json_decode($tokenData->data);
            foreach($tokenDataData as $key => $value){
                $userData[$key] = $value;
            }
            return $userData;
        }else{
            throw new Exception("INVALID_TOKEN");
        }
    }

    public function checkAccess($user_id, $controller, $action){
        global $di;

        if($action == 'myaccess'){
            return true;
        }

        if($controller == 'auth' && $action == 'getmenu'){
            return true;
        }

        $sql = "select
            *
        from
            SecAccess sa
                join
            SecRole sr ON sa.sec_role_id = sr.id
                join
            UserHasRole uhr ON sr.id = uhr.sec_role_id
        where
            uhr.user_id = :user_id:
                and sa.controller = :controller:
                and sa.action = :action:";
        // Instantiate the Query
        $query = new Query($sql, $di);

        $controllerActionList = $this->getControllerAction($controller);

        if(!isset($controllerActionList[$action])){
            throw new Exception("Please register your controller and action in the accessList.php");
        }

        // Execute the query returning a result if any
        $SecAccess = $query->execute(array(
            'user_id' => $user_id,
            'controller' => $controller,
            'action' => $controllerActionList[$action]
        ));

        if(count($SecAccess)){
            return true;
        }else{
            return false;
        }
    }

    public function getControllerAction($controllerName){
        $accessList = include __DIR__ . '/../../../app/config/accessList.php';

        $action = array();
        foreach($accessList as $al){
            if($al['controller'] == $controllerName){
                $action = $al['action'];
            }
        }
        return $action;
    }

    public function logActivity($user_id, $controller, $action, $data){

        if($action == 'myaccess'){
            return true;
        }

        $token_log = \UserTokenLog::findFirst(array(
            'user_id = :user_id: AND token = :token:',
            'bind' => array(
                'user_id' => $user_id,
                'token' => $this->token
            )
        ));

        $activity_log = new \UserActivityLog();
        $activity_log->user_token_log_id = $token_log->id;
        $activity_log->controller = $controller;
        $activity_log->action = $action;
        $activity_log->data = $data;
        $activity_log->created = date('Y-m-d H:i:s');
        $activity_log->save();
    }

    public function assignRole($user_id, $role){

    }

    public function getAccessList(){
        global $config;
        $accessConfig = $config->application->accessList;

        $accessList = array();
        foreach($accessConfig as $controller => $actions){
            foreach($actions as $action){
                $accessList[$controller][] =  $action;
            }
        }
        return $accessList;
    }

    public function createMenu($user_id, $arr){
        $return = array();
        if(isset($arr['access']) && !$this->checkAccess($user_id, $arr['access']['controller'], $arr['access']['action'])){
            unset($arr);
        }else{
            if(isset($arr['access'])){
                unset($arr['access']);
            }
            foreach($arr as $k => $v) {
                if(is_array($v)) {
                    $recur = $this->createMenu($user_id, $v);

                    if(count($recur)){
                        $return[$k] = $recur;
                    }else{
                        continue;
                    }
                }
                $return[$k] = $v;
            }
        }
        return $return;
    }
} 