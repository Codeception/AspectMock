<?php
namespace demo;

class UserModel {

    protected $name;
    protected $info;

    private static $topSecret = 'awesome';

    public static function getTopSecret()
    {
        throw new \PHPUnit_Framework_AssertionFailedError("I am not going to tell you");
    }

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

    /**
     * @param mixed $name
     */
    public function setNameAndInfo($name, $info)
    {
        $this->name = $name;
        $this->info = $info;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNameAndInfo()
    {
        return array($this->name, $this->info);
    }

    /**
     * @param array $info
     */
    public function setInfo($info)
    {
        $this->info = $info;
        return $this;
    }

    /**
     * @return array
     */
    public function getInfo($info)
    {
        return $this->info;
    }

    function save()
    {
        throw new \PHPUnit\Framework\AssertionFailedError("I should not be called");
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