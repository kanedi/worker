<?php




class CrCounterOperator extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $cr_counter_id;
     
    /**
     *
     * @var integer
     */
    public $user_id;
    public function initialize()
    {
        $this->belongsTo('cr_counter_id', 'CrCounter', 'id');
        $this->belongsTo('user_id', 'User', 'id');
    }
     
}
