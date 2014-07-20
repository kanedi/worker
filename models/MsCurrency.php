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

    public function renew($currency_code, $rate){
        try{
            //Create a transaction manager
            $manager = new TxManager();

            // Request a transaction
            $transaction = $manager->get();

            $this->setTransaction($transaction);
            $this->code = $currency_code;
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
