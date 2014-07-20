<?php

use Phalcon\Mvc\Model\Transaction\Manager as TxManager,
    Phalcon\Mvc\Model\Transaction\Failed as TxFailed,
    Phalcon\Mvc\Model\Behavior\Timestampable;

class MsCurrency extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var string
     */
    public $code;
     
    /**
     *
     * @var string
     */
    public $name;
     
    /**
     *
     * @var double
     */
    public $rate;
     
    /**
     *
     * @var string
     */
    public $updated;

    public function initialize()
    {
        $this->addBehavior(new Timestampable(
            array(
                'beforeSave' => array(
                    'field' => 'updated',
                    'format' => 'Y-m-d H:i:s'
                )
            )
        ));
    }

    public $currency_name = array(
        'AUD' => "Australian dollar",
        'BND' => "Brunei dollar",
        'CAD' => "Canadian dollar",
        'CHF' => "Swiss franc",
        'CNY' => "Chinese yuan",
        'DKK' => "Danish krone",
        'EUR' => "EURO",
        'GBP' => "Pound sterling",
        'HKD' => "Hong Kong dollar",
        'IDR' => "Indonesian rupiah",
        'JPY' => "Japanese yen",
        'KRW' => "South Korean won",
        'KWD' => "Kuwaiti dinar",
        'MYR' => "Malaysian ringgit",
        'NOK' => "Norwegian krone",
        'NZD' => "New Zealand dollar",
        'PGK' => "Papua New Guinean kina",
        'PHP' => "Philippine peso",
        'SAR' => "Saudi riyal",
        'SEK' => "Swedish krona",
        'SGD' => "Singapore dollar",
        'THB' => "Thai baht",
        'USD' => "United States dollar"
    );

    public function renew($currency_code, $rate){
        try{
            //Create a transaction manager
            $manager = new TxManager();

            // Request a transaction
            $transaction = $manager->get();

            $this->setTransaction($transaction);
            $this->code = $currency_code;
            $this->name = $this->currency_name[$currency_code];
            $this->rate = $rate;

            if(!$this->save()){
                $transaction->rollback("Cannot save exchange rate");
            }

            $cur_log = new MsCurrencyRateLog();
            $cur_log->setTransaction($transaction);
            $cur_log->fa_currency_code = $currency_code;
            $cur_log->rate = $rate;

            if(!$cur_log->save()){
                $transaction->rollback("Cannot save exchange rate log");
            }

            $transaction->commit();
        }catch (TxFailed $e){
            echo $e->getMessage();
        }
    }
}
