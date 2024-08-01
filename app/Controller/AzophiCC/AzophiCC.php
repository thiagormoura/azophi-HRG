<?php

namespace App\Controller\AzophiCC;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Model\AzophiCC\AzophiCC as AzophiCCModel;
use DateTime;
use DateTimeZone;

class AzophiCC extends LayoutPage
{

  // Método responsável por retornar os procedimentos por convênio
  private static function getProcedimentosByConvenioDataPoint($procedimentos)
  {
    $dataPoints = array();

    foreach ($procedimentos as $procedimento) {
      $dataPoints[] = array("label" => $procedimento->convenio, "y" => $procedimento->qtd);
    }
    return $dataPoints;
  }

  // Método responsável por retornar os procedimentos por porte
  private static function getProcedimentosByPorteDataPoint($procedimentos)
  {
    $dataPoints = array();

    foreach ($procedimentos as $procedimento) {
      $dataPoints[] = array("label" => $procedimento->porte, "y" => $procedimento->qtd);
    }
    return $dataPoints;
  }

  // Método responsável por retornar o vetor de horários de cirurgia preenchido
  private static function fillArray($cirurgia, &$timesOfDay)
  {
    // Verifica se a cirurgia durou de um dia para o outro
    if ($cirurgia->h_inicio > $cirurgia->h_fim) {
      for ($i = $cirurgia->h_inicio; $i < 24; $i++) {
        if ($cirurgia->m_inicio > 30 && $i == $cirurgia->h_inicio) {
          $timesOfDay[$i][1] += 1;
          continue;
        }
        $timesOfDay[$i][0] += 1;
        $timesOfDay[$i][1] += 1;
      }
      $cirurgia->h_inicio = 00;
      $cirurgia->m_inicio = 00;
    }

    // Verifica se as horas são iguais e checa os minutos
    if ($cirurgia->h_inicio == $cirurgia->h_fim) {
      if ($cirurgia->m_inicio > 30) {
        $timesOfDay[$cirurgia->h_inicio][1] += 1;
      } else {
        $timesOfDay[$cirurgia->h_inicio][0] += 1;
        $timesOfDay[$cirurgia->h_inicio][1] += 1;
      }
      return;
    }

    // Preenche todas as horas até a hora final da cirurgia
    for ($i = $cirurgia->h_inicio; $i <= $cirurgia->h_fim; $i++) {
      if ($i <= $cirurgia->h_fim - 1) {
        if ($cirurgia->m_inicio > 30 && $i == $cirurgia->h_inicio) {
          $timesOfDay[$i][1] += 1;
          continue;
        }
        $timesOfDay[$i][0] += 1;
        $timesOfDay[$i][1] += 1;
      }
    }
    // Verifica se a hora final da cirurgia terminou depois das 30 para preencher todos os campos
    if ($cirurgia->m_inicio > 30) {
      $timesOfDay[$cirurgia->h_fim][0] += 1;
      $timesOfDay[$cirurgia->h_fim][1] += 1;
      return;
    }
    $timesOfDay[$cirurgia->h_fim][0] += 1;
  }

  // Método responsável por a quantidade de cirurgias por horários
  private static function getCirurgiasByHorariosDataPoint($cirurgias)
  {
    $timesOfDay = array_fill(0, 24, array(0 => 0, 1 => 0));

    foreach ($cirurgias as $cirurgia) {
      self::fillArray($cirurgia, $timesOfDay);
    }

    $dataPoints = array();

    foreach ($timesOfDay as $hour => $minutes) {
      $dataPoints[] = array("label" => strlen($hour) == 1 ? '0' . $hour . ":00" : $hour . ":00", "y" => $minutes[0]);
      $dataPoints[] = array("label" => strlen($hour) == 1 ? '0' . $hour . ":30" : $hour . ":30", "y" => $minutes[1]);
    }

    return $dataPoints;
  }

  // Método responsável por retornar as salas mais utilizadas
  private static function getSalasMaisUtilizadasDataPoint($salas)
  {
    $dataPoints = array();
    $salas_utilizadas = array();
    foreach ($salas as $sala) {
      if ($sala->nome === NULL) continue;
      $salas_utilizadas[$sala->nome] += $sala->uso;
    }

    $tempo_total = $salas[0]->tempo;
    foreach ($salas_utilizadas as $nome => $uso) {
      $utilizada = (float) number_format(($uso * 100) / $tempo_total, 2);
      $dataPoints[] = array("label" => $nome, "y" => $utilizada, "name" => $nome);
    }

    return $dataPoints;
  }


  // Método responsável por retprnar a média anual de cirúrgias
  private static function getMediaAnualCirgurgiasDataPoint($cirurgias)
  {
    $media = 0;
    foreach ($cirurgias as $cirurgia) {
      $media += $cirurgia->qtd;
    }

    return round($media / count($cirurgias, 0));
  }

  // Método responsável por a média anual de cirúrgias
  private static function getCirurgiasDataPoint($cirurgias)
  {
    $dataPoints = array();

    foreach ($cirurgias as $cirurgia) {
      $dataPoints[] = array("label" => $cirurgia->mes, "y" => $cirurgia->qtd);
    }

    return $dataPoints;
  }

  // Método responsável por retornar os dados para prenchimentos dos charts
  public static function getDataPoints($request)
  {
    $postVars = $request->getPostVars();
    $convenios = "'" . str_replace(' ', '', $postVars['convenios']) . "'";
    $convenios = str_replace(',', "','", $convenios);

    $data_inicial = $postVars['firstDate'];
    $data_final = $postVars['lastDate'];

    $cirurgiasByMonth = AzophiCCModel::getCirurgiasByMonths($data_inicial, $data_final, $convenios);
    $bestMonthsCirurgias = AzophiCCModel::getBestMonths(5, $data_inicial, $data_final, $convenios);
    $salasMaisUtilizadas = AzophiCCModel::getSalasMaisUtilizadas($data_inicial, $data_final, $convenios);
    $cirurgiasByHorarios = AzophiCCModel::getHorariosCirurgias($data_inicial, $data_final, $convenios);
    $procedimentosByPorte = AzophiCCModel::getProcedimentosByPorte($data_inicial, $data_final, $convenios);
    $procedimentosByConvenio = AzophiCCModel::getProcedimentosByConvenio($data_inicial, $data_final, $convenios);

    return array(
      'cirurgias_mes' => $cirurgiasByMonth ? self::getCirurgiasDataPoint($cirurgiasByMonth) : false,
      'cirurgias_mes_media' => $cirurgiasByMonth ? self::getMediaAnualCirgurgiasDataPoint($cirurgiasByMonth) : false,
      'melhores_meses_cirurgia' => $bestMonthsCirurgias ? self::getCirurgiasDataPoint($bestMonthsCirurgias) : false,
      'salas_mais_utilizadas' => $salasMaisUtilizadas ? self::getSalasMaisUtilizadasDataPoint($salasMaisUtilizadas) : false,
      'cirurgias_horarios' => $cirurgiasByHorarios ? self::getCirurgiasByHorariosDataPoint($cirurgiasByHorarios) : false,
      'procedimentos_porte' => $procedimentosByPorte ? self::getProcedimentosByPorteDataPoint($procedimentosByPorte) : false,
      'procedimento_convenio' => $procedimentosByConvenio ? self::getProcedimentosByConvenioDataPoint($procedimentosByConvenio) : false,
    );
  }

  // Método responsável por retornar os convênios dentro de um range de data
  public static function getConvenios($request)
  {
    $postVars = $request->getPostVars();
    $convenios = AzophiCCModel::getConveniosByDate($postVars['firstDate'], $postVars['lastDate']);
    $optionsConvenio = '';

    foreach ($convenios as $convenio) {
      $optionsConvenio .= View::render('azophicc/options_convenios', [
        'convenio-code' => rtrim($convenio->code),
        'convenio-nome' => $convenio->nome
      ]);
    }

    return View::render('azophicc/select_convenios', [
      'options' => $optionsConvenio
    ]);
  }

  private static function getCirurgiasAgendadas($data_inicial, $data_final)
  {
    $cirurgias = AzophiCCModel::getCirurgiasAgendadas($data_inicial, $data_final);
    $linhas_tabela = '';
    foreach ($cirurgias as $cirurgia) {
      $horario_cor = '';

      switch (true) {
        case $cirurgia->TEMPO <= 0:
          $horario_cor = 'text-primary';
          break;
        case $cirurgia->TEMPO > 0 && $cirurgia->TEMPO <= 20:
          $horario_cor = 'text-danger';
          break;
        case $cirurgia->TEMPO > 20 && $cirurgia->TEMPO <= 45:
          $horario_cor = 'text-warning';
          break;
        case $cirurgia->TEMPO > 45:
          $horario_cor = 'text-success';
          break;
      }

      $fortalezaTimeZone = new DateTimeZone(CURRENT_TIMEZONE);
      $dataCirurgiaInicio = new DateTime($cirurgia->HORA_INICIO, $fortalezaTimeZone);

      $linhas_tabela .= View::render('azophicc/linha_tabela', [
        'horario' => $dataCirurgiaInicio->format('d/m') . '<br>' . $dataCirurgiaInicio->format('H:i'),
        'horario-order' => $dataCirurgiaInicio->getTimestamp(),
        'horario-cor' => $horario_cor,
        'sala' => str_replace('SALA CIR ', '', $cirurgia->SALA),
        'paciente' => $cirurgia->NOME_PACIENTE,
        'convenio' => $cirurgia->CONVENIO,
        'medico' => $cirurgia->MEDICO,
        'procedimento' => $cirurgia->SERVICO_NOME,
        'internacao' => $cirurgia->INTERNACAO,
      ]);
    }

    return $linhas_tabela;
  }

  // Método responsável por retornar os campos da página do AzhopiCC
  public static function getCamposAzophiCC($request)
  {
    $postVars = $request->getPostVars();
    $data_inicial = $postVars['firstDate'];
    $data_final = $postVars['lastDate'];

    $convenios = "'" . str_replace(' ', '', $postVars['convenios']) . "'";
    $convenios = str_replace(',', "','", $convenios);

    return View::render('azophicc/campos_azophicc', [
      'cirurgias-agendadas' => AzophiCCModel::getCountCirurgiasAgendadas($data_inicial, $data_final, $convenios),
      'cirurgias-realizadas' => count(AzophiCCModel::getCirurgiasRealizadas($data_inicial, $data_final, $convenios)),
      'procedimentos' => count(AzophiCCModel::getProcedimentos($data_inicial, $data_final, $convenios)),
      'cirurgias-suspensas' => AzophiCCModel::getCountCirurgiasSuspensas($data_inicial, $data_final)->qtd,
      'pacientes-cirurgiados' => AzophiCCModel::getCountPacientesCirurgiados($data_inicial, $data_final, $convenios)->qtd_pacientes,
      'cirurgias-agendadas-tabela' => self::getCirurgiasAgendadas($data_inicial, $data_final),
      'graficos' =>  $convenios !== "''" ? View::render('azophicc/graficos_azophicc') : View::render('utils/alert', [
        'color' => 'danger',
        'mensagem' => 'Nenhuma cirúrgia registrada durante esse período.',
      ]),
    ]);
  }

  public static function getHome($request)
  {
    $content = View::render('azophicc/home', []);

    return parent::getPage('Azophi - CC', 'azophicc', $content, $request);
  }
}
