<?php

namespace App\Controller\Chaves;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Http\Request;
use \App\Model\Chaves\ChavesModel;
use \App\Model\Utils\Spy;
use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;

class ChavesController extends LayoutPage
{
    public static function getHome(Request $request, $order = null)
    {
        $armarios = ChavesModel::getLockers();

        $optionsArmarios = View::render('utils/option', [
            "id" => "",
            "nome" => "Selecione um armário",
            "selected" => "selected",
            "disabled" => "disabled"
        ]);

        foreach ($armarios as $armario) {
            $optionsArmarios .= View::render('utils/option', [
                "id" => $armario['id'],
                "nome" => $armario['numero'],
                "selected" => "",
                "disabled" => ""
            ]);
        }

        $funcionarios = ChavesModel::getFuncionarios();

        $optionsFuncionarios = View::render('utils/option', [
            "id" => "",
            "nome" => "Selecione um armário",
            "selected" => "selected",
            "disabled" => "disabled"
        ]);

        foreach ($funcionarios as $funcionario) {
            $optionsFuncionarios .= View::render('utils/option', [
                "id" => $funcionario['id'],
                "nome" => $funcionario['matricula']." - ".$funcionario['nome'],
                "selected" => "",
                "disabled" => ""
            ]);
        }

        $date = new DateTime();
        $date->setTimezone(new DateTimeZone('America/Fortaleza'));
        $final = $date->format("Y-m-d");
        $date->modify("-2 week");
        $initial = $date->format("Y-m-d");

        $filtros = View::render('chaves/filtros_card', [
            "armarios-select" => View::render('utils/select', [
                "class" => "",
                "id" => "armarios",
                "disabled" => "",
                "options" => $optionsArmarios
            ]),
            "matriculas-select" => View::render('utils/select', [
                "class" => "",
                "id" => "matriculas",
                "disabled" => "",
                "options" => $optionsFuncionarios
            ]),
            "data-interval" => View::render('chaves/datas_input', [
                "initial-interval" => $initial,
                "final-interval" => $final
            ])
        ]);

        $content = View::render('chaves/home', [
            "armarios" => self::getLockersHTML(),
            "filtros" => $filtros
        ]);

        // Atualiza o acesso do usuario nesse sistema
        Spy::updateAcess($request->user, 17, 'sistema_de_chaves');

        return parent::getPage("Gerenciamento de chaves", 'chaves', $content, $request);
    }

    public static function getLockersHTML($order = null)
    {
        $armarios = $order == null ? ChavesModel::getLockers() : ChavesModel::getLockers('desc');

        $armariosHtml = "";
        foreach ($armarios as $locker) {
            $armariosHtml .= View::render('chaves/locker', [
                "id" => $locker['id'],
                "bg-status" => $locker['status'] == "L" ? "success" : "danger",
                "numero" => $locker['numero']
            ]);
        }

        return $armariosHtml;
    }

    public static function searchRegistration(Request $request)
    {
        $post = $request->getPostVars();

        if(!is_numeric($post['matricula']))
            return ["title" => "Coloque somente números!", "succeded" => false];

        $funcionario = ChavesModel::searchRegistration($post['matricula']);
        if(empty($funcionario))
            return ["title" => "Funcionário não encontrado! Deseja colocá-lo no sistema?", "code" => "MF", "succeded" => false];

        $strHasLocker = '';
        foreach($funcionario as $locker){
            $lockerNum = $locker['armario'];
            if(count($funcionario) > 1){
                $strHasLocker .= "$lockerNum ";
            } else $strHasLocker .= "$lockerNum";
        }

        $hasOtherLocker = empty($strHasLocker) ? "" : "<h4 class='text-danger' style='padding: 0 1em 1em;'> Esse funcionário já tem o(s) seguite(s) armário(s) registrado(s) em seu nome: $strHasLocker</h4>";
        
        return [
            "title" => "Você tem certeza que quer alugar para esta pessoa?",
            "message" => View::render('chaves/funcionario_infos', [
                "has-other-locker" => $hasOtherLocker,
                "nome" => $funcionario[0]['nome'],
                "matricula" => (int) $funcionario[0]['matricula'],
                "setor" => $funcionario[0]['setor']
            ]),
            "succeded" => true
        ];
    }

    public static function addFuncionario(Request $request)
    {
        $post = $request->getPostVars();
        if(ChavesModel::addFuncionario($post['matricula'], $post['nome'], $post['setor']) === false)
            return ["title" => "Algo falhou - AF0", "succeded" => false];

        return ['title' => "Adicionado!", "succeded" => true];
    }
    
    public static function getAllSetores(Request $request)
    {
        $sectors = ChavesModel::getSetores();
        $setoresOptions = '';
        foreach($sectors as $sector){
            $setoresOptions .= View::render('utils/option', [
                "id" => "",
                "nome" => $sector['setor'],
                "selected" => "",
                "disabled" => ""
            ]);
        }
        

        $setorSelect = View::render('utils/select', [
            "class"=>"custom-select setor-select",
            "name"=>"setor-select",
            "id"=>"",
            "disabled"=>"",
            "options"=>$setoresOptions,
        ]);
        return $setorSelect;
    }
    public static function getModalLocker(Request $request)
    {
        $locker = ChavesModel::getLockerInfos($request->getPostVars()['idLocker']);

        $lockerInfo = [];
        if ($locker['status'] == "L") {
            $lockerInfo = [
                "numero-armario" => $locker['numero'],
                "status-armario" => $locker['status'],
                "body" => View::render('chaves/freeLocker'),
                "button-id" => "btn-alugar",
                "button-color" => "primary",
                "button-name" => "Alugar"
            ];
        } else {
            $data_alteracao = ChavesModel::getLastRent($locker['id'], $locker['matricula']);

            if($data_alteracao === false)
                return "erro";

            $lockerInfo = [
                "numero-armario" => $locker['numero'],
                "status-armario" => $locker['status'],
                "body" => View::render('chaves/occupiedLocker', [
                    "nome-funcionario" => $locker['nome'],
                    "matricula" => $locker['matricula'],
                    "setor" => $locker['setor'],
                    "data" => (new DateTime($data_alteracao['data']))->format("d/m/Y H:i:s")
                ]),
                "button-id" => "btn-devolver",
                "button-color" => "success",
                "button-name" => "Devolver"
            ];
        }

        return View::render('chaves/lockerInfo', $lockerInfo);
    }

    public static function alugarLocker(Request $request)
    {
        $post = $request->getPostVars();

        if (!ChavesModel::alugarLocker($post['matricula'], $post['idLocker']))
            return ["title" => "Algo falhou - A0", "succeded" => false];
        
        if (!ChavesModel::putRentHistoric($post['matricula'], $post['idLocker'], 'O'))
            return ["title" => "Algo falhou - A1", "succeded" => false];

        // if(!ChavesModel::alugarLockerCorrectly($post['matricula'], $post['idLocker']))
        //     return ["title" => "Algo falhou - E1", "succeded" => false];

        return ["title" => "Armário alugado com sucesso", "succeded" => true];
    }

    public static function devolverLocker(Request $request)
    {
        $post = $request->getPostVars();

        $locker = ChavesModel::getLockerInfos($post['idLocker']);

        if($locker === false)
            return ["title" => "Algo falhou - D0", "succeded" => false];

        if (!ChavesModel::devolverLocker($post['idLocker']))
            return ["title" => "Algo falhou - D1", "succeded" => false];

        if (!ChavesModel::putRentHistoric($locker['matricula'], $post['idLocker'], 'L'))
            return ["title" => "Algo falhou - D2", "succeded" => false];

        return ["title" => "Chave do armário devolvido com sucesso", "succeded" => true];
    }

    public static function getHistorico(Request $request)
    {

        $colums = [
            "",
            "data_action",
            "id_arm",
            "type_action",
            "matricula_func",
            "func.nome"
        ];

        $post = $request->getPostVars();

        $firstLoad = $post['firstload'] == "true" ? true : false;

        if($firstLoad){
            $finalInterval = (new DateTime(ChavesModel::getLastDate()['data']))->format("Y-m-d");
            $initialInterval = (new DateTime(ChavesModel::getLastDate()['data']))->sub(new DateInterval('P14D'))->format("Y-m-d");
        } else {
            $initialInterval = $post['initialInterval'];
            $finalInterval = $post['finalInterval'];
        }

        if (isset($post['start']) && $post['length'] != -1) {
            $limit = intval($post['start']) . ", " . intval($post['length']);
        }

        $order = "";
        if (!empty($post['order'])) {
            $endItem = end($post['order']);
            $firstItem = reset($post['order']);

            foreach ($post['order'] as $item) {
                if ($item['column'] == 0)
                    continue;

                if ($item == $firstItem)
                    $order = $colums[$item['column']] . " " . $item['dir'] . (count($post['order']) > 1 ? ", " : "");

                elseif ($item == $endItem)
                    $order .= $colums[$item['column']] . " " . $item['dir'];

                else
                    $order .= $colums[$item['column']] . " " . $item['dir'] . ", ";
            }
        }

        $inputTextSearch = !empty($post['search']['value']) ? strtoupper($post['search']['value']) : null;

        $lockers = null;
        if (!empty($post['lockers']))
            $lockers = implode(",", $post['lockers']);

        $funcionarios = null;
        if (!empty($post['funcionarios']))
            $funcionarios = implode(",", $post['funcionarios']);

        $movimentacao = null;
        if (!empty($post['movimentacao'])) {
            $post['movimentacao'] = array_map(function ($value) {
                return $value == 'E' ? "'" . 'O' . "'" : "'" . 'L' . "'";
            }, $post['movimentacao']);
            $movimentacao = implode(",", $post['movimentacao']);
        }


        $filters = [
            "inputText" => !is_null($inputTextSearch) ?
                "(func.nome like '%" . $inputTextSearch . "%')" : null,

            "lockers" => !is_null($lockers) ?
                "id_arm in (" . $lockers . ")" : null,

            "funcionarios" => !is_null($funcionarios) ?
                "matricula_func in (" . $funcionarios . ")" : null,

            "movimentacao" => !is_null($movimentacao) ?
                "type_action in (" . $movimentacao . ")" : null,

            "datas" => !empty($post['initialInterval']) && !empty($post['finalInterval']) ?
                "date(data_action) between '" . $initialInterval . "' AND '" . $finalInterval . "'" : null,
        ];

        $filterString = "";
        if (count(array_count_values($filters)) > 1) {
            $aux = 0;
            foreach (array_count_values($filters) as $key => $value) {
                if (!$aux++)
                    $filterString = $key;
                else
                    $filterString .= " AND " . $key;
            }
        } elseif (count(array_count_values($filters)) == 1) {
            foreach (array_filter($filters) as $value) {
                $filterString = $value;
            }
        }

        $actions = self::historicoToHTMLRow(ChavesModel::getHistorico($limit, $order, $filterString));
        return array(
            "draw" => isset($post['draw']) ? intval($post['draw']) : 0,
            "recordsTotal" => count($actions),
            "recordsFiltered" => count(ChavesModel::getHistorico(null, $order, $filterString)),
            "data" => $actions,
            "datas" => [
                "inicio" => $initialInterval,
                "final" => $finalInterval
            ]
        );
    }

    public static function historicoToHTMLRow($actions)
    {
        $historic = [];
        $contador = 0;
        foreach ($actions as $action) {
            $historic[$contador]['ID'] = $contador + 1;
            $historic[$contador]['DTHR_ACTION'] = (new DateTime($action['data']))->format("d/m/Y H:i:s");
            $historic[$contador]['NUMERO_ARMARIO'] = $action['numero'];
            $historic[$contador]['MOVIMENTACAO'] = $action['status'];
            $historic[$contador]['MATRICULA'] = $action['matricula'];
            $historic[$contador++]['FUNCIONARIO'] = $action['nome'];
        }

        return $historic;
    }

    public static function getFuncionariosData($request){
        $funcionariosData =  ChavesModel::getFuncionariosData();
        $options = View::render('utils/option', [
            "id"=> "",
            "nome"=> "Selecione um funcionário"
        ]);
        foreach ($funcionariosData as $func) {
            $options .= View::render('utils/option', [
                "id"=>$func["matricula"],
                "nome"=>$func['matricula']." - ". $func["nome"],
            ]);
        }

        $content = View::render('utils/select', [
            "id" => "matricula",
            "class" => "custom-select form-control",
            "name" => "matricula-custom",
            "options"=> $options
        ]);

        return $content;
    }
}
