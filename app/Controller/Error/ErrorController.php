<?php

namespace App\Controller\Error;

use \App\Controller\Layout\Layout as LayoutPage;
use App\Utils\View;

class ErrorController extends LayoutPage
{

    public static function getError($httpCode, $content)
    {
        $contentPage = View::render('layout/centralservicos/errors/default_error', [
            'error-code' => $httpCode,
            'error-message' => $content,
        ]);

        return parent::getPage('Central Servicos - Error ' . $httpCode, 'home', $contentPage, $request ?? null);
    }
}
