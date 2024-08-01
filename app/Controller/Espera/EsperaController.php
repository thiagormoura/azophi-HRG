<?php

namespace App\Controller\Espera;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Http\Request;
use App\Model\Espera\EsperaModel;
use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;

class EsperaController extends LayoutPage
{
    public static function getEspera(Request $request)
    {
        $locals = [
            "classificacao" => [
                "tipo" => "classificacao",
                "hasReavaliation" => false,
                "class" => "col-12",
                "style" => "padding-top: 5px !important;",
                "img-id" => "img-triagem",
                "img" => URL . "/resources/img/espera/triagem_white.png",
                "titulo-captalize" => "Triagem",
                "numero-pacientes" => 0,
                "tempo" => 0,
                "time" => ""
            ],
            "recepcao" => [
                "tipo" => "recepcao",
                "hasReavaliation" => false,
                "class" => "col-12",
                "style" => "",
                "img-id" => "recepcao",
                "img" => URL . "/resources/img/espera/reception_white.png",
                "titulo-captalize" => "Recepção",
                "numero-pacientes" => 0,
                "tempo" => 0,
                "time" => ""
            ],
            "clinica" => [
                "tipo" => "clinica",
                "hasReavaliation" => true,
                "class" => "col-12 col-md-12",
                "img" => URL . "/resources/img/espera/doctor_white.png",
                "img-id" => "nurse",
                "titulo-captalize" => "Clínica Médica",
                "primeiro-atendimento-pessoas" => 0,
                "primeiro-atendimento-tempo" => 0,
                "reavaliacao-pessoas" => 0,
                "reavaliacao-tempo" => 0,
                "time" => ""
            ],
            "cardio" => [
                "tipo" => "cardio",
                "hasReavaliation" => true,
                "class" => "col-12 col-md-12",
                "img" => URL . "/resources/img/espera/cardio_white.png",
                "img-id" => "nurse",
                "titulo-captalize" => "Cardiologia",
                "primeiro-atendimento-pessoas" => 0,
                "primeiro-atendimento-tempo" => 0,
                "reavaliacao-pessoas" => 0,
                "reavaliacao-tempo" => 0,
                "time" => ""
            ],
            "ortopedia" => [
                "tipo" => "ortopedia",
                "hasReavaliation" => true,
                "class" => "col-12 col-md-12",
                "img" => URL . "/resources/img/espera/nurse_white.png",
                "img-id" => "nurse",
                "titulo-captalize" => "Ortopedia",
                "primeiro-atendimento-pessoas" => 0,
                "primeiro-atendimento-tempo" => 0,
                "reavaliacao-pessoas" => 0,
                "reavaliacao-tempo" => 0,
                "time" => ""
            ]
        ];

        $cards = "";
        foreach ($locals as $tipo => $card) {
            if (!$card['hasReavaliation']) {
                $triagem = self::getTriagemHospitalValues();
                $card['numero-pacientes'] = $triagem[$tipo]['pacientes'];
                $card['tempo'] = $triagem[$tipo]['tempo'];

                if ($card['tempo'] <= 60) $card['time'] = 'bg-success';
                elseif ($card['tempo'] > 60 && $card['tempo'] <= 120) $card['time'] = 'bg-warning';
                else $card['time'] = 'bg-danger';
            } else {
                $infos = self::getClinicaAndCardioAndOrto();
                $card['primeiro-atendimento-pessoas'] = $infos[$tipo]['primeiro-atendimento-pessoas'];
                $card['primeiro-atendimento-tempo'] = $infos[$tipo]['primeiro-atendimento-tempo'];
                $card['reavaliacao-pessoas'] = $infos[$tipo]['reavaliacao-pessoas'];
                $card['reavaliacao-tempo'] = $infos[$tipo]['reavaliacao-tempo'];

                if ($card['primeiro-atendimento-tempo'] <= 60) $card['time'] = 'bg-success';
                elseif ($card['primeiro-atendimento-tempo'] > 60 && $card['primeiro-atendimento-tempo'] <= 120) $card['time'] = 'bg-warning';
                else $card['time'] = 'bg-danger';
            }

            $cards .= View::render('espera/' . ($card['hasReavaliation'] ? 'card_with_reavaliation' : 'card_without_reavaliation'), $card);
        }
        return View::render('espera/home_hospital', [
            "cards" => $cards
        ]);
    }

    public static function getEsperaPed(Request $request)
    {
        $locals = [
            "triagem" => [
                "tipo" => "triagem",
                "hasReavaliation" => false,
                "class" => "col-12",
                "style" => "padding-top: 5px !important;",
                "img-id" => "img-triagem",
                "img" => URL . "/resources/img/espera/triagem_white.png",
                "titulo-captalize" => "Triagem Pediátrica",
                "numero-pacientes" => 0,
                "tempo" => 0
            ],
            "recepcao" => [
                "tipo" => "recepcao",
                "hasReavaliation" => false,
                "class" => "col-12",
                "style" => "",
                "img-id" => "recepcao",
                "img" => URL . "/resources/img/espera/reception_white.png",
                "titulo-captalize" => "Recepção Pediátrica",
                "numero-pacientes" => 0,
                "tempo" => 0
            ],
            "consultorio_ped" => [
                "tipo" => "consultorio_ped",
                "hasReavaliation" => true,
                "class" => "col-12 col-md-12",
                "img" => URL . "/resources/img/espera/cardio_white.png",
                "img-id" => "nurse",
                "titulo-captalize" => "Consultorio Pediátria",
                "primeiro-atendimento-pessoas" => 0,
                "primeiro-atendimento-tempo" => 0,
                "reavaliacao-pessoas" => 0,
                "reavaliacao-tempo" => 0
            ]
        ];

        $cards = "";
        foreach ($locals as $tipo => $card) {
            if (!$card['hasReavaliation']) {
                $triagem = self::getTriagemPedValues($tipo);
                $card['numero-pacientes'] = $triagem[$tipo]['pessoas'];
                $card['tempo'] = $triagem[$tipo]['tempo'];

                if ($card['tempo'] <= 60) $card['time'] = 'bg-success';
                elseif ($card['tempo'] > 60 && $card['tempo'] <= 120) $card['time'] = 'bg-warning';
                else $card['time'] = 'bg-danger';
            } else {
                $infos = self::getConsultorioValues();
                $card['primeiro-atendimento-pessoas'] = $infos[$tipo]['primeiro-atendimento-pessoas'];
                $card['primeiro-atendimento-tempo'] = $infos[$tipo]['primeiro-atendimento-tempo'];
                $card['reavaliacao-pessoas'] = $infos[$tipo]['reavaliacao-pessoas'];
                $card['reavaliacao-tempo'] = $infos[$tipo]['reavaliacao-tempo'];

                if ($card['primeiro-atendimento-tempo'] <= 60) $card['time'] = 'bg-success';
                elseif ($card['primeiro-atendimento-tempo'] > 60 && $card['primeiro-atendimento-tempo'] <= 120) $card['time'] = 'bg-warning';
                else $card['time'] = 'bg-danger';
            }

            $cards .= View::render('espera/' . ($card['hasReavaliation'] ? 'card_with_reavaliation' : 'card_without_reavaliation'), $card);
        }

        return View::render('espera/home_hospital', [
            "cards" => $cards
        ]);
    }

    public static function getTriagemHospitalValues()
    {
        $result = [];
        $result['classificacao']['pacientes'] = 0;
        $result['classificacao']['tempo'] = 0;
        $result['recepcao']['pacientes'] = 0;
        $result['recepcao']['tempo'] = 0;

        $triagem = EsperaModel::getTriagem();
        foreach ($triagem as $linha) {
            /* CLASSIFICACAO - tempo / pacientes */
            if ($linha['FILA_COD_CLASSIFICACAO'] == 900250) {
                /* obtendo pacientes */
                if ($linha['STATUS_CLASSIFICACAO'] == 'A')
                    $result['classificacao']['pacientes']++;

                /* obtendo valor maior */
                if ($linha['ESPERA_CLASSIFICACAO'] > $result['classificacao']['tempo'] && $linha['STATUS_CLASSIFICACAO'] == 'A')
                    $result['classificacao']['tempo'] = $linha['ESPERA_CLASSIFICACAO'];
            }

            /* CLASSIFICACAO - tempo / pacientes */
            if ($linha['FILA_COD_RECEPCAO'] == 900197) {

                /* obtendo pacientes */
                if ($linha['STATUS_RECEPCAO'] == 'A') {
                    $result['recepcao']['pacientes']++;
                }
                /* obtendo valor maior */
                if ($linha['ESPERA_RECEPCAO'] > $result['recepcao']['tempo'] && $linha['STATUS_RECEPCAO'] == 'A')
                    $result['recepcao']['tempo'] = $linha['ESPERA_RECEPCAO'];
            }
        }

        return $result;
    }

    public static function getClinicaAndCardioAndOrto()
    {
        /* variaveis de especialidades */
        $result['clinica']['primeiro-atendimento-pessoas'] = 0;
        $result['clinica']['primeiro-atendimento-tempo'] = 0;
        $result['clinica']['reavaliacao-pessoas'] = 0;
        $result['clinica']['reavaliacao-tempo'] = 0;
        $result['cardio']['primeiro-atendimento-pessoas'] = 0;
        $result['cardio']['primeiro-atendimento-tempo'] = 0;
        $result['cardio']['reavaliacao-pessoas'] = 0;
        $result['cardio']['reavaliacao-tempo'] = 0;
        $result['ortopedia']['primeiro-atendimento-pessoas'] = 0;
        $result['ortopedia']['primeiro-atendimento-tempo'] = 0;
        $result['ortopedia']['reavaliacao-pessoas'] = 0;
        $result['ortopedia']['reavaliacao-tempo'] = 0;

        $primeiraAvaliacao = EsperaModel::getPrimeiraAvaliacao();
        $reavaliacao = EsperaModel::getReavaliacao();

        foreach ($primeiraAvaliacao as $linha) {

            /* CLINICA MEDICA */
            if ($linha['FILA_COD'] == '900290') {
                $result['clinica']['primeiro-atendimento-pessoas']++;
                if ($linha['tempo_espera_total'] >  $result['clinica']['primeiro-atendimento-tempo'] && $linha['tempo_espera_total'] < 360) {
                    $result['clinica']['primeiro-atendimento-tempo'] = $linha['tempo_espera_total'];
                }
            }

            /* CLINICA CARDIOLOGIA */
            if ($linha['FILA_COD'] == '900288') {
                /* soma de pacientes */
                $result['cardio']['primeiro-atendimento-pessoas']++;
                /* comparacao de tempo */
                if ($linha['tempo_espera_total'] >  $result['cardio']['primeiro-atendimento-tempo'] && $linha['tempo_espera_total'] < 360) {
                    $result['cardio']['primeiro-atendimento-tempo'] = $linha['tempo_espera_total'];
                }
            }

            /* CLINICA ORTOPEDIA */
            if ($linha['FILA_COD'] == '900289') {
                /* soma de pacientes */
                $result['ortopedia']['primeiro-atendimento-pessoas']++;
                /* comparacao de tempo */
                if ($linha['tempo_espera_total'] >  $result['ortopedia']['primeiro-atendimento-tempo'] && $linha['tempo_espera_total'] < 360) {
                    $result['ortopedia']['primeiro-atendimento-tempo'] = $linha['tempo_espera_total'];
                }
            }
        }

        foreach ($reavaliacao as $linha) {

            /* CLINICA MEDICA */
            if ($linha['FILA_COD'] == '900290') {
                $result['clinica']['reavaliacao-pessoas']++;
                if ($linha['tempo_espera_total'] >  $result['clinica']['reavaliacao-tempo'] && $linha['tempo_espera_total'] < 600) {
                    $result['clinica']['reavaliacao-tempo'] = $linha['tempo_espera_total'];
                }
            }
            /* CLINICA CARDIOLOGIA */
            if ($linha['FILA_COD'] == '900288') {
                /* soma de pacientes */
                $result['cardio']['reavaliacao-pessoas']++;
                /* comparacao de tempo */
                if ($linha['tempo_espera_total'] > $result['cardio']['reavaliacao-tempo'] && $linha['tempo_espera_total'] < 600) {
                    $result['cardio']['reavaliacao-tempo'] = $linha['tempo_espera_total'];
                }
            }
            /* CLINICA ORTOPEDIA */
            if ($linha['FILA_COD'] == '900289') {
                /* soma de pacientes */
                $result['ortopedia']['reavaliacao-pessoas']++;
                /* comparacao de tempo */
                if ($linha['tempo_espera_total'] >  $result['ortopedia']['reavaliacao-tempo'] && $linha['tempo_espera_total'] < 360) {
                    $result['ortopedia']['reavaliacao-tempo'] = $linha['tempo_espera_total'];
                }
            }
        }

        return $result;
    }

    public static function getTriagemPedValues($tipo)
    {
        $result = [];
        $result[$tipo]['pessoas'] = 0;
        $result[$tipo]['tempo'] = 0;

        $triagemPed = $tipo == "triagem" ? EsperaModel::getTriagemPedValues() : EsperaModel::getRecepcaoPedValues();
        foreach ($triagemPed as $linha) {
            $result[$tipo]['pessoas']++;
            if ($linha['tempo_espera_total'] >  $result[$tipo]['tempo'] && $linha['tempo_espera_total'] < 360) {
                $result[$tipo]['tempo'] = $linha['tempo_espera_total'];
            }
        }

        return $result;
    }

    public static function getConsultorioValues()
    {
        $result = [];
        $result['consultorio_ped']['primeiro-atendimento-pessoas'] = 0;
        $result['consultorio_ped']['primeiro-atendimento-tempo'] = 0;
        $result['consultorio_ped']['reavaliacao-pessoas'] = 0;
        $result['consultorio_ped']['reavaliacao-tempo'] = 0;

        $consult = EsperaModel::getConsultorioValues();

        foreach ($consult as $linha) {
            if ($linha['atendimento'] == "A") {
                $result['consultorio_ped']['primeiro-atendimento-pessoas']++;
                if ($linha['tempo_espera_total'] >  $result['consultorio_ped']['primeiro-atendimento-tempo'] && $linha['tempo_espera_total'] < 360) {
                    $result['consultorio_ped']['primeiro-atendimento-tempo'] = $linha['tempo_espera_total'];
                }
            } else {
                $result['consultorio_ped']['reavaliacao-pessoas']++;
                if ($linha['tempo_espera_total'] >  $result['consultorio_ped']['reavaliacao-tempo'] && $linha['tempo_espera_total'] < 360) {
                    $result['consultorio_ped']['reavaliacao-tempo'] = $linha['tempo_espera_total'];
                }
            }
        }

        return $result;
    }
}
