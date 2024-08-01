<?php

namespace App\Controller\Papem;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Http\Request;
use App\Model\Papem\PapemModel;
use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;

class PapemController extends LayoutPage
{
    public static function getPapem(Request $request)
    {
        $resultado = [
            "clinica" => [
                "title" => "CLÍNICA MÉDICA",
                "pacientes" => 0,
                "tempo" => 0,
                "color" => "success"
            ],
            "ortopedia" => [
                "title" => "ORTOPEDIA",
                "pacientes" => 0,
                "tempo" => 0,
                "color" => "success"
            ],
            "cardio" => [
                "title" => "CARDIOLOGIA",
                "pacientes" => 0,
                "tempo" => 0,
                "color" => "success"
            ]
        ];

        $converter = [
            "900290" => "clinica",
            "900289" => "ortopedia",
            "900288" => "cardio"
        ];

        $fila = PapemModel::getFila();
        if (count($fila) > 0) {

            /*  Trata Média Aritimética das Filas   */
            foreach ($fila as $linha) {

                $type = $converter[$linha['FILA_COD']];

                $resultado[$type]['pacientes']++;
                if ($resultado[$type]['tempo'] < $linha['tempo_espera_total'])
                    $resultado[$type]['tempo'] = $linha['tempo_espera_total'];

                if ($resultado[$type]['tempo'] >= 10 & $resultado[$type]['tempo'] < 20)
                    $resultado[$type]['color'] = 'warning';

                elseif ($resultado[$type]['tempo'] >= 20)
                    $resultado[$type]['color'] = 'danger';
            }
        }

        $cards = "";
        foreach ($resultado as $props) {
            $cards .= View::render('papem/card_painel', [
                "title" => $props['title'],
                "color" => $props['color'],
                "pacientes" => $props['pacientes'],
                "time" => $props['tempo'] . " Min"
            ]);
        }

        $content =  View::render('papem/painel', [
            "cards" => $cards
        ]);

        return self::getPanelLayout('Papem', $content);
    }

    public static function getPapemRecep(Request $request)
    {
        $resultado = [
            "classificacao" => [
                "title" => "Classificação de Risco",
                "tempo" => 0,
                "color" => "success"
            ],
            "clinica" => [
                "title" => "Clínica Médica",
                "tempo" => 0,
                "color" => "success"
            ]
        ];


        $fetchClinica = PapemModel::getClinicaPapemRecep();
        foreach ($fetchClinica as $linha) {
            if ($linha['tempo_espera_total'] >  $resultado['clinica']['tempo'] && $linha['tempo_espera_total'] < 360)
                $resultado['clinica']['tempo'] = $linha['tempo_espera_total'];

            // AMARELO
            if ($resultado['clinica']['tempo'] > 60 && $resultado['clinica']['tempo'] < 120)
                $resultado['clinica']['color'] = "warning";

            // VERMELHO
            elseif ($resultado['clinica']['tempo'] >= 120)
                $resultado['clinica']['color'] = "danger";
        }

        $fetchClassificacao = PapemModel::getClassificaoPapemRecep();
        foreach ($fetchClassificacao as $linha) {
            if ($linha['tempo_espera_total'] >  $resultado['classificacao']['tempo'])
                $resultado['classificacao']['tempo'] = $linha['tempo_espera_total'];

            // AMARELO
            if ($resultado['classificacao']['tempo'] > 60 && $resultado['classificacao']['tempo'] < 120)
                $resultado['classificacao']['color'] = "warning";

            // VERMELHO
            elseif ($resultado['classificacao']['tempo'] >= 120)
                $resultado['classificacao']['color'] = "danger";
        }

        $cards = "";
        foreach ($resultado as $props) {
            $cards .= View::render('papem/card_painel_recep', [
                "title" => $props['title'],
                "color" => $props['color'],
                "time" => $props['tempo']
            ]);
        }

        $content =  View::render('papem/papem_recep', [
            "cards" => $cards
        ]);

        return parent::getPanelLayout('Papem Recepção', $content);
    }
}
