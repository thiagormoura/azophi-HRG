<?php

namespace App\Controller\Check_OS;

use \App\Utils\View;
use \App\Http\Request;
use \App\Controller\Layout\Layout as LayoutPage;
use \App\Model\Check_OS\Check_OSModel;
use \App\Model\Utils\Spy;
use DateTime;
use DateTimeZone;

class Check_OSController extends LayoutPage
{
    public static function getHome(Request $request)
    {
        $content = View::render('check_os/home');

        // Atualiza o acesso do usuario nesse sistema
        Spy::updateAcess($request->user, 19, 'checkos');
      
        return parent::getPage("Check OS", "check_os", $content, $request);
    }

    public static function getLegend(Request $request)
    {
        return View::render('check_os/legenda');
    }

    public static function ajaxReloadTable(Request $request)
    {
        $colums = [
            "OS_SERIE_NUMERO",
            "LANCAMENTO",
            "SETOR",
            "QTD",
            "LIBERADO",
            "ABERTO",
            "TIPO_INTEGRACAO"
        ];

        $post = $request->getPostVars();

        $limit = [
            "start" => $post['start'], 
            "length" => $post['length']
        ];

        $order = "";
        if (!empty($post['order'])) {
            $endItem = end($post['order']);
            $firstItem = reset($post['order']);

            foreach ($post['order'] as $item) {

                if ($item == $firstItem){
                    if($item['column'] == 0)
                        $order = "Pagination.OS_SERIE " . $item['dir'] .", Pagination.OS_NUMERO " . $item['dir'] . (count($post['order']) > 1 ? ", " : "");
                    else
                        $order = "Pagination.".$colums[$item['column']] . " " . $item['dir'] . (count($post['order']) > 1 ? ", " : "");
                }

                elseif ($item == $endItem)
                    $order .= "Pagination.".$colums[$item['column']] . " " . $item['dir'];

                else
                    $order .= "Pagination.".$colums[$item['column']] . " " . $item['dir'] . ", ";
            }
        }

        $sectors = null;
        if(!empty($post['sectors'])){
            foreach ($post['sectors'] as $key => $setor) {
                $post['sectors'][$key] = "'".$setor."'";
            }
            $sectors = implode(",", $post['sectors']);
        }

        $dataInicial = null;
        $dataFinal = null;
        if(!empty($post['dataInicio'])){
            $dataInicial = (new DateTime($post['dataInicio']['date']))->format("Y-m-d");
            $dataInicial .= " ".(new DateTime($post['dataInicio']['time']))->format("H:i");
            $dataFinal = (new DateTime($post['dataFim']['date']))->format("Y-m-d");
            $dataFinal .= " ".(new DateTime($post['dataFim']['time']))->format("H:i");
        }

        $specifyOs = null;
        if(!empty($post['pacientes'])){

            $array_serie = [];
            $array_numero = [];

            foreach ($post['pacientes'] as $serie_number) {
                $os_serie = explode("-", $serie_number)[0];
                $os_number = explode("-", $serie_number)[1];

                $array_serie[] = "'".$os_serie."'";
                $array_numero[] = "'".$os_number."'";
            }

            $specifyOs['serie'] = implode(",", $array_serie);
            $specifyOs['numero'] = implode(",", $array_numero);
        }

        $filters = [
            "sectors" => !empty($sectors) ?
                "str.str_cod in (".$sectors.")" : null,
            "inputText" => !empty($specifyOs) ? 
                "OSM_SERIE in (".$specifyOs['serie'].") AND OSM_NUM in (".$specifyOs['numero'].")": null,
            "data" => !empty($dataInicial) ?
                "pex.PEX_DTHR BETWEEN '".$dataInicial."' AND '".$dataFinal."'" : null
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

        $typeStatusImplode = [];
        if($post['isOpen'] == "true") 
            $typeStatusImplode[] = "Pagination.status_aberto = 'A'";

        if($post['isExec'] == "true") 
            $typeStatusImplode[] = "Pagination.status_execucao = 'X'";

        if($post['isLib'] == "true") 
            $typeStatusImplode[] = "Pagination.status_liberacao = 'L'";


        $filters_array = [
            "first" => $filterString ?? "",
            "second" => count($typeStatusImplode) > 0 ? "(".implode(" OR ", $typeStatusImplode).")" : "",
            "third" => $post['isCiente'] == "true"
        ];

        $os = self::getOs($limit, $order, $filters_array);

        return array(
            "draw" => isset($post['draw']) ? intval($post['draw']) : 0,
            "recordsTotal" => $os['countAll'],
            "recordsFiltered" => $os['countAll'],
            "data" => $os['OSListLimit'],
            "sectors" => self::getSectors($dataInicial, $dataFinal),
            "pacientes" => $os['pacientes']
            ,"teste" => json_encode($post['order'], JSON_UNESCAPED_UNICODE)
        );
    }

    public static function getSectors($dataInicio=null, $dataFim=null)
    {
        if(empty($dataInicio)){
            $date = new DateTime();
            $dataInicio = $date->format("Y-m-d");
            $date->modify("-7 days");
            $dataFim = $date->format("Y-m-d");
        }
        
        $sectors = Check_OSModel::getSectors($dataInicio, $dataFim);

        $options = [];
        foreach ($sectors as $sector) {
            $options[] = [
                "text" => $sector['SETOR'],
                "value" => $sector['SETOR_COD']
            ];
        }

        return $options;
    }

    public static function getOs($limit = null, $order = null, $filterString=null)
    {
        $osBySetor = [];

        if($filterString['third']){
            $isVerified = Check_OSModel::getAllVerifiedOS();
            if(!empty($isVerified)){

                $count = 0;
                $sectors = [];
                $pacientesNum = 0;
                $pacientes = [];
                $offset = 0;

                $os_numero_string = array_unique(array_column($isVerified, 'OS_NUMERO'));
                foreach ($os_numero_string as $key => $value) {
                    $os_numero_string[$key] = "'".$value."'";
                }
                
                $os_serie_string = array_unique(array_column($isVerified, 'OS_SERIE'));
                foreach ($os_serie_string as $key => $value) {
                    $os_serie_string[$key] = "'".$value."'";
                }

                $filterString['third'] = "OSM_NUM in (".implode(",", $os_numero_string).") AND OSM_SERIE in (".implode(",", $os_serie_string).")" ;

                $osSetor = Check_OSModel::getAllMinimalExamesByDatesAndSector($order, $filterString);

                foreach ($osSetor as $os) {
        
                    $osSerieNum = $os['OS_SERIE']  . " - " . $os['OS_NUMERO'];
        
                    $osBySetor[$count]['OS_SERIE_NUMERO'] = $osSerieNum;
                    $osBySetor[$count]['LANCAMENTO_PROGRAMADO'] = (new DateTime($os['LANCAMENTO']))->format("d/m/Y H:i");
                    $osBySetor[$count]['LANCAMENTO'] = (new DateTime($os['pedido_data']))->format("d/m/Y H:i");
                    $osBySetor[$count]['QTD'] = $os['LIBERADO']."<b style='font-size: 17px;'> / ".$os['ABERTO'] + $os['EXECUCAO'] + $os['LIBERADO']."</b>";
                    $osBySetor[$count]['SETOR'] = trim($os['setor']);
        
                    $pacientesNum++;
                    $pacientes[] = [
                        "text" => "(".$os['OS_SERIE']  . "-" . $os['OS_NUMERO'].") / (".trim($os['paciente_nome'])."-".$os['paciente_registro'].")",
                        "value" => $os['OS_SERIE']  . "-" . $os['OS_NUMERO']
                    ];
        
                    if(empty($sectors[trim($os['setor_codigo'])]))
                        $sectors[trim($os['setor_codigo'])] = trim($os['setor']);
        
                    $status = "<span class='badge bg-danger'>ERRO</span>";
                    if($os['TIPO_INTEGRACAO'] == "I"){
                        $status = "";
                        if($os['status_aberto'] !== 'false')
                            $status .= "<span class='badge bg-primary'>ABER</span>";
                        if($os['status_execucao'] !== 'false')
                            $status .= "<span class='badge bg-warning'>EXEC</span>";
                        if($os['status_liberacao'] !== 'false')
                            $status .= "<span class='badge bg-success'>LIB</span>";
                    }
        
                    $osBySetor[$count++]['STATUS'] = $status;
                }

                return [
                    "countAll" => count($osSetor),
                    "OSListLimit" => $osBySetor,
                    "sectors" => $sectors,
                    "pacientes" => $pacientes,
                    "pacientesNum" => $pacientesNum
                ];
            }
            else return [
                "countAll" => 0,
                "OSListLimit" => [],
                "sectors" => [],
                "pacientes" => [],
                "pacientesNum" => 0
            ];
        }
        
        if(!empty($filterString))
            $osSetor = Check_OSModel::getAllMinimalExamesByDatesAndSector($order, $filterString);
        
        else
            $osSetor = Check_OSModel::getAllMinimalExamesByDatesAndSector();
        
        $count = 0;
        $sectors = [];
        $pacientesNum = 0;
        $pacientes = [];
        $offset = 0;
        foreach ($osSetor as $os) {


            if(intval($os['RowNum']) <= $limit['start']){
                $pacientesNum++;
                $pacientes[] = [
                    "text" => "(".$os['OS_SERIE']  . "-" . $os['OS_NUMERO'].") / (".trim($os['paciente_nome'])."-".$os['paciente_registro'].")",
                    "value" => $os['OS_SERIE']  . "-" . $os['OS_NUMERO']
                ];
                continue;
            } 

            if(intval($os['RowNum']) > ($limit['length'] + $limit['start']) + $offset) {
                $pacientesNum++;
                $pacientes[] = [
                    "text" => "(".$os['OS_SERIE']  . "-" . $os['OS_NUMERO'].") / (".trim($os['paciente_nome'])."-".$os['paciente_registro'].")",
                    "value" => $os['OS_SERIE']  . "-" . $os['OS_NUMERO']
                ];
                continue;
            }

            $isVerified = Check_OSModel::getStatus($os['OS_SERIE'], $os['OS_NUMERO']);
            if(!empty($filterString['third'])){
                if ($isVerified[0]['STATUS'] == "V" && count($osBySetor) < 10){
                    $offset++;
                    continue;
                }
            }

            $osSerieNum = $os['OS_SERIE']  . " - " . $os['OS_NUMERO'];

            $osBySetor[$count]['OS_SERIE_NUMERO'] = $osSerieNum;
            $osBySetor[$count]['LANCAMENTO_PROGRAMADO'] = (new DateTime($os['LANCAMENTO']))->format("d/m/Y H:i");
            $osBySetor[$count]['LANCAMENTO'] = (new DateTime($os['pedido_data']))->format("d/m/Y H:i");
            $osBySetor[$count]['QTD'] = $os['LIBERADO']."<b style='font-size: 17px;'> / ".$os['ABERTO'] + $os['EXECUCAO'] + $os['LIBERADO']."</b>";
            $osBySetor[$count]['SETOR'] = trim($os['setor']);

            $pacientesNum++;
            $pacientes[] = [
                "text" => "(".$os['OS_SERIE']  . "-" . $os['OS_NUMERO'].") / (".trim($os['paciente_nome'])."-".$os['paciente_registro'].")",
                "value" => $os['OS_SERIE']  . "-" . $os['OS_NUMERO']
            ];

            if(empty($sectors[trim($os['setor_codigo'])]))
                $sectors[trim($os['setor_codigo'])] = trim($os['setor']);

            $status = "<span class='badge bg-danger'>ERRO</span>";
            if($os['TIPO_INTEGRACAO'] == "I"){
                $status = "";
                if($os['status_aberto'] !== 'false')
                    $status .= "<span class='badge bg-primary'>ABER</span>";
                if($os['status_execucao'] !== 'false')
                    $status .= "<span class='badge bg-warning'>EXEC</span>";
                if($os['status_liberacao'] !== 'false')
                    $status .= "<span class='badge bg-success'>LIB</span>";
            }

            $osBySetor[$count++]['STATUS'] = $status;
        }

        // if(!empty($filterString['first']))
            

        return [
            "countAll" => count($osSetor),
            "OSListLimit" => $osBySetor,
            "sectors" => $sectors,
            "pacientes" => $pacientes,
            "pacientesNum" => $pacientesNum
        ];
    }

    public static function ordernateIntegration(array $osList, $order)
    {
        $result = usort($osList, function($a, $b) use (&$order){

            if ($a['TIPO_INTEGRACAO'] == $b['TIPO_INTEGRACAO']) return 0;

            if($order['dir'] == "desc"){
                if($a['TIPO_INTEGRACAO'] == "NI" &&
                    $b['TIPO_INTEGRACAO'] != $a['TIPO_INTEGRACAO']) return 1;

                if($a['TIPO_INTEGRACAO'] != "NI" &&
                    $b['TIPO_INTEGRACAO'] != $a['TIPO_INTEGRACAO']) return -1;
            }
            else{
                if($a['TIPO_INTEGRACAO'] == "NI" &&
                    $b['TIPO_INTEGRACAO'] != $a['TIPO_INTEGRACAO']) return -1;

                if($a['TIPO_INTEGRACAO'] != "NI" &&
                    $b['TIPO_INTEGRACAO'] != $a['TIPO_INTEGRACAO']) return 1;
            }
        });
        
        return $result === true ? $osList : [];
    }

    public static function getOSModal(Request $request)
    {
        $post = $request->getPostVars();
        $osDetails = Check_OSModel::getExameByOS($post['os_serie'], $post['os_numero']);
        $isVerified = Check_OSModel::getStatus($post['os_serie'], $post['os_numero']);

        $osExames = "";
        foreach ($osDetails as $details) {
            $observacao = "";

            if(!empty(Check_OSModel::getObsByExame($post['os_serie'], $post['os_numero'], $details['SMM_NUM']))){
                $observacao = "<i class='fas fa-exclamation-triangle text-danger'></i>";
            }

            if($details['ST'] == "A"){
                $color = "primary"; 
                $spanName = "Aberto";
            }
            elseif($details['ST'] == "L"){
                $color = "success";
                $spanName = "Liberado";
            }
            elseif($details['ST'] == "X"){
                $color = "warning";
                $spanName = "Em execução";
            }
                

            $osExames .= View::render('check_os/exame', [
                "ordinary-os" => $details['SMM_NUM'],
                "quantidade" => $details['QTD'],
                "nome-exame" => $details['EXAME'],
                "amostra" => $details['amostra'],
                "cod-exame" => $details['codigo_exame'],
                "status" => "<span class='badge bg-".$color."'>".$spanName."</span>",
                "observacao" => $observacao
            ]);
        }
        
        return View::render('check_os/modalOS', [
            "os-serie-number" => "(" . $post['os_serie'] ." - ". $post['os_numero'] . ")  /  (" . (new DateTime($osDetails[0]['LANCAMENTO']))->format("d/m/Y H:i"). ")",
            "body" => (!empty($isVerified) && !empty($isVerified[0]['COMENTARIO']) ? View::render('utils/alert_label', [
                    'color' => "navy",
                    "text" => "<b>Comentário</b>:<br> ".$isVerified[0]['COMENTARIO']
                ]) : "").
                (($osDetails[0]['tipo_os'] == "ASS") ? View::render('utils/alert_label', [
                    'color' => "danger",
                    "text" => "<b>OS fora do atendimento!</b>"
                ]) : "")
                .View::render('check_os/os_info_hidden', [
                "registro" => $osDetails[0]['paciente_registro'],
                "nome" => trim($osDetails[0]['paciente_nome']),
                "sexo" => ($osDetails[0]['paciente_sexo'] == "M" ? "Masculino" : ($osDetails[0]['paciente_sexo'] == "F" ? "Feminino" : "Outro")),
                "cpf" => trim($osDetails[0]['paciente_cpf']),
                "rg" => trim($osDetails[0]['paciente_rg']),
                "data-nascimento" => (new DateTime($osDetails[0]['paciente_dtnasc']))->format("Y-m-d"),
                "convenio" => $osDetails[0]['convenio_nome'],
                "unidade" => trim($osDetails[0]['setor']),
                "leito" => trim($osDetails[0]['LOCAL_NOME']),
                "solicitante" => trim($osDetails[0]['solicitante_nome']),
                "crm" => $osDetails[0]['solicitante_registro'],
                "data-lancamento" => (new DateTime($osDetails[0]['pedido_data']))->format("d/m/Y H:i"),
                "indicacao-clinica" => $osDetails[0]['pedido_indicacao'],
                "rows-exames" => $osExames
            ]),
            "modal-footer" => empty($isVerified) ? View::render('check_os/footer_modalOS') : 
                ($isVerified[0]['STATUS'] == "NV" ? View::render('check_os/footer_modalOS') : "")
        ]);
    }

    public static function verifyOS(Request $request)
    {
        $post = $request->getPostVars();

        if(Check_OSModel::verifyOS($post['os_serie'], $post['os_numero'], $request->user->id, $post['obs']) === false)
            return [
                "success" => false,
                "title" => "Falha na verificação da OS!"
            ];

        return [
            "success" => true,
            "title" => "Verificação da OS foi feita com sucesso!"
        ]; 
    }

    public static function getOSModalExame(Request $request)
    {
        $get = $request->getQueryParams();

        $osDetails = Check_OSModel::getExameByOS($get['os_serie'], $get['os_numero'], $get['os_num_exame']);

        $observacao = "";

        $observacaoBanco = Check_OSModel::getObsByExame($get['os_serie'], $get['os_numero'], $get['os_num_exame']);
        
        if($osDetails[0]['ST'] == "A")
            $spanName = "Aberto";

        elseif($osDetails[0]['ST'] == "L")
            $spanName = "Liberado";

        elseif($osDetails[0]['ST'] == "X")
            $spanName = "Em execução";

        
        $justificativas = Check_OSModel::getJustificativas();

        $options = View::render('utils/option', [
            "id" => "",
            "selected" => "selected",
            "disabled" => "disabled",
            "nome" => "Selecione a justificativa"
        ]);
        foreach ($justificativas as $key => $justificativa) {
            $options .= View::render('utils/option', [
                "id" => $justificativa['id'],
                "selected" => !empty($observacaoBanco[0]) && $observacaoBanco[0]['justificativa'] == $justificativa['id'] ? "selected" : "",
                "disabled" => "",
                "nome" => $justificativa['nome']
            ]);
        }

        return View::render('check_os/modalObsOS', [
            "os-serie-number" => "(".$get['os_serie']." - ".$get['os_numero'].")",
            "ssm-num" => "Exame ".$get['os_num_exame'],
            "nome" => $osDetails[0]['EXAME'],
            'quantidade' => $osDetails[0]['QTD'],
            "amostra" => $osDetails[0]['amostra'],
            "cod-exame" => $osDetails[0]['codigo_exame'],
            "status" => $spanName,
            "observacao" => empty($observacaoBanco) ? $observacao : $observacaoBanco[0]['observacao'],
            "justificativas" => View::render("utils/select", [
                "class" => "form-control",
                "id" => "justificativa",
                "disabled" => "",
                "options" => $options,
                "style" => "border: 1px solid #ced4da !important;"
            ])
        ]);
    }

    public static function submitObsExame(Request $request)
    {
        $post = $request->getPostVars();

        $observacaoBanco = Check_OSModel::getObsByExame($post['os_serie'], $post['os_numero'], $post['os_num_exame']);

        if(empty($observacaoBanco)) 
            Check_OSModel::addObsInExame($post['os_serie'], $post['os_numero'], $post['os_num_exame'], $post['observacao'], $post['justificativa']);
        else 
            Check_OSModel::updateObsInExame($observacaoBanco[0]['id'], $post['observacao'], $post['justificativa']);
        
        return true;
    }
}
