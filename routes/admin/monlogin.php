<?php

use \App\Http\Response;
use \App\Controller\Admin;

$router->get('/admin/monlogin', [
    'middlewares' => [
        'jwt-auth',
        'check-permission::admin',
    ],
    function ($request) {
        return new Response(200, Admin\MonLogin::getHome($request));
    }
]);
$router->post('/admin/monlogin', [
    'middlewares' => [
        'jwt-auth',
        'check-permission::admin',
    ],
    function ($request) {
        return new Response(200, Admin\MonLogin::getUserLogin($request));
    }
]);
