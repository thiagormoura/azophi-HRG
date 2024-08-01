<?php

namespace App\Controller\OuviMed;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Http\Request;
use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use App\Model\OuviMed\Manifestacao;
use App\Model\OuviMed\Setor;
use App\Model\Entity\User;
use App\Model\OuviMed\Dashboard;

class DashboardController extends LayoutPage
{

    public static function getDashboardPage(Request $request){

        $options = '<option value="" selected disabled>Selecione os setores envolvidos</option>';
      
        foreach (Setor::getAllSectors() as $setor) {
            $options .= View::render('utils/option', [
                'id' => $setor['id'],
                'nome' => $setor['nome']
            ]);
        }
       
        $select = View::render('utils/select', [
            'class' => '',
            'name' => '',
            'id' => 'slSetoresEnvolvidosDashboard',
            'style' => '',
            'disabled' => '',
            'options' => $options,
            'required' => 'required'
        ]);

        $filtros = View::render('ouvimed/dashboard/filtros', [
            'select-setores-envolvidos' => $select
        ]);

        $content = View::render('ouvimed/dashboard', [
            'filtros' => $filtros
        ]);

        return parent::getPage('Dashboard/Ouvimed - Dashboard', 'OuviMed', $content, $request);
    }

    public static function getDashboardsData(Request $request){

        $filtros = $request->getPostVars();

        // Charts datas
        $dataChart1 = Dashboard::getChart1Data($filtros);
        $dataChart2 = Dashboard::getChart2Data($filtros);
        $dataChart3 = Dashboard::getChart3Data($filtros);
        $dataChart4 = Dashboard::getChart4Data($filtros);
        $dataChart5 = Dashboard::getChart5Data($filtros);
        $dataChart6 = Dashboard::getChart6Data($filtros);

        $finalData['chart1'] = self::prepareDataChart1($dataChart1);
        $finalData['chart2'] = self::prepareDataChart2($dataChart2);
        $finalData['chart3'] = self::prepareDataChart3($dataChart3);
        $finalData['chart4'] = self::prepareDataChart4($dataChart4);
        $finalData['chart5'] = self::prepareDataChart5($dataChart5);
        $finalData['chart6'] = self::prepareDataChart6($dataChart6);

        return $finalData;

    }

    public static function prepareDataChart1($dataChart1){

        // Create array for each status
        $aberto = array();
        $processamento = array();
        $finalizado = array();
        $cancelado = array();

        // Fill each array with "status" as "label" and 0 as "y"
        foreach ($dataChart1 as $key => $value) {
            if($value['status'] == 'A'){
                $aberto[] = array(
                    'label' => 'Aberto',
                    'y' => 0,
                    'color' => '#0366fc',
                    'percent' => 0
                );
            }
            if($value['status'] == 'EA'){
                $processamento[] = array(
                    'label' => 'Processamento',
                    'y' => 0,
                    'color' => '#fcba03',
                    'percent' => 0
                );
            }
            if($value['status'] == 'F'){
                $finalizado[] = array(
                    'label' => 'Finalizado',
                    'y' => 0,
                    'color' => '#069e1d',
                    'percent' => 0
                );
            }
            if($value['status'] == 'C'){
                $cancelado[] = array(
                    'label' => 'Cancelado',
                    'y' => 0,
                    'color' => '#524e4e',
                    'percent' => 0
                );
            }
        }

        // get total of manifestacoes
        $total = 0;
        foreach ($dataChart1 as $key => $value) {
            $total += $value['quantidade'];
        }

        // Loop data and count ocourrences in "y" for each status
        foreach ($dataChart1 as $key => $value) {
            if($value['status'] == 'A'){
                foreach ($aberto as $key2 => $value2) {
                    $aberto[$key2]['y'] = $value['quantidade'];
                    $aberto[$key2]['percent'] = round(($value['quantidade'] / $total) * 100, 2);
                }
            }
            if($value['status'] == 'EA'){
                foreach ($processamento as $key2 => $value2) {
                    $processamento[$key2]['y'] = $value['quantidade'];
                    $processamento[$key2]['percent'] = round(($value['quantidade'] / $total) * 100, 2);
                }
            }
            if($value['status'] == 'F'){
                foreach ($finalizado as $key2 => $value2) {
                    $finalizado[$key2]['y'] = $value['quantidade'];
                    $finalizado[$key2]['percent'] = round(($value['quantidade'] / $total) * 100, 2);
                }
            }
            if($value['status'] == 'C'){
                foreach ($cancelado as $key2 => $value2) {
                    $cancelado[$key2]['y'] = $value['quantidade'];
                    $cancelado[$key2]['percent'] = round(($value['quantidade'] / $total) * 100, 2);
                }
            }
        }

        return array_merge($aberto, $processamento, $finalizado, $cancelado);
        
    }

    public static function prepareDataChart2($dataChart2){

        // get unique setores id and nome
        $setores = array();
        foreach ($dataChart2 as $key => $value) {
            $setores[$value['id']] = $value['nome'];
        }

        // create array for each status: Aberto, Processamento, Finalizado, Cancelado
        $aberto = array();
        $processamento = array();
        $finalizado = array();
        $cancelado = array();

        // fill each array with "id" as "x", "nome" as "label" and 0 as "y".
        foreach ($setores as $key => $value) {
            $aberto[] = array(
                'x' => $key,
                'label' => $value,
                'y' => 0
            );
            $processamento[] = array(
                'x' => $key,
                'label' => $value,
                'y' => 0
            );
            $finalizado[] = array(
                'x' => $key,
                'label' => $value,
                'y' => 0
            );
            $cancelado[] = array(
                'x' => $key,
                'label' => $value,
                'y' => 0
            );
        }

        // loop data and count ocourrences in "y" for each status
        foreach ($dataChart2 as $key => $value) {
            if($value['status'] == 'A'){
                foreach ($aberto as $key2 => $value2) {
                    if($value2['x'] == $value['id']){
                        $aberto[$key2]['y'] = $value['quantidade'];
                    }
                }
            }
            if($value['status'] == 'EA'){
                foreach ($processamento as $key2 => $value2) {
                    if($value2['x'] == $value['id']){
                        $processamento[$key2]['y'] = $value['quantidade'];
                    }
                }
            }
            if($value['status'] == 'F'){
                foreach ($finalizado as $key2 => $value2) {
                    if($value2['x'] == $value['id']){
                        $finalizado[$key2]['y'] = $value['quantidade'];
                    }
                }
            }
            if($value['status'] == 'C'){
                foreach ($cancelado as $key2 => $value2) {
                    if($value2['x'] == $value['id']){
                        $cancelado[$key2]['y'] = $value['quantidade'];
                    }
                }
            }
        }

        // replace "x" with numeral index
        foreach ($aberto as $key => $value) {
            $aberto[$key]['x'] = $key;
        }
        foreach ($processamento as $key => $value) {
            $processamento[$key]['x'] = $key;
        }
        foreach ($finalizado as $key => $value) {
            $finalizado[$key]['x'] = $key;
        }
        foreach ($cancelado as $key => $value) {
            $cancelado[$key]['x'] = $key;
        }

        return array(
            'aberto' => $aberto,
            'processamento' => $processamento,
            'finalizado' => $finalizado,
            'cancelado' => $cancelado
        );

    }

    public static function prepareDataChart3($dataChart3){

        // create array for each veiculo
        $presencial = array(
            'label' => 'Presencial',
            'y' => 0,
            'color' => '#0366fc'
        );
        $telefone = array(
            'label' => 'Telefone',
            'y' => 0,
            'color' => '#fcba03'
        );
        $caixa_sugestoes = array(
            'label' => 'Caixa de Sugestões',
            'y' => 0,
            'color' => '#069e1d'
        );
        $email = array(
            'label' => 'E-mail',
            'y' => 0,
            'color' => '#524e4e'
        );
        $rede_social = array(
            'label' => 'Rede Social',
            'y' => 0,
            'color' => '#8403fc'
        );
        $outro = array(
            'label' => 'Outro',
            'y' => 0,
            'color' => '#fc037b'
        
        );

        // loop data and count ocourrences in "y" for each veiculo
        foreach ($dataChart3 as $key => $value) {
            if($value['veiculo_manifestacao'] == 'PRESENCIAL'){
                $presencial['y'] = $value['quantidade'];
            }
            if($value['veiculo_manifestacao'] == 'TELEFONE'){
                $telefone['y'] = $value['quantidade'];
            }
            if($value['veiculo_manifestacao'] == 'CAIXA_SUGESTOES'){
                $caixa_sugestoes['y'] = $value['quantidade'];
            }
            if($value['veiculo_manifestacao'] == 'EMAIL'){
                $email['y'] = $value['quantidade'];
            }
            if($value['veiculo_manifestacao'] == 'REDE_SOCIAL'){
                $rede_social['y'] = $value['quantidade'];
            }
            if($value['veiculo_manifestacao'] == 'OUTRO'){
                $outro['y'] = $value['quantidade'];
            }
        }

        // return array with all veiculos with y > 0
        $return = array();
        if($presencial['y'] > 0){
            $return[] = $presencial;
        }
        if($telefone['y'] > 0){
            $return[] = $telefone;
        }
        if($caixa_sugestoes['y'] > 0){
            $return[] = $caixa_sugestoes;
        }
        if($email['y'] > 0){
            $return[] = $email;
        }
        if($rede_social['y'] > 0){
            $return[] = $rede_social;
        }
        if($outro['y'] > 0){
            $return[] = $outro;
        }


        return $return;


    }

    public static function prepareDataChart4($dataChart4){

        // get unique setores id and nome
        $setores = array();
        foreach ($dataChart4 as $key => $value) {
            $setores[$value['id']] = $value['nome'];
        }

        // create array for each veiculo
        $presencial = array();
        $telefone = array();
        $caixa_sugestoes = array();
        $email = array();
        $rede_social = array();
        $outro = array();

        // fill each array with "id" as "x", "nome" as "label" and 0 as "y".
        foreach ($setores as $key => $value) {
            $presencial[] = array(
                'x' => $key,
                'label' => $value,
                'y' => 0
            );
            $telefone[] = array(
                'x' => $key,
                'label' => $value,
                'y' => 0
            );
            $caixa_sugestoes[] = array(
                'x' => $key,
                'label' => $value,
                'y' => 0
            );
            $email[] = array(
                'x' => $key,
                'label' => $value,
                'y' => 0
            );
            $rede_social[] = array(
                'x' => $key,
                'label' => $value,
                'y' => 0
            );
            $outro[] = array(
                'x' => $key,
                'label' => $value,
                'y' => 0
            );
        }

        // loop data and count ocourrences in "y" for each status
        foreach ($dataChart4 as $key => $value) {
            if($value['veiculo_manifestacao'] == 'PRESENCIAL'){
                foreach ($presencial as $key2 => $value2) {
                    if($value2['x'] == $value['id']){
                        $presencial[$key2]['y'] = $value['quantidade'];
                    }
                }
            }
            if($value['veiculo_manifestacao'] == 'TELEFONE'){
                foreach ($telefone as $key2 => $value2) {
                    if($value2['x'] == $value['id']){
                        $telefone[$key2]['y'] = $value['quantidade'];
                    }
                }
            }
            if($value['veiculo_manifestacao'] == 'CAIXA_SUGESTOES'){
                foreach ($caixa_sugestoes as $key2 => $value2) {
                    if($value2['x'] == $value['id']){
                        $caixa_sugestoes[$key2]['y'] = $value['quantidade'];
                    }
                }
            }
            if($value['veiculo_manifestacao'] == 'EMAIL'){
                foreach ($email as $key2 => $value2) {
                    if($value2['x'] == $value['id']){
                        $email[$key2]['y'] = $value['quantidade'];
                    }
                }
            }
            if($value['veiculo_manifestacao'] == 'REDE_SOCIAL'){
                foreach ($rede_social as $key2 => $value2) {
                    if($value2['x'] == $value['id']){
                        $rede_social[$key2]['y'] = $value['quantidade'];
                    }
                }
            }
            if($value['veiculo_manifestacao'] == 'OUTRO'){
                foreach ($outro as $key2 => $value2) {
                    if($value2['x'] == $value['id']){
                        $outro[$key2]['y'] = $value['quantidade'];
                    }
                }
            }
        }

        // replace "x" with numeral index
        foreach ($presencial as $key => $value) {
            $presencial[$key]['x'] = $key;
        }
        foreach ($telefone as $key => $value) {
            $telefone[$key]['x'] = $key;
        }
        foreach ($caixa_sugestoes as $key => $value) {
            $caixa_sugestoes[$key]['x'] = $key;
        }
        foreach ($email as $key => $value) {
            $email[$key]['x'] = $key;
        }
        foreach ($rede_social as $key => $value) {
            $rede_social[$key]['x'] = $key;
        }
        foreach ($outro as $key => $value) {
            $outro[$key]['x'] = $key;
        }

        return array(
            'presencial' => $presencial,
            'telefone' => $telefone,
            'caixa_sugestoes' => $caixa_sugestoes,
            'email' => $email,
            'rede_social' => $rede_social,
            'outro' => $outro
        );

    }

    public static function prepareDataChart5($dataChart5){

        // create arrays for all identificações
        $elogio = array(
            'label' => 'Elogio',
            'y' => 0,
            'color' => '#069e1d'
        );
        $reclamação = array(
            'label' => 'Reclamação',
            'y' => 0,
            'color' => '#bd1c11'
        );
        $solicitação = array(
            'label' => 'Solicitação',
            'y' => 0,
            'color' => '#0366fc'
        );
        $informação = array(
            'label' => 'Informação',
            'y' => 0,
            'color' => '#8403fc'
        );
        $sugestao = array(
            'label' => 'Sugestão',
            'y' => 0,
            'color' => '#fc037b'
        );
        $critica = array(
            'label' => 'Crítica/Comentário',
            'y' => 0,
            'color' => '#fcba03'
        );
        $denuncia = array(
            'label' => 'Denúncia',
            'y' => 0,
            'color' => '#524e4e'
        );

        // loop data and count ocourrences in "y" for each status
        foreach ($dataChart5 as $key => $value) {
            if($value['codigo'] == 'ELOGIO'){
                $elogio['y'] = $value['quantidade'];
            }
            if($value['codigo'] == 'RECLAMACAO'){
                $reclamação['y'] = $value['quantidade'];
            }
            if($value['codigo'] == 'SOLICITACAO'){
                $solicitação[0]['y'] = $value['quantidade'];
            }
            if($value['codigo'] == 'INFORMACAO'){
                $informação['y'] = $value['quantidade'];
            }
            if($value['codigo'] == 'SUGESTAO'){
                $sugestao['y'] = $value['quantidade'];
            }
            if($value['codigo'] == 'CRITICA'){
                $critica['y'] = $value['quantidade'];
            }
            if($value['codigo'] == 'DENUNCIA'){
                $denuncia['y'] = $value['quantidade'];
            }
        }

        // return arrays with y > 0
        $return = array();
        if($elogio['y'] > 0){
            $return[] = $elogio;
        }
        if($reclamação['y'] > 0){
            $return[] = $reclamação;
        }
        if($solicitação['y'] > 0){
            $return[] = $solicitação;
        }
        if($informação['y'] > 0){
            $return[] = $informação;
        }
        if($sugestao['y'] > 0){
            $return[] = $sugestao;
        }
        if($critica['y'] > 0){
            $return[] = $critica;
        }
        if($denuncia['y'] > 0){
            $return[] = $denuncia;
        }
        
        return $return;
      
    }

    public static function prepareDataChart6($dataChart6){

        // get unique setores id and nome
        $setores = array();
        foreach ($dataChart6 as $key => $value) {
            $setores[$value['id']] = $value['nome'];
        }

        // create arrays for all identificações
        $elogio = array();
        $reclamacao = array();
        $solicitacao = array();
        $informação = array();
        $sugestao = array();
        $critica = array();
        $denuncia = array();

        // loop setores and create arrays
        foreach ($setores as $key => $value) {
            $elogio[] = array(
                'x' => $key,
                'label' => $value,
                'y' => 0,
            );
            $reclamacao[] = array(
                'x' => $key,
                'label' => $value,
                'y' => 0,
            );
            $solicitacao[] = array(
                'x' => $key,
                'label' => $value,
                'y' => 0,
            );
            $informação[] = array(
                'x' => $key,
                'label' => $value,
                'y' => 0,
            );
            $sugestao[] = array(
                'x' => $key,
                'label' => $value,
                'y' => 0,
            );
            $critica[] = array(
                'x' => $key,
                'label' => $value,
                'y' => 0,
            );
            $denuncia[] = array(
                'x' => $key,
                'label' => $value,
                'y' => 0,
            );
        }

        // loop data and count ocourrences in "y" for each status
        foreach ($dataChart6 as $key => $value) {
            if($value['codigo'] == 'ELOGIO'){
                foreach ($elogio as $key2 => $value2) {
                    if($value2['x'] == $value['id']){
                        $elogio[$key2]['y'] = $value['quantidade'];
                    }
                }
            }
            if($value['codigo'] == 'RECLAMACAO'){
                foreach ($reclamacao as $key2 => $value2) {
                    if($value2['x'] == $value['id']){
                        $reclamacao[$key2]['y'] = $value['quantidade'];
                    }
                }
            }
            if($value['codigo'] == 'SOLICITACAO'){
                foreach ($solicitacao as $key2 => $value2) {
                    if($value2['x'] == $value['id']){
                        $solicitacao[$key2]['y'] = $value['quantidade'];
                    }
                }
            }
            if($value['codigo'] == 'INFORMACAO'){
                foreach ($informação as $key2 => $value2) {
                    if($value2['x'] == $value['id']){
                        $informação[$key2]['y'] = $value['quantidade'];
                    }
                }
            }
            if($value['codigo'] == 'SUGESTAO'){
                foreach ($sugestao as $key2 => $value2) {
                    if($value2['x'] == $value['id']){
                        $sugestao[$key2]['y'] = $value['quantidade'];
                    }
                }
            }
            if($value['codigo'] == 'CRITICA'){
                foreach ($critica as $key2 => $value2) {
                    if($value2['x'] == $value['id']){
                        $critica[$key2]['y'] = $value['quantidade'];
                    }
                }
            }
            if($value['codigo'] == 'DENUNCIA'){
                foreach ($denuncia as $key2 => $value2) {
                    if($value2['x'] == $value['id']){
                        $denuncia[$key2]['y'] = $value['quantidade'];
                    }
                }
            }

        }

        // replace "x" with numeral index
        foreach ($elogio as $key => $value) {
            $elogio[$key]['x'] = $key;
        }
        foreach ($reclamacao as $key => $value) {
            $reclamacao[$key]['x'] = $key;
        }
        foreach ($solicitacao as $key => $value) {
            $solicitacao[$key]['x'] = $key;
        }
        foreach ($informação as $key => $value) {
            $informação[$key]['x'] = $key;
        }
        foreach ($sugestao as $key => $value) {
            $sugestao[$key]['x'] = $key;
        }
        foreach ($critica as $key => $value) {
            $critica[$key]['x'] = $key;
        }
        foreach ($denuncia as $key => $value) {
            $denuncia[$key]['x'] = $key;
        }

        return array(
            'elogio' => $elogio,
            'reclamacao' => $reclamacao,
            'solicitacao' => $solicitacao,
            'informacao' => $informação,
            'sugestao' => $sugestao,
            'critica' => $critica,
            'denuncia' => $denuncia
        );


    }




}