<?php
function __amock_before($class, $declaredClass, $method, $params, $static) {
	$mocker = AspectMock\Kernel::getInstance()->getContainer()->getAspect('AspectMock\Core\Mocker');
	$invocation = new \AspectMock\Intercept\MethodInvocation();
	$invocation->setThis($class);
	$invocation->setMethod($method);
	$invocation->setArguments($params);
    $invocation->isStatic($static);
    $invocation->setDeclaredClass($declaredClass);

	return $mocker->fakeMethodsAndRegisterCalls($invocation);
}

const __AM_CONTINUE__ = '__am_continue__';
