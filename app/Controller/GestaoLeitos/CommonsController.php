<?php

namespace App\Controller\GestaoLeitos;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Http\Request;
use App\Model\GestaoLeitos\Leito;
use App\Model\GestaoLeitos\Setor;
use App\Model\GestaoLeitos\Solicitacao;
use App\Model\Utils\Setor as SmartSetor;
use App\Model\Entity\User;
use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;

class CommonsController extends LayoutPage
{

    private const UsersAndDateColumnRelation = [
        "SOLICITACAO_SOLICITANTE" => [
            "datetime" => "SOLICITACAO_DTHR_REGISTRO"
        ],
        "USUARIO_EDICAO" => [
            "datetime" => "SOLICITACAO_DTHR_EDICAO"
        ],
        "USUARIO_ATENDIMENTO" => [
            "datetime" => "SOLICITACAO_DTHR_ATENDIMENTO"
        ],
        "USUARIO_CANC_ATENDIMENTO" => [
            "datetime" => "DTHR_CANC_ATENDIMENTO"
        ],
        "USUARIO_CANCELAMENTO" => [
            "datetime" => "SOLICITACAO_DTHR_CANCELAMENTO"
        ],
        "USUARIO_ENCERRAMENTO" => [
            "datetime" => "SOLICITACAO_DTHR_ENCERRAMENTO"
        ],
        "USUARIO_LIBERADO" => [
            "datetime" => "SOLICITACAO_DTHR_FINAL"
        ],
        "USUARIO_ALTERACAO_RESERVA" => [
            "datetime" => "DTHR_ALTERACAO_RESERVA"
        ],
        "USUARIO_ENCERRAMENTO_RESERVA" => [
            "datetime" => "DTHR_ENCERRAMENTO_RESERVA"
        ],
        "USUARIO_TRANSFERENCIA" => [
            "datetime" => "DTHR_TRANSFERENCIA"
        ]
    ];

    private const PhrasesToHistoric = [
        "A" => [
            "label" => " criou a solicitação",
            "icon" => "fas fa-plus",
            "color" => "warning"
        ],
        "P" => [
            "label" => " preparou o leito",
            "icon" => "far fa-person-booth",
            "color" => "success"
        ],
        "L" => [
            "label" => " reservou o leito",
            "icon" => "far fa-person-booth",
            "color" => "info"
        ],
        "EDT" => [
            "label" => " editou a solicitação",
            "icon" => "far fa-edit",
            "color" => "orange"
        ],
        "C" => [
            "label" => " cancelou a solicitação",
            "icon" => "fas fa-times",
            "color" => "danger"
        ],
        "CLB" => [
            "label" => " alterou o leito liberado",
            "icon" => "fas fa-exchange",
            "color" => "purple"
        ],
        "E" => [
            "label" => " confirmou a reserva",
            "icon" => "far fa-ban",
            "color" => "dark"
        ],
        "CP" => [
            "label" => " cancelou a preparação",
            "icon" => "fas fa-undo",
            "color" => "dark"
        ]
        // "RC" => [
        //     "label" => " cancelou a reserva",
        //     "icon" => "fas fa-do-not-enter",
        //     "color" => "dark"
        // ],
        // "RC2" => [
        //     "label" => " cancelou a reserva pelo SMART",
        //     "icon" => "fas fa-do-not-enter",
        //     "color" => "dark"
        // ],
        // "RU" => [
        //     "label" => " admitiu o paciente",
        //     "icon" => "fas fa-do-not-enter",
        //     "color" => "admit"
        // ],
        // "RU2" => [
        //     "label" => " admitiu o paciente pelo SMART",
        //     "icon" => "fas fa-procedures",
        //     "color" => "admit"
        // ]
    ];



    /**
     * Método responsável por verificar se o leito está bloqueado
     */
    public static function isBlocked(string $bedCode)
    {
        $bedOperation = Leito::getBlockBedOperation(trim($bedCode));

        if (empty($bedOperation))
            return false;

        if (strtoupper($bedOperation['operacao']) === "DESBLOQUEIO")
            return false;

        return true;
    }

    public static function pullAdequateBeds(
        Request $request = null,
        array $perfil = null
    ) {
        if ($request != null && !empty($request->getPostVars())) {
            $perfil = $request->getPostVars();
        }
        $ignoreIncompatible = $perfil['ignoreIncompatible'] ?? 0;
        $filters = $perfil['filters'];
        $filters['gender'] = $filters['gender'] === 'Feminino'
            ? 'F' : ($filters['gender'] === 'Masculino' ? 'M' : 'O');


        $hospitalBeds = [];
        if ($ignoreIncompatible)
            $hospitalBeds = ($filters['sector'] == null || $filters['sector'] == "")
                ? Setor::getAllSectorsWithoutDifference()
                : Leito::getBedsByStatusAndUnit($filters['sector'], 'L');
        else {
            switch (true) {
                case $filters['covid']:
                    if (is_null($filters['sector']))
                        $hospitalBeds = Setor::getSectorsCovid($filters['accommodation']);
                    else
                        $hospitalBeds = Leito::getCovidBedsByUnitAndAcc($filters['sector'], $filters['accommodation']);

                    break;
                case $filters['pediatric']:
                    if (is_null($filters['sector']))
                        $hospitalBeds = Setor::getSectorsPediatricos($filters['accommodation']);
                    else
                        $hospitalBeds = Leito::getPediatricBedsByUnitAndAcc($filters['sector'], $filters['accommodation']);

                    break;
                case strtoupper($filters['accommodation']) === 'ENF':
                    if (is_null($filters['sector']))
                        $hospitalBeds = Setor::getSectorsEnfermaria($filters['accommodation'], $filters['gender']);
                    else
                        $hospitalBeds = Leito::getBedsByUnitAccAndGender($filters['sector'], $filters['accommodation'], $filters['gender']);

                    break;
                default:
                    if (is_null($filters['sector'])) {
                        $hospitalBeds = Setor::getSectorsNormals($filters['accommodation']);
                    } else {
                        $hospitalBeds = Leito::getBedsByUnitAndAcc($filters['sector'], $filters['accommodation']);
                    }
                    break;
            }
        }

        foreach ($hospitalBeds as $row) {
            $row['setor_codigo'] = trim($row['setor_codigo']);
            $row['leito_nome'] = trim($row['leito_nome']);

            $result[] = $row;
        }

        return $result;
    }

    public static function getAdequateSectors(Request $request)
    {
        $beds = self::pullAdequateBeds($request);
        $resultSectors = [];
        $aux = 0;
        return $beds;
        foreach ($beds as $bed) {
            if (in_array($bed["setor_codigo"], array_column($resultSectors, "setor_codigo")) == false) {
                $resultSectors[$aux]["setor_codigo"] = $bed["setor_codigo"];
                $resultSectors[$aux++]["setor_nome"] = $bed["setor_nome"];
            }
        }
    }

    public static function getAdequateBedsBySector(Request $request)
    {
        $leitos = self::pullAdequateBeds($request, null);
        $result = [];
        foreach ($leitos as $leito) {
            $isBlocked = self::isBlocked($leito['leito_codigo']);

            if ($isBlocked)
                continue;

            $result[] = $leito;
        }
        return $result;
    }

    public static function getHistoricSolic(string $id)
    {
        $historico_array = Solicitacao::getHistoricoAtendimentoFromNewSolicitation($id);

        $countText = 0;
        $textFinal = "";
        foreach ($historico_array as $evento) {
            if ($countText >= 3)
                break;

            $textFinal .= View::render('gestaoleitos/solicitacao/historico/preview-detail', [
                'color' => "color: rgba(0,0,0,0." . 9 - (3 * $countText++) . ")",
                'date' => (new DateTime($evento['dateAction']))->format("d/m/Y"),
                'time' => (new DateTime($evento['dateAction']))->format("H:i:s"),
                'user' => !is_null($evento['usuario']) ? self::getUsername($evento['usuario']) : $evento['SMARTUsername'],
                'phrase' => self::PhrasesToHistoric[$evento['statusChange']]['label']
            ]);
        }

        return View::render('gestaoleitos/solicitacao/card_historico', [
            'historico' => $textFinal,
            'button' => View::render('gestaoleitos/solicitacao/button_historico')
        ]);
    }

    public static function getUsername(int|string $user)
    {
        if (is_numeric($user)) {
            $user = User::getUserById($user);
            return $user->nome . " " . $user->sobrenome;
        }
        return $user;
    }

    public static function getActionTimeLine($action)
    {
        return self::PhrasesToHistoric[$action];
    }

    public static function getCodeAndUnits(array $units)
    {
        $responseUnits = [];

        foreach ($units as $unit) {
            $responseUnits[trim($unit['codigo'])] = trim($unit['nome']);
        }
        return $responseUnits;
    }

    public static function checkPermissaoByAjax($request)
    {
        return parent::checkPermissao($request->user, $request->getPostVars()['permission'], "admin");
    }

    public static function compareDate($a, $b)
    {
        if ($a['data'] > $b['data']) return -1;
        elseif ($a['data'] < $b['data']) return 1;
        return 0;
    }
}
