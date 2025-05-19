<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});

$app->group('/api', function (RouteCollectorProxy $api) {
    $api->group('/artefacto', function (RouteCollectorProxy $endpoint) {
        $endpoint->get('/read[/{id}]', Artefacto::class . ':read'); // Ruta corregida
        $endpoint->post('', Artefacto::class . ':create');
        $endpoint->put('/{id}', Artefacto::class . ':update');
        $endpoint->delete('/{id}', Artefacto::class . ':delete');
        $endpoint->get('/filtrar/{pag}/{lim}', Artefacto::class . ':filtrar');
    });

      $api->group('/cliente', function (RouteCollectorProxy $endpoint) {
        $endpoint->get('/read[/{id}]', Cliente::class . ':read'); // Ruta corregida
        $endpoint->post('', Cliente::class . ':create');
        $endpoint->put('/{id}', Cliente::class . ':update');
        $endpoint->delete('/{id}', Cliente::class . ':delete');
        $endpoint->get('/filtrar/{pag}/{lim}', Cliente::class . ':filtrar');
    });


    $api->group('/administrador', function (RouteCollectorProxy $endpoint) {
    $endpoint->get('/read[/{id}]', Administrador::class . ':read');
    $endpoint->post('', Administrador::class . ':create');
    $endpoint->put('/{id}', Administrador::class . ':update');
    $endpoint->delete('/{id}', Administrador::class . ':delete');
});

});