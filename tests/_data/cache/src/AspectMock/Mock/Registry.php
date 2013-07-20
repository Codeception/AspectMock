<?php


namespace AspectMock\Mock;


trait Registry {
    public function registerClass($class, $params = array())
    {
        $class = ltrim($class,'\\');
        $this->classMap[$class] = $params;
        $this->classMethodCalls[$class] = array();
    }

    public function registerObject($object, $params = array())
    {
        $this->objectMap[spl_object_hash($object)] = $params;
        $this->objectMethodCalls[spl_object_hash($object)] = array();
    }

    public function clean()
    {
        $this->classMap = [];
        $this->objectMap = [];
    }
}