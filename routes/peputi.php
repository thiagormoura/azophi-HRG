<?php

use \App\Http\Response;
use \App\Controller\Peputi;

$router->get('/peputi', [
    'middlewares' => [
        'jwt-auth',
    ],
    function ($request) {
        return new Response(200, Peputi\Home::getHome($request));
    }
]);