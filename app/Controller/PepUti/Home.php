<?php

namespace App\Controller\Peputi;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use \App\Model\PepUti\Paciente as PacienteModel;
use DateTime;
use DateTimeZone;

class Home extends LayoutPage
{
    // Método responsável por retornar a classificação de risco dos pacientes 
    private static function getColorTempoHospitalar($tempoHospitalar)
    {
        if ($tempoHospitalar > 0 && $tempoHospitalar < 20) return 'text-success';
        elseif ($tempoHospitalar > 20 && $tempoHospitalar < 60) return 'text-warning';
        elseif ($tempoHospitalar > 60) return 'text-danger';
    }

    // Método responsável por retornar os pacientes em fila
    private static function getPacientes()
    {
        $pacientes = PacienteModel::getPacientes();
        $pacientePage = '';
        $bgStatusFila = '';
        foreach ($pacientes as $paciente) {
            if($paciente['med'] != $paciente['apl']){
                $reavalicao = $paciente['reavaliacao'] == 'S' ? '(R) ' : '';
                $classificacao = '';
    
                if ($paciente['pac_reg'] != 0 && !empty($paciente['CR'])) {
                    $classificacao = 'CR';
                } elseif (empty($paciente['CR'])) {
                    $classificacao = 'SEM CR';
                }
    
                if ($paciente['fle_status_nome'] == 'Aguardando') {
                    $bgStatusFila = 'bg-amarelo text-dark';
                } else if ($paciente['fle_status_nome'] == 'Em atendimento') {
                    $bgStatusFila = 'bg-verde text-light';
                } else {
                    $bgStatusFila = 'bg-dark text-light';
                }
    
                $fortalezTimezone = new DateTimeZone(CURRENT_TIMEZONE);
                $dataHoraChegada = new DateTime($paciente['FLE_DTHR_CHEGADA'], $fortalezTimezone);
    
                $pacientePage .= View::render('monps/paciente_linha', [
                    'fila' => $paciente['psv_FILA_nome'],
                    'registro' =>  $paciente['pac_reg'] != 0 ? $paciente['pac_reg'] : '',
                    'bip' => $paciente['FLE_BIP'],
                    'tempo-fila' => $dataHoraChegada->format('Y-m-d H:i:s'),
                    'pac-nome' => $reavalicao . ($paciente['pac_reg'] == 0 ? '#' . $paciente['FLE_BIP'] : $paciente['pac_nome']),
                    'bg-status-fila' => $bgStatusFila,
                    'status-fila' => $paciente['fle_status_nome'],
                    'pac-cr' => $classificacao,
                    'bg-cr' => $paciente['pac_reg'] != 0 && !empty($paciente['CR']) ? 'bg-' . strtolower($paciente['CR']) : 'bg-cinza',
                    'preferencial' => substr($paciente['FLE_BIP'], 0, 1) == 'P' ? '' : 'd-none',
                    'pac-tempo-fila' => $paciente['tempo_na_fila'] . ' min',
                    'color-tempo-fila' => self::getColorTempoHospitalar($paciente['tempo_na_fila']),
                    'tempo-hospitalar' => $paciente['tempo_hospitalar'] . ' min',
                    'medicacao' => $paciente['med'],
                    'aplicadas' => $paciente['apl']
                ]);
            }
        }

        return $pacientePage;
    }

    // Método responsável por retornar as filas do paciente
    private static function getFilasPaciente($paciente)
    {
        $linhaFilas = '';
        foreach ($paciente as $fila) {
            $linhaFilas .= View::render('monps/modals/filas', [
                'fila-nome' => $fila['psv_FILA_nome'],
                'fila-status' => $fila['fle_status_nome'],
                'fila-tempo' => $fila['tempo_na_fila'] . ' min',
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

    private static function getPacientesTriagemRecepcao()
    {
        $pacientes_triagem_recepcao = PacienteModel::getPacientesRecepcaoTriagem();
        $paciente_tempo = array();
        foreach ($pacientes_triagem_recepcao as $paciente) {
            if ($paciente['FILA_COD_RECEPCAO'] === NULL) {
                if ($paciente['STATUS_CLASSIFICACAO'] === 'A') {
                    $paciente_tempo[$paciente['FILA_COD_CLASSIFICACAO']]['qtd_paciente']++;
                    if ($paciente['ESPERA_CLASSIFICACAO'] > $paciente_tempo[$paciente['FILA_COD_CLASSIFICACAO']]['tempo']) {
                        $paciente_tempo[$paciente['FILA_COD_CLASSIFICACAO']]['tempo'] = $paciente['ESPERA_CLASSIFICACAO'];
                        $paciente_tempo[$paciente['FILA_COD_CLASSIFICACAO']]['cor'] = self::getCorPacienteFila($paciente_tempo[$paciente['FILA_COD_CLASSIFICACAO']]['tempo']);
                    }
                }
            } else {
                if ($paciente['STATUS_RECEPCAO'] === 'A') {
                    $paciente_tempo[$paciente['FILA_COD_RECEPCAO']]['qtd_paciente']++;
                    if ($paciente['ESPERA_RECEPCAO'] > $paciente_tempo[$paciente['FILA_COD_RECEPCAO']]['tempo']) {
                        $paciente_tempo[$paciente['FILA_COD_RECEPCAO']]['tempo'] = $paciente['ESPERA_RECEPCAO'];
                        $paciente_tempo[$paciente['FILA_COD_RECEPCAO']]['cor'] = self::getCorPacienteFila($paciente_tempo[$paciente['FILA_COD_RECEPCAO']]['tempo']);
                    }
                }
            }
        }
        return $paciente_tempo;
    }

    private static function getCorPacienteFila($tempo)
    {
        if ($tempo <= 60) return '';
        else if ($tempo > 60 && $tempo < 120) return 'bg-warning';
        else if ($tempo >= 120) return 'bg-danger';
    }

    private static function getPacientesPAtendimento()
    {
        $pacientes_primeiro_atendimento = PacienteModel::getPacientesPAtendimento();
        $paciente_tempo = array();
        foreach ($pacientes_primeiro_atendimento as $paciente) {
            $paciente_tempo[$paciente['FILA_COD']]['qtd_paciente']++;
            if ($paciente['tempo_espera_total'] > $paciente_tempo[$paciente['FILA_COD']]['tempo'] && $paciente['tempo_espera_total'] < 360) {
                $paciente_tempo[$paciente['FILA_COD']]['tempo'] = $paciente['tempo_espera_total'];
                $paciente_tempo[$paciente['FILA_COD']]['cor'] = self::getCorPacienteFila($paciente_tempo[$paciente['FILA_COD']]['tempo']);
            }
        }
        return $paciente_tempo;
    }
    private static function getPacientesReavalicao()
    {
        $pacientes_reavaliacao = PacienteModel::getPacientesReavaliacao();
        $paciente_tempo = array();
        foreach ($pacientes_reavaliacao as $paciente) {
            $paciente_tempo[$paciente['FILA_COD']]['qtd_paciente']++;
            if ($paciente['tempo_espera_total'] > $paciente_tempo[$paciente['FILA_COD']]['tempo']) {
                $paciente_tempo[$paciente['FILA_COD']]['tempo'] = $paciente['tempo_espera_total'];
                $paciente_tempo[$paciente['FILA_COD']]['cor'] = self::getCorPacienteFila($paciente_tempo[$paciente['FILA_COD']]['tempo']);
            }
        }
        return $paciente_tempo;
    }

    public static function getPacientesFilas()
    {
        $pacientes_primeiro_atendimento = self::getPacientesPAtendimento();
        $pacientes_reavaliacao = self::getPacientesReavalicao();
        $pacientes_triagem_recepcao = self::getPacientesTriagemRecepcao();
        return View::render('monps/paciente_filas', [
            'paciente-triagem' => $pacientes_triagem_recepcao['900250']['qtd_paciente'] ?? 0,
            'tempo-triagem' => $pacientes_triagem_recepcao['900250']['tempo'] ?? 0,
            'cor-triagem' => $pacientes_triagem_recepcao['900250']['cor'] ?? '',

            'paciente-recepcao' => $pacientes_triagem_recepcao['900197']['qtd_paciente'] ?? 0,
            'tempo-recepcao' => $pacientes_triagem_recepcao['900197']['tempo'] ?? 0,
            'cor-recepcao' => $pacientes_triagem_recepcao['900197']['cor'] ?? '',

            'paciente-clinica' => $pacientes_primeiro_atendimento['900290']['qtd_paciente'] ?? 0,
            'tempo-clinica' =>  $pacientes_primeiro_atendimento['900290']['tempo'] ?? 0,
            'cor-clinica' =>  $pacientes_primeiro_atendimento['900290']['cor'] ?? '',

            're-paciente-clinica' => $pacientes_reavaliacao['900290']['qtd_paciente'] ?? 0,
            're-tempo-clinica' => $pacientes_reavaliacao['900290']['tempo'] ?? 0,
            're-cor-clinica' => $pacientes_reavaliacao['900290']['cor'] ?? '',

            'paciente-cardiologia' => $pacientes_primeiro_atendimento['900288']['qtd_paciente'] ?? 0,
            'tempo-cardiologia' =>  $pacientes_primeiro_atendimento['900288']['tempo'] ?? 0,
            'cor-cardiologia' =>  $pacientes_primeiro_atendimento['900288']['cor'] ?? '',

            're-paciente-cardiologia' => $pacientes_reavaliacao['900288']['qtd_paciente'] ?? 0,
            're-tempo-cardiologia' => $pacientes_reavaliacao['900288']['tempo'] ?? 0,
            're-cor-cardiologia' => $pacientes_reavaliacao['900288']['cor'] ?? '',

            'paciente-ortopedia' => $pacientes_primeiro_atendimento['900289']['qtd_paciente'] ?? 0,
            'tempo-ortopedia' =>  $pacientes_primeiro_atendimento['900289']['tempo'] ?? 0,
            'cor-ortopedia' =>  $pacientes_primeiro_atendimento['900289']['cor'] ?? '',

            're-paciente-ortopedia' => $pacientes_reavaliacao['900289']['qtd_paciente'] ?? 0,
            're-tempo-ortopedia' => $pacientes_reavaliacao['900289']['tempo'] ?? 0,
            're-cor-ortopedia' => $pacientes_reavaliacao['900289']['cor'] ?? '',
        ]);
    }


    // Método responsável por retornar a página inicial 
    public static function getHome($request)
    {
        $content = View::render('peputi/home', [
            'linhas-paciente' => self::getPacientes(),
            'pacientes-filas' => self::getPacientesFilas(),
        ]);

        return parent::getPage('PEP UTI', 'peputi', $content, $request);
    }
}
