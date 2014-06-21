<?php




class CrDonationRole extends \Phalcon\Mvc\Model
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
    public $order;
    
    public function initialize()
    {
        $this->belongsTo('ms_branch_id', 'MsBranch', 'id');
    }
}
