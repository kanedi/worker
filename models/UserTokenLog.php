<?php




class UserTokenLog extends \Phalcon\Mvc\Model
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
    public $user_id;
     
    /**
     *
     * @var string
     */
    public $token;
     
    /**
     *
     * @var string
     */
    public $created;
     
    /**
     *
     * @var string
     */
    public $deleted;
     
}
