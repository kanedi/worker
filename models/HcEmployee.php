<?php




class HcEmployee extends \Phalcon\Mvc\Model
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
    public $ms_department_id;
     
    /**
     *
     * @var string
     */
    public $name;

    public function initialize()
    {
        $this->belongsTo('ms_department_id', 'MsDepartment', 'id');
    }
}
