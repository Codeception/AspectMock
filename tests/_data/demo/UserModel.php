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

    public function __call($name, $args = array())
    {
        if ($name == 'renameUser') return 'David Blane';
    }

    public static function __callStatic($name, $args)
    {
        if ($name == 'defaultRole') return "member";
    }

    public function dump()
    {
        return file_put_contents(\Codeception\Configuration::logDir().'user.txt',$this->name);
    }

}