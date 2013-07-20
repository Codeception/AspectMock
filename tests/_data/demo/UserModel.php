<?php
namespace demo;

class UserModel {

    protected $name;

    static function tableName()
    {
        return "users";
    }

    public function __construct($data = array())
    {
        foreach ($data as $key => $value)
        {
            $this->$key = $value;
        }
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    function save()
    {
        throw new \PHPUnit_Framework_AssertionFailedError("I should not be called");
    }

}