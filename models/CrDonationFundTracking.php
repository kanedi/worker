<?php




class CrDonationFundTracking extends \Phalcon\Mvc\Model
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
    public $cr_donation_header_id;
    public $from_user_id;
    public $to_user_id;
    public $from_role;
    public $to_role;
    public $position_user_id;
    public $status;
    public $created;
    
    public function initialize()
    {
        $this->belongsTo('cr_donation_header_id', 'CrDonationHeader', 'id');
        $this->belongsTo('from_user_id', 'User', 'id');
    }
}
