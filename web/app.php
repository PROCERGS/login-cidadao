<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use LoginCidadao\CoreBundle\Security\Compatibility\RamseyUuidFeatureSet;

$loader = require_once __DIR__.'/../app/autoload.php';

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/../.env');

$kernel = new AppKernel('prod', false);

$uuidFactory = new \Ramsey\Uuid\UuidFactory(new RamseyUuidFeatureSet());
\Ramsey\Uuid\Uuid::setFactory($uuidFactory);
$generator = new \Qandidate\Stack\UuidRequestIdGenerator();
$stack = new \Qandidate\Stack\RequestId($kernel, $generator);

try {
    $trustedProxies = explode(',', getenv('TRUSTED_PROXIES'));
    Request::setTrustedProxies($trustedProxies);
} catch (Exception $ex) {
    http_response_code(500);
    exit('Invalid configuration');
}

$request = Request::createFromGlobals();

// This line is required after Symfony 2.8.44
Request::setTrustedHeaderName(Request::HEADER_FORWARDED, null);

$response = $stack->handle($request);
$response->send();
$kernel->terminate($request, $response);
