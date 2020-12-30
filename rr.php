<?php

require __DIR__ . "/vendor/autoload.php";

use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;

$relay = new Spiral\Goridge\StreamRelay(STDIN, STDOUT);
$psr7 = new Spiral\RoadRunner\PSR7Client(new Spiral\RoadRunner\Worker($relay));

$app = require_once __DIR__ . '/bootstrap/app.php';

while ($req = $psr7->acceptRequest()) {
	try {
		$httpFoundationFactory = new HttpFoundationFactory();
		$request = Illuminate\Http\Request::createFromBase($httpFoundationFactory->createRequest($req));
		
		$response = $app->dispatch($request);
		
		$psr7factory = new DiactorosFactory();
		$psr7response = $psr7factory->createResponse($response);
		$psr7->respond($psr7response);
	} catch (\Throwable $e) {
		$psr7->getWorker()->error((string)$e);
	}
}
