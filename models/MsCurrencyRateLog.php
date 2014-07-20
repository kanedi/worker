<?php


use Phalcon\Mvc\Model\Behavior\Timestampable;

class MsCurrencyRateLog extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id;
     
    /**
     *
     * @var string
     */
    public $fa_currency_code;
     
    /**
     *
     * @var double
     */
    public $rate;
     
    /**
     *
     * @var string
     */
    public $created;

    public function initialize()
    {
        $this->addBehavior(new Timestampable(
            array(
                'beforeCreate' => array(
                    'field' => 'created',
                    'format' => 'Y-m-d H:i:s'
                )
            )
        ));
    }
}
