<?php




class SecAccess extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $sec_role_id;
     
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
    
    public function initialize()
    {
        $this->belongsTo('sec_role_id', 'SecRole', 'id');
    }
}
