<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__.'/vendor/autoload.php';

$app = AppFactory::create();
$app->setBasePath($_SERVER['BASE'] ?? '');

$app->any('/[{path:.*}]', function (Request $request, Response $response, array $args) {

    if (!isset($args['path']) || !strchr($args['path'], 'api')) {
        $response->getBody()->write('Change URL!');

        return $response;
    }

    $host = 'https://api-studentski.bidlag.com/';

    $headers = [];
    foreach ($request->getHeaders() AS $headerName => $headerValue) {
        $headers[] = $headerName.': '.implode(', ', $headerValue);
    }

    $curl = curl_init($host.$args['path']);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    if (in_array($request->getMethod(), ['POST', 'PUT'], true)) {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request->getParsedBody());
    }


    $data = curl_exec($curl);

    curl_close($curl);

    $response->getBody()->write($data);

    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
