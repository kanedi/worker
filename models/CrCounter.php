<?php




class CrCounter extends \Phalcon\Mvc\Model
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
    public $ms_branch_id;
    
    public $name;
    public $head_user_id; 
    /**
     *
     * @var string
     */
    public $address;
     
    /**
     *
     * @var string
     */
    public $city;
     
    /**
     *
     * @var string
     */
    public $province;
     
    /**
     *
     * @var string
     */
    public $country;
     
    /**
     *
     * @var string
     */
    public $begin_date;
     
    /**
     *
     * @var string
     */
    public $end_date;
    /**
     *
     * @var string
     */
    public $is_active;
    
    public function initialize()
    {
        $this->belongsTo('ms_branch_id', 'MsBranch', 'id');
        $this->belongsTo('head_user_id', 'User', 'id');
    }
}
