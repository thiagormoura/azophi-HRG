<?php

namespace App\Controller\Monps;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use \App\Model\Monps\Paciente as PacienteModel;
use \App\Model\Monps\Exame as ExameModel;

class HomePaciente extends LayoutPage
{
  // Método responsável por retornar as filas do paciente
  private static function getFilasPaciente($registro)
  {
    $filas = PacienteModel::getFilas($registro);
    $linhaFilas = '';
    foreach ($filas as $fila) {
      $linhaFilas .= View::render('monps/modals/filas', [
        'fila-nome' => $fila['fila'],
        'fila-status' => $fila['status_fila'],
        'fila-tempo' => $fila['tempo'] . ' min',
      ]);
    }
    return $linhaFilas;
  }

  // Método responsável por retornar os exames do paciente
  private static function getExame($bgExame, $colorText, $title, $exame)
  {
    return View::render('monps/modals/exame', [
      'bg-exame' => $bgExame,
      'color-text' => $colorText,
      'title' => $title,
      'exame' => $exame,
    ]);
  }

  // Método responsável por retornar a quantidade de exames do paciente
  private static function getQtdExames($exames, $type)
  {
    if (count($exames) == 0) return View::render('monps/modals/sem_exames', ['type' => ucfirst($type)]);

    $count = array(
      'solicitado' => 0,
      'executado' => 0,
      'liberado' => 0
    );
    $solicitados = '';
    $executando = '';
    $liberados = '';

    foreach ($exames as $exame) {
      if ($exame['smm_exec'] == 'A') {
        $count['solicitado']++;
        $solicitados .= self::getExame('bg-secondary', '', 'Aberto', $exame['EXAME']);
      } elseif ($exame['smm_exec'] == 'X') {
        $count['executado']++;
        $executando .= self::getExame('bg-warning', 'text-dark', 'Execução', $exame['EXAME']);
      } elseif ($exame['smm_exec'] == 'I' || $exame['smm_exec'] == 'L') {
        $count['liberado']++;
        $liberados .= self::getExame('bg-success', '', 'Liberado', $exame['EXAME']);
      }
    }

    $examesPage = $solicitados . $executando . $liberados;

    return View::render('monps/modals/quantidade_exames', [
      'type' => $type,
      'type-title' => ucfirst($type),
      'solicitado' => $count['solicitado'],
      'executado' => $count['executado'],
      'liberado' => $count['liberado'],
      'total' => count($exames),
      'exames' => $examesPage
    ]);
  }

  // Método responsável por retornar o modal do paciente
  public static function getModal($request)
  {
    $registro = $request->user->registro;
    $paciente = PacienteModel::getPacienteByRegistro($registro);
    if(!$paciente) return array('success' => false, 'message' => 'Desculpe, infelizmente você não tem acesso a esse serviço.');
    $classificacao = '';
    $explodedArray = explode(';', $paciente->bip);
    $paciente->bip = $explodedArray[0];
    $paciente->minTHospitalar = $explodedArray[1];
    $paciente->tempoHospitalar = $explodedArray[2]; 
    
    if (!empty($paciente->cr)) {
      $classificacao = 'CR';
    } elseif (empty($paciente->cr)) {
      $classificacao = 'SEM CR';
    }
    $examesImg = ExameModel::getExameImgForPaciente($paciente->registro, $paciente->numInternacao);
    $examesLab = ExameModel::getExameLabForPaciente($paciente->registro, $paciente->numInternacao);
    $modal = View::render('paciente/monps/modal', [
      'registro' => $paciente->registro,
      'bip' => $paciente->bip,
      'reavaliacao' => $paciente->reavaliacao == 'S' ? 'Sim' : 'Não',
      'bg-cr' => !empty($paciente->cr) ? 'bg-' . strtolower($paciente->cr) : 'bg-cinza',
      'pac-cr' => $classificacao,
      'medicacao' => $paciente->med,
      'aplicadas' => $paciente->apl,
      'tempo-hospitalar' => $paciente->minTHospitalar,
      'nome' => $paciente->nome,
      'nome-medico' => $paciente->medico,
      'hora-chegada' => $paciente->tempoHospitalar,
      'filas' => self::getFilasPaciente($paciente->registro),
      'lab-collapse' => self::getQtdExames($examesLab, 'lab'),
      'img-collapse' => self::getQtdExames($examesImg, 'img'),
    ]);
    return array(
      'success' => true,
      'modal' => $modal,
    );
  }
}
