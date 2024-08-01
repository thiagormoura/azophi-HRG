<?php

use \App\Http\Response;
use \App\Controller\Check_Exame;
use \ShowPDF;

$router->get('/check_exame', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::admin,check-exame',
    ],
    function ($request) {
        return new Response(200, Check_Exame\Check_ExameController::getHome($request));
    }
]);

$router->post('/check_exame/getPacientes', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::admin,check-exame',
    ],
    function ($request) {
        return new Response(200, Check_Exame\Check_ExameController::getPacientes($request), 'application/json');
    }
]);

$router->post('/check_exame/getPacienteExamesModal', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::admin,check-exame',
    ],
    function ($request) {
        return new Response(200, Check_Exame\Check_ExameController::getPacienteExamesModal($request));
    }
]);

$router->get('/check_exame/getFile/{exame}/{registro}', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::admin,check-exame',
    ],
    function ($request, $exame, $registro) {
        return new Response(200, Check_Exame\Check_ExameController::getFile($request, $exame, $registro), 'application/pdf');
    }
]);

$router->get('/check_exame/getAllFiles/{dataInicial}/{dataFinal}/{registro}', [
    'middlewares' => [
        'jwt-auth',
        'maintenance',
        'check-permission::admin,check-exame',
    ],
    function ($request, $dataInicial, $dataFinal, $registro) {
        $zipData = Check_Exame\Check_ExameController::getAllFilesFromPaciente($request, $dataInicial, $dataFinal, $registro);
        return new Response(200, $zipData['nomePaciente'], 'application/zip', [
            'Content-Length' => $zipData['size']
        ]);
    }
]);