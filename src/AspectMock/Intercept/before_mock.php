<?php
function __amock_before($class, $declaredClass, $method, $params, $static) {
    return \AspectMock\Core\Registry::$mocker->fakeMethodsAndRegisterCalls($class, $declaredClass, $method, $params, $static);
}

const __AM_CONTINUE__ = '__am_continue__';
