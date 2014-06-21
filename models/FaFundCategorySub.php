<?php




class FaFundCategorySub extends \Phalcon\Mvc\Model
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
    public $fa_fund_category_id;
     
    /**
     *
     * @var string
     */
    public $name;
    public function initialize()
    {
        $this->belongsTo('fa_fund_category_id', 'FaFundCategory', 'id');
    }
     
}
