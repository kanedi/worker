<?php


use Phalcon\Mvc\Model\Validator\Email as Email;

class CrDonorTag extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id;
     
    
    public $cr_donor_id;
     
    /**
     *
     * @var integer
     */
    public $tag;
     
    /**
     * Validations and business logic
     */
     public function initialize()
    {
        $this->belongsTo('cr_donor_id', 'CrDonor', 'id');
    }

}
