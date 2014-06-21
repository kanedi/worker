<?php




class FaPpdDetail extends \Phalcon\Mvc\Model
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
    public $fa_ppd_header_id;
     
    /**
     *
     * @var integer
     */
    public $ms_department_id;
     
    /**
     *
     * @var integer
     */
    public $fa_account_id;
     
    /**
     *
     * @var string
     */
    public $description;
     
    /**
     *
     * @var double
     */
    public $amount;
     
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
     * @var string
     */
    public $attachment;
     
}
