<?php

namespace App\Controller\Check_Exame;

use \App\Utils\View;
use \App\Http\Request;
use \App\Controller\Layout\Layout as LayoutPage;
use \App\Model\Check_Exame\Check_ExameModel;
use App\Model\Check_OS\Check_OSModel;
use \App\Model\Utils\Spy;
use DateTime;
use DateTimeZone;
use ZipArchive;

class Check_ExameController extends LayoutPage
{
    public static function getHome(Request $request)
    {
        $examesString = '';
        $exames = Check_ExameModel::getExamesName();
        foreach ($exames as $exame) {
            $examesString .= View::render('utils/option', [
                'id' => $exame['CODIGO'],
                'nome' => $exame['NOME']." - ".$exame['CODIGO'],
                'selected' => '',
                'disabled' => '' 
            ]);
        }

        $content = View::render('check_exame/home', [
            'exames' => $examesString
        ]);

        // Atualiza o acesso do usuario nesse sistema
        // Spy::updateAcess($request->user, 19, 'check_exame');
      
        return parent::getPage("Check Exame", "check_exame", $content, $request);
    }

    public static function getPacientes($request)
    {
        $post = $request->getPostVars();

        $colums = ["NOME", "REGISTRO"];

        $where = [];

        $join = null;

        $dataInicial = null;
        $dataFinal = null;
        if(!empty($post['dataInicio'])){
            $dataInicial = (new DateTime($post['dataInicio']['date']))->format("Y-m-d");
            $dataInicial .= " ".(new DateTime($post['dataInicio']['time']))->format("H:i");
            $dataFinal = (new DateTime($post['dataFim']['date']))->format("Y-m-d");
            $dataFinal .= " ".(new DateTime($post['dataFim']['time']))->format("H:i");

            $where[] = "IMG_RCL_RCL_DTHR between '".$dataInicial."' AND '".$dataFinal."'";
        }

        if(!empty($post['pacientes'])){
            foreach ($post['pacientes'] as $key => $paciente) {
                $post['pacientes'][$key] = "'".$paciente."'";
            }

            $where[] = "IMG_RCL_RCL_PAC in (".implode(',', $post['pacientes']).")";
        }

        if(!empty($post['exames'])){
            foreach ($post['exames'] as $key => $paciente) {
                $post['exames'][$key] = "'".$paciente."'";
            }

            $where[] = "smk_cod in (".implode(',', $post['exames']).")";
        }

        $order = "";
        if (!empty($post['order'])) {
            $endItem = end($post['order']);
            $firstItem = reset($post['order']);

            foreach ($post['order'] as $item) {

                if ($item == $firstItem){
                    if($item['column'] == 0)
                        $order = "PACIENTES.NOME " . $item['dir'] .", PACIENTES.REGISTRO " . $item['dir'] . (count($post['order']) > 1 ? ", " : "");
                    else
                        $order = "PACIENTES.".$colums[$item['column']] . " " . $item['dir'] . (count($post['order']) > 1 ? ", " : "");
                }

                elseif ($item == $endItem)
                    $order .= "PACIENTES.".$colums[$item['column']] . " " . $item['dir'];

                else
                    $order .= "PACIENTES.".$colums[$item['column']] . " " . $item['dir'] . ", ";
            }
        }

        $pacientes = Check_ExameModel::getPacientes($where, $order, $join);

        $pacientesNum = 0;
        $pacientesArraySearch = [];
        $pacientesArray = [];
        $examesRegistrosArraySearch = [];
        $count = 0;
        foreach ($pacientes as $paciente) {
            
            if(intval($paciente['RowNum']) <= $_POST['start']){
                $pacientesNum++;
                $pacientesArraySearch[] = [
                    "text" => "(".trim($paciente['NOME'])."-".$paciente['REGISTRO'].")",
                    "value" => $paciente['REGISTRO']
                ];
                $examesRegistrosArraySearch[] = $paciente['REGISTRO'];

                continue;
            } 
    
            if(intval($paciente['RowNum']) > ($_POST['length'] + $_POST['start'])) {
                $pacientesNum++;
                $pacientesArraySearch[] = [
                    "text" => "(".trim($paciente['NOME'])."-".$paciente['REGISTRO'].")",
                    "value" => $paciente['REGISTRO']
                ];
                $examesRegistrosArraySearch[] = $paciente['REGISTRO'];
                continue;
            }

            $pacientesNum++;
            $pacientesArray[$count]['NOME'] = $paciente['NOME'];
            $pacientesArray[$count++]['REGISTRO'] = $paciente['REGISTRO'];
            $pacientesArraySearch[] = [
                "text" => "(".trim($paciente['NOME'])."-".$paciente['REGISTRO'].")",
                "value" => $paciente['REGISTRO']
            ];
            $examesRegistrosArraySearch[] = $paciente['REGISTRO'];
        }

        $examesFiltered = self::getAllExamesFromFilteredPacientes($examesRegistrosArraySearch, "IMG_RCL_RCL_DTHR between '".$dataInicial."' AND '".$dataFinal."'");

        return [
            "draw" => $_POST['draw'],
            "recordsTotal" => count($pacientesArray),
            "recordsFiltered" => $pacientesNum,
            "data" => $pacientesArray,
            'selectRegistros' => $pacientesArraySearch,
            'selectExames' => $examesFiltered
        ];
    }

    public static function getAllExamesFromFilteredPacientes($registros, $dataInterval)
    {
        foreach ($registros as $key => $registro) {
            $registros[$key] = "'".$registro."'";
        }

        $registrosImplode = implode(',', $registros);

        if(!empty($registrosImplode)){
            $exames = Check_ExameModel::getExamesFiltered($registrosImplode, $dataInterval);
            $lastExameArray = [];
            foreach ($exames as $exame) {
                $lastExameArray[] = ["text" => '('.$exame['text'].'-'.$exame['value'].')', "value" => $exame['value']];
            }
            return $lastExameArray;
        }
        return [];
    }

    public static function getPacienteExamesModal($request)
    {
        $post = $request->getPostVars();

        $dataInicial = (new DateTime($post['dataInicio']['date']))->format("Y-m-d");
        $dataInicial .= " ".(new DateTime($post['dataInicio']['time']))->format("H:i");
        $dataFinal = (new DateTime($post['dataFim']['date']))->format("Y-m-d");
        $dataFinal .= " ".(new DateTime($post['dataFim']['time']))->format("H:i");

        foreach ($post['exames'] as $key => $value) {
            $post['exames'][$key] = "'".$value."'";
        }

        $whereExames = null;
        if(!empty($post['exames'])){
            $whereExames = "smk_cod in (".implode(',', $post['exames']).")";
        }


        $whereData = "IMG_RCL_RCL_DTHR between '".$dataInicial."' AND '".$dataFinal."'";

        $exames = Check_ExameModel::getPacienteExames($post['registro'], $whereData, $whereExames);

        $examesString = "";
        foreach ($exames as $exame) {
            $examesString .= View::render('/check_exame/linkExame', [
                'id-exame' => $exame['id'],
                'registro' => $post['registro'],
                'codigo' => $exame['codigo'],
                'nome-exame' => $exame['nomeExame'],
                'dthr-exame' => (new DateTime($exame['dthr']))->format('d/m/Y H:i')
            ]);
        }

        return View::render('/check_exame/pacienteExamesModal', [
            'registro' => $post['registro'],
            'exames' => $examesString,
            'dataInicial' => (new DateTime($dataInicial))->format('Y-m-d').'T'.(new DateTime($dataInicial))->format('H:i'),
            'dataFinal' => (new DateTime($dataFinal))->format('Y-m-d').'T'.(new DateTime($dataFinal))->format('H:i')
        ]);
    }
    
    public static function getFile($request, $exame, $registro)
    {
        $arquivo = Check_ExameModel::getFile($exame, $registro);
        return $arquivo;
    }

    public static function getAllFilesFromPaciente($request, $dataInicial, $dataFinal, $registro)
    {
        $dataInicial = str_replace('T', ' ', $dataInicial);
        $dataFinal = str_replace('T', ' ', $dataFinal);

        $exames = Check_ExameModel::getPacienteExames($registro, "IMG_RCL_RCL_DTHR between '".$dataInicial."' AND '".$dataFinal."'", null, true);
        $nome = trim($exames[0]['nomePaciente']);

        $name = tempnam(sys_get_temp_dir(), "FOO");
        $zip = new ZipArchive;
        $res = $zip->open($name, ZipArchive::CREATE);
        if ($res === TRUE) {
            foreach ($exames as $exame) {
                $zip->addFromString($exame['nomeExame'].'-'.((new DateTime($exame['dthr']))->format('d-m-Y')).'.pdf', $exame['arquivo']);
            }
            $zip->close();
            $sizeFile = filesize($name);
        } 
        else return 'erro';

        return ['nomePaciente' => $name, 'size' => $sizeFile];
    }
}