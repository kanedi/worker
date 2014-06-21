<?php




class FaBank extends \Phalcon\Mvc\Model
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
    public $fa_account_id;
    
    public $fa_fund_type_id;
     
    /**
     *
     * @var string
     */
    public $bank_name;
     
    /**
     *
     * @var string
     */
    public $bank_acc_no;
     
    /**
     *
     * @var string
     */
    public $bank_acc_name;
    public function initialize()
    {
        $this->belongsTo('fa_fund_type_id', 'FaFundType', 'id');
    }    
}
