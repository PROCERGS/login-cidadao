<?php

use Symfony\Component\Debug\Debug;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Request;
use LoginCidadao\CoreBundle\Security\Compatibility\RamseyUuidFeatureSet;

// If you don't want to setup permissions the proper way, just uncomment the following PHP line
// read http://symfony.com/doc/current/book/installation.html#configuration-and-setup for more information
//umask(0000);

$loader = require_once __DIR__.'/../app/autoload.php';

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/../.env');

Debug::enable();

$kernel = new AppKernel('dev', true);

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

$allowed = explode(',', getenv('DEV_ALLOWED'));
if (!IpUtils::checkIp($request->getClientIp(), $allowed)) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file.');
}

// This line is required after Symfony 2.8.44
Request::setTrustedHeaderName(Request::HEADER_FORWARDED, null);

$response = $stack->handle($request);
$response->send();
$kernel->terminate($request, $response);
