<?php
namespace AspectMock\Intercept;

class MethodInvocation
{
    protected $method;
    protected $arguments;
    protected $isStatic;
    protected $declaredClass;

    /**
     * @param mixed $declaredClass
     */
    public function setDeclaredClass($declaredClass)
    {
        $this->declaredClass = $declaredClass;
    }

    /**
     * @return mixed
     */
    public function getDeclaredClass()
    {
        return $this->declaredClass;
    }


    /**
     * @param mixed $isStatic
     */
    public function isStatic($isStatic = null)
    {
        if ($isStatic === null) return $this->isStatic;
        $this->isStatic = $isStatic;
    }


    protected $class;

    /**
     * @param mixed $class
     */
    public function setThis($class)
    {
        $this->class = $class;
    }

    /**
     * @return mixed
     */
    public function getThis()
    {
        return $this->class;
    }

    /**
     * @param mixed $closure
     */
    public function setClosure($closure)
    {
        $this->closure = $closure;
    }

    /**
     * @return mixed
     */
    public function getClosure()
    {
        return $this->closure;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $params
     */
    public function setArguments($params)
    {
        $this->arguments = $params;
    }

    /**
     * @return mixed
     */
    public function getArguments()
    {
        return $this->arguments;
    }

}