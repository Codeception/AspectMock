<?php

declare(strict_types=1);

namespace AspectMock\Intercept;

class MethodInvocation
{
    public $closure;

    protected $method;

    protected $arguments;

    protected $isStatic;

    protected $declaredClass;

    protected $class;

    /**
     * @param mixed $declaredClass
     */
    public function setDeclaredClass($declaredClass): void
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

    /**
     * @param mixed $class
     */
    public function setThis($class): void
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
    public function setClosure($closure): void
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
    public function setMethod($method): void
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
    public function setArguments($params): void
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