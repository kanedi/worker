<?php




class MsDirectorate extends \Phalcon\Mvc\Model
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
    public $ms_branch_id;
     
    /**
     *
     * @var string
     */
    public $name;
    
    public function initialize()
    {
        $this->belongsTo('ms_branch_id', 'MsBranch', 'id');
    }
     
}
