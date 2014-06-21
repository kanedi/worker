<?php




class UserActivityLog extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id;
     
    /**
     *
     * @var integer
     */
    public $user_token_log_id;
     
    /**
     *
     * @var string
     */
    public $controller;
     
    /**
     *
     * @var string
     */
    public $action;
     
    /**
     *
     * @var string
     */
    public $data;
     
    /**
     *
     * @var string
     */
    public $created;
     
}
