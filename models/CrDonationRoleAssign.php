<?php




class CrDonationRoleAssign extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $user_id;
     
    /**
     *
     * @var integer
     */
    public $cr_donation_role_id;
    
    public function initialize()
    {
        $this->belongsTo('user_id', 'User', 'id');
        $this->belongsTo('cr_donation_role_id', 'CrDonationRole', 'id');
    }
}
