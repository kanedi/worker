<?php




class CrDonationRecap extends \Phalcon\Mvc\Model
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
    public $head_user_id;
    public $created;
    public $note;
    public $status;
    
    public function initialize()
    {
        $this->belongsTo('head_user_id', 'User', 'id');
    }
}
