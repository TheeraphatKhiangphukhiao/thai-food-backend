<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->setBasePath('/shopwebapi');//************************************** ชื่อ folder นี้
$app->addErrorMiddleware(true, true, true);

require __DIR__ . '/dbconnect.php';//************************************** file connect database
require __DIR__ . '/api/goods.php';//************************************** file goods API
require __DIR__ . '/api/type.php';//*************************************** file type API
require __DIR__ . '/api/customer.php';//*********************************** file customer API
require __DIR__ . '/api/iorder.php';//************************************* file iorder API
require __DIR__ . '/api/admin.php';//*************************************** file shop API
require __DIR__ . '/api/basket.php';//*************************************** file basket API


$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->get('/ping', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Pong!!!");
    return $response;
});

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
    throw new HttpNotFoundException($request);
});

$app->run();