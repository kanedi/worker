<?php


use Phalcon\Mvc\Model\Behavior\SoftDelete;

class CrDonationDetail extends \Phalcon\Mvc\Model
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
    public $fa_fund_category_sub_id;
    public $ms_currency_code;
    public $currency_rate;
    public $currency_amount;
    public $amount;
    public $created;
    public $updated;
    
    public function initialize()
    {
        $this->belongsTo('cr_donation_header_id', 'CrDonationHeader', 'id');
        $this->belongsTo('ms_currency_code', 'MsCurrency', 'code');
        $this->belongsTo('fa_fund_type_id', 'FaFundType', 'id');
        $this->addBehavior(new SoftDelete(
            array(
                'field' => 'is_deleted',
                'value' => 1
            )
        ));
    }
}
