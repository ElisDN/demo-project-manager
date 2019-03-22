<?php

declare(strict_types=1);

use Slim\Http\Request;
use Slim\Http\Response;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

http_response_code(500);

(function () {
    $app = new \Slim\App([
        'settings' => [
            'addContentLengthHeader' => false,
            'displayErrorDetails' => (bool)getenv('APP_DEBUG'),
        ],
    ]);

    $app->get('/', function (Request $request, Response $response) {
        return $response->withJson([
            'name' => 'Manager',
            'param' => $request->getQueryParam('param'),
        ]);
    });

    $app->run();
})();