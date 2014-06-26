<?php


use Phalcon\Mvc\Model\Validator\Email as Email;

class CrDonor extends \Phalcon\Mvc\Model
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
    public $name;
     
    /**
     *
     * @var string
     */
    public $email;
     
    /**
     *
     * @var string
     */
    public $hp;
     
    /**
     *
     * @var string
     */
    public $address;
     
    /**
     *
     * @var string
     */
    public $city;
     
    /**
     *
     * @var string
     */
    public $province;
     
    /**
     *
     * @var string
     */
    public $country;
     
    /**
     *
     * @var integer
     */
    public $branch_origin;
     
    /**
     *
     * @var integer
     */
    public $branch_current;
     
    /**
     * Validations and business logic
     */
    public function validation()
    {
        //if($this->email!=null){
        //    $this->validate(
        //        new Email(
        //            array(
        //                "field"    => "email",
        //                //"required" => false,
        //            )
        //        )
        //    );
        //    if ($this->validationHasFailed() == true) {           
        //        return false;
        //    }
        //}        
    }

}
