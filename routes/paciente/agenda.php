<?php

use \App\Http\Response;
use \App\Controller\Agenda;

$router->get('/agenda', [
  function ($request) {
    return new Response(200, Agenda\Home::getHome());
  }
]);