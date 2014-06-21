<?php




class CrDonationBankHeader extends \Phalcon\Mvc\Model
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
    public $fa_bank_id;
    public $created;
    
    public function initialize()
    {
        $this->belongsTo('fa_bank_id', 'FaBank', 'id');
    }
}
