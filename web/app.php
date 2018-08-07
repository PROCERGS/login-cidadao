<?php

use Symfony\Component\HttpFoundation\Request;
use LoginCidadao\CoreBundle\Security\Compatibility\RamseyUuidFeatureSet;

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';

require_once __DIR__.'/../app/AppKernel.php';

$kernel = new AppKernel('prod', false);

$uuidFactory = new \Ramsey\Uuid\UuidFactory(new RamseyUuidFeatureSet());
\Ramsey\Uuid\Uuid::setFactory($uuidFactory);
$generator = new \Qandidate\Stack\UuidRequestIdGenerator();
$stack = new \Qandidate\Stack\RequestId($kernel, $generator);

$kernel->loadClassCache();
$request = Request::createFromGlobals();

// This line is required after Symfony 2.8.44
Request::setTrustedHeaderName(Request::HEADER_FORWARDED, null);

$response = $stack->handle($request);
$response->send();
$kernel->terminate($request, $response);
