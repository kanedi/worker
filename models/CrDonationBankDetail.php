<?php




class CrDonationBankDetail extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id;
    public $cr_donation_bank_header_id;
    public $cr_donation_header_id;
    public $trx_date;
    public $messages;
    public $amount;
    
    public function initialize()
    {
        $this->belongsTo('cr_donation_bank_header_id', 'CrDonationBankHeader', 'id');
    }
}
