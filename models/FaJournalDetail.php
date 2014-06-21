<?php




class FaJournalDetail extends \Phalcon\Mvc\Model
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
    public $fa_journal_header_id;
     
    /**
     *
     * @var integer
     */
    public $fa_account_id;
     
    /**
     *
     * @var string
     */
    public $ms_currency_code;
     
    /**
     *
     * @var double
     */
    public $currency_rate;
     
    /**
     *
     * @var double
     */
    public $debit;
     
    /**
     *
     * @var double
     */
    public $credit;
     
    /**
     *
     * @var string
     */
    public $is_active;
     
    /**
     *
     * @var string
     */
    public $created;
     
    /**
     *
     * @var string
     */
    public $updated;
     
    /**
     *
     * @var integer
     */
    public $created_user_id;
     
    /**
     *
     * @var integer
     */
    public $updated_user_id;
     
}
