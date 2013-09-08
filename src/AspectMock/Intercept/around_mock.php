<?php
function __amock_around($class, $declaredClass, $method, $params, $static, $closure) {
	$mocker = AspectMock\Kernel::getInstance()->getContainer()->getAspect('AspectMock\Core\Mocker');
	$invocation = new \AspectMock\Intercept\MethodInvocation();
	$invocation->setThis($class);
	$invocation->setMethod($method);
	$invocation->setArguments($params);
	$invocation->setClosure($closure);
    $invocation->isStatic($static);
    $invocation->setDeclaredClass($declaredClass);

	return $mocker->fakeMethodsAndRegisterCalls($invocation);
}
