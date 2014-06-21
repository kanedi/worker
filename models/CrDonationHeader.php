<?php


use Phalcon\Mvc\Model\Behavior\SoftDelete;

class CrDonationHeader extends \Phalcon\Mvc\Model
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
    public $cr_donor_id;
    public $cr_counter_id;
    public $cr_donation_bank_detail_id;
    /**
     *
     * @var integer
     */
    public $user_id;
    public $trx_date;
    public $via;
    public $created;
    public $updated;
    public function initialize()
    {
        $this->belongsTo('cr_donor_id', 'CrDonor', 'id');
        $this->belongsTo('cr_counter_id', 'CrCounter', 'id');
        $this->belongsTo('user_id', 'User', 'id');
        $this->belongsTo('cr_donation_bank_detail_id', 'CrDonationBankDetail', 'id');
        $this->addBehavior(new SoftDelete(
            array(
                'field' => 'is_deleted',
                'value' => 1
            )
        ));
    }
     
}
