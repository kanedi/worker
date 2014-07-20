<?php




class UserToken extends \Phalcon\Mvc\Model
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
    public $data;
     
    /**
     *
     * @var string
     */
    public $ip_address;
     
    /**
     *
     * @var string
     */
    public $expired;

    public function clearLogin(){
        $this->query('TRUNCATE table user_token;');
    }
}
