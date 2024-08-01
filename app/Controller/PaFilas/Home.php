<?php

namespace App\Controller\PaFilas;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Http\Request;
use App\Model\PaFilas\Adulto;
use App\Model\PaFilas\Pediatria;

class Home extends LayoutPage
{
    // Variável responsável por o conteúdo das filas da maternidade
    // titulo, icones, subtitulo
    const filasMaternidade = [
        'triagem-pediatrica' => [
            'titulo' => 'Triagem',
            'icone' => 'fas fa-briefcase-medical',
            'primeiro-subtitulo' => 'Pediátrica',
            'segundo-subtitulo' => 'Obstetrícia',
            'primeiro-subtitulo-valores' => [
                'primeiro-rotulo' => 'Paciente(s)',
                'segundo-rotulo' => 'Minuto(s)',
            ],
            'segundo-subtitulo-valores' => [
                'primeiro-rotulo' => 'Paciente(s)',
                'segundo-rotulo' => 'Minuto(s)',
            ],
        ],
        'recepcao-pediatrica' => [
            'titulo' => 'Recepção',
            'icone' => 'fas fa-hospital-user',
            'primeiro-subtitulo' => 'Pediátrica',
            'segundo-subtitulo' => 'Obstetrícia',
            'primeiro-subtitulo-valores' => [
                'primeiro-rotulo' => 'Paciente(s)',
                'segundo-rotulo' => 'Minuto(s)',
            ],
            'segundo-subtitulo-valores' => [
                'primeiro-rotulo' => 'Paciente(s)',
                'segundo-rotulo' => 'Minuto(s)',
            ],
        ],
        'consultorio-pediatrica' => [
            'titulo' => 'Consultório Pediátrico',
            'icone' => 'fas fa-user-md',
            'primeiro-subtitulo' => '1º Atendimento',
            'segundo-subtitulo' => 'Reavaliação',
            'primeiro-subtitulo-valores' => [
                'primeiro-rotulo' => 'Paciente(s)',
                'segundo-rotulo' => 'Minuto(s)',
            ],
            'segundo-subtitulo-valores' => [
                'primeiro-rotulo' => 'Paciente(s)',
                'segundo-rotulo' => 'Minuto(s)',
            ],
        ],
        'consultorio-go' => [
            'titulo' => 'Consultório Obstetrícia',
            'icone' => 'fas fa-user-nurse',
            'primeiro-subtitulo' => '1º Atendimento',
            'segundo-subtitulo' => 'Reavaliação',
            'primeiro-subtitulo-valores' => [
                'primeiro-rotulo' => 'Paciente(s)',
                'segundo-rotulo' => 'Minuto(s)',
            ],
            'segundo-subtitulo-valores' => [
                'primeiro-rotulo' => 'Paciente(s)',
                'segundo-rotulo' => 'Minuto(s)',
            ],
        ]
    ];

    // Variável responsável por o conteúdo das filas do hrg
    // titulo, icones, subtitulo
    const filasHRG = [
        'triagem-adulto' => [
            'titulo' => 'Triagem',
            'icone' => 'fas fa-briefcase-medical',
            'primeiro-subtitulo' => '',
            'segundo-subtitulo' => '',
            'primeiro-subtitulo-valores' => [
                'primeiro-rotulo' => 'Paciente(s)',
            ],
            'segundo-subtitulo-valores' => [
                'primeiro-rotulo' => 'Minuto(s)',
            ],
        ],
        'recepcao-adulto' => [
            'titulo' => 'Recepção',
            'icone' => 'fas fa-hospital-user',
            'primeiro-subtitulo' => '',
            'segundo-subtitulo' => '',
            'primeiro-subtitulo-valores' => [
                'primeiro-rotulo' => 'Paciente(s)',
            ],
            'segundo-subtitulo-valores' => [
                'primeiro-rotulo' => 'Minuto(s)',
            ],
        ],
        'clinica-medica' => [
            'titulo' => 'Clínica Médica',
            'icone' => 'fas fa-user-md',
            'primeiro-subtitulo' => '1º Atendimento',
            'segundo-subtitulo' => 'Reavaliação',
            'primeiro-subtitulo-valores' => [
                'primeiro-rotulo' => 'Paciente(s)',
                'segundo-rotulo' => 'Minuto(s)',
            ],
            'segundo-subtitulo-valores' => [
                'primeiro-rotulo' => 'Paciente(s)',
                'segundo-rotulo' => 'Minuto(s)',
            ],
        ],
        'cardiologia' => [
            'titulo' => 'Cardiologia',
            'icone' => 'fas fa-heart-rate',
            'primeiro-subtitulo' => '1º Atendimento',
            'segundo-subtitulo' => 'Reavaliação',
            'primeiro-subtitulo-valores' => [
                'primeiro-rotulo' => 'Paciente(s)',
                'segundo-rotulo' => 'Minuto(s)',
            ],
            'segundo-subtitulo-valores' => [
                'primeiro-rotulo' => 'Paciente(s)',
                'segundo-rotulo' => 'Minuto(s)',
            ],
        ],
        'ortopedia' => [
            'titulo' => 'Ortopedia',
            'icone' => 'fas fa-skeleton',
            'primeiro-subtitulo' => '1º Atendimento',
            'segundo-subtitulo' => 'Reavaliação',
            'primeiro-subtitulo-valores' => [
                'primeiro-rotulo' => 'Paciente(s)',
                'segundo-rotulo' => 'Minuto(s)',
            ],
            'segundo-subtitulo-valores' => [
                'primeiro-rotulo' => 'Paciente(s)',
                'segundo-rotulo' => 'Minuto(s)',
            ],
        ],
    ];

    // Método responsável por buscar nos pacientes pediátricos a
    // quantidade de pacientes e tempo
    private static function getQntPacienteTempo(array $pacientes)
    {
        $quantidadePacientes = 0;
        $tempoEspera = 0;

        foreach ($pacientes as $paciente) {
            $quantidadePacientes += 1;
            $tempoHospitalar = $paciente['tempo_espera_total'];
            if ($tempoHospitalar >  $tempoEspera && $tempoHospitalar < 360)
                $tempoEspera = intval($tempoHospitalar);
        }

        return array(
            'paciente' => $quantidadePacientes,
            'tempo' => $tempoEspera
        );
    }

    // Método responsável por definir o conteúdo da fila adulta separada
    // quando existe subfilas, geralmente (1º atendimento e reavaliação)
    private static function setFilasAdulto(array $primeiraFila, array $segundaFila, array &$fila, string $codigoFila)
    {
        $filaTemporaria = array();
        // Captura os elementos que estão na fila a partir do código dele
        // Armazenando os indexes deles no array
        $indexPacientesAtd = array_keys(array_column($primeiraFila, 'FILA_COD'), $codigoFila);
        for ($i = 0; $i < count($indexPacientesAtd); $i++) {
            // Define o index que está presente o paciente com aquele respectivo código
            $indexPaciente = $indexPacientesAtd[$i];
            // Acrescenta mais um a quantidade de pacientes em atendimento
            $filaTemporaria['pacientes-atendimento']++;
            // Atribui a espera total do paciente a variavel
            $esperaTotal = $primeiraFila[$indexPaciente]['tempo_espera_total'];
            // Checa se a espera total do paciente é a maior da fila
            if ($esperaTotal > $filaTemporaria['tempo-atendimento'] && $esperaTotal < 360)
                $filaTemporaria['tempo-atendimento'] = intval($esperaTotal);
        }

        $indexPacientesReav = array_keys(array_column($segundaFila, 'FILA_COD'), $codigoFila);
        for ($i = 0; $i < count($indexPacientesReav); $i++) {
            $indexPaciente = $indexPacientesReav[$i];
            $filaTemporaria['pacientes-reavaliacao']++;
            $esperaTotal = $segundaFila[$indexPaciente]['tempo_espera_total'];
            if ($esperaTotal > $filaTemporaria['tempo-reavaliacao'] && $esperaTotal < 360)
                $filaTemporaria['tempo-reavaliacao'] = intval($esperaTotal);
        }

        $fila['primeiro-subtitulo-valores']['primeiro-valor'] = $filaTemporaria['pacientes-atendimento'] ?? 0;
        $fila['primeiro-subtitulo-valores']['segundo-valor'] = $filaTemporaria['tempo-atendimento'] ?? 0;
        $fila['segundo-subtitulo-valores']['primeiro-valor'] = $filaTemporaria['pacientes-reavaliacao'] ?? 0;
        $fila['segundo-subtitulo-valores']['segundo-valor'] = $filaTemporaria['tempo-reavaliacao'] ?? 0;
    }

    // Método responsável por definir o conteúdo da fila pediatrica separada
    // quando existe subfilas, geralmente (1º atendimento e reavaliação)
    private static function setFilasFragmentadasPediatria(array $pacientesPrimeiraFila, array $pacientesSegundaFila, array &$fila)
    {
        $dadosPrimeiraFila = self::getQntPacienteTempo($pacientesPrimeiraFila);
        $dadosSegundaFila = self::getQntPacienteTempo($pacientesSegundaFila);

        $fila['primeiro-subtitulo-valores']['primeiro-valor'] = $dadosPrimeiraFila['paciente'] ?? 0;
        $fila['primeiro-subtitulo-valores']['segundo-valor'] = $dadosPrimeiraFila['tempo'] ?? 0;
        $fila['segundo-subtitulo-valores']['primeiro-valor'] = $dadosSegundaFila['paciente'] ?? 0;
        $fila['segundo-subtitulo-valores']['segundo-valor'] = $dadosSegundaFila['tempo'] ?? 0;
    }

    // Método responsável por definir o conteúdo da fila pediatrica
    private static function setFilasPediatria(array $pacientes, array &$fila)
    {
        $filaTemporaria = array();

        foreach ($pacientes as $paciente) {
            $tempoHospitalar = $paciente['tempo_espera_total'];

            if ($paciente['atendimento'] === 'A') {
                $filaTemporaria['paciente-atendimento']++;

                if ($tempoHospitalar > $filaTemporaria['tempo-atendimento'] && $tempoHospitalar < 360)
                    $filaTemporaria['tempo-atendimento'] = intval($tempoHospitalar);
                continue;
            }

            $filaTemporaria['paciente-reavaliacao']++;
            if ($tempoHospitalar > $filaTemporaria['tempo-reavaliacao'] && $tempoHospitalar < 360)
                $filaTemporaria['tempo-reavaliacao'] = intval($tempoHospitalar);
        }

        // Define o conteúdo gerado como valores da fila
        $fila['primeiro-subtitulo-valores']['primeiro-valor'] = $filaTemporaria['paciente-atendimento'] ?? 0;
        $fila['primeiro-subtitulo-valores']['segundo-valor'] = $filaTemporaria['tempo-atendimento'] ?? 0;
        $fila['segundo-subtitulo-valores']['primeiro-valor'] = $filaTemporaria['paciente-reavaliacao'] ?? 0;
        $fila['segundo-subtitulo-valores']['segundo-valor'] = $filaTemporaria['tempo-reavaliacao'] ?? 0;
    }

    // Método responsável por definir o conteúdo da fila da triagem adulta
    private static function setFilasAdultoTriagem(array $pacientes, array &$fila)
    {
        $filaTemporaria = array();
        // Captura os elementos que estão na fila a partir do código dele
        // Armazenando os indexes deles no array
        $indexPacientesAtd = array_keys(array_column($pacientes, 'FILA_COD_CLASSIFICACAO'), '900250');
        for ($i = 0; $i < count($indexPacientesAtd); $i++) {
            // Define o index que está presente o paciente com aquele respectivo código
            $indexPaciente = $indexPacientesAtd[$i];

            // Acrescenta mais um a quantidade de pacientes em atendimento
            if ($pacientes[$indexPaciente]['STATUS_CLASSIFICACAO'] == 'A') {
                $filaTemporaria['pacientes']++;
            }

            // Atribui a espera total do paciente a variavel
            $esperaTotal = $pacientes[$indexPaciente]['ESPERA_CLASSIFICACAO'];
            if ($esperaTotal > $filaTemporaria['tempo'] && $pacientes[$indexPaciente]['STATUS_CLASSIFICACAO'] == 'A') {
                $filaTemporaria['tempo'] = intval($esperaTotal);
            }
        }

        // Define o conteúdo gerado como valores da fila
        $fila['primeiro-subtitulo-valores']['primeiro-valor'] = $filaTemporaria['pacientes'] ?? 0;
        $fila['segundo-subtitulo-valores']['primeiro-valor'] = $filaTemporaria['tempo'] ?? 0;
    }
    // Método responsável por definir o conteúdo da fila da recepção adulta
    private static function setFilasAdultoRecepcao(array $pacientes, array &$fila)
    {
        $filaTemporaria = array();
        // Captura os elementos que estão na fila a partir do código dele
        // Armazenando os indexes deles no array
        $indexPacientesAtd = array_keys(array_column($pacientes, 'FILA_COD_RECEPCAO'), '900197');
        for ($i = 0; $i < count($indexPacientesAtd); $i++) {
            // Define o index que está presente o paciente com aquele respectivo código
            $indexPaciente = $indexPacientesAtd[$i];

            // Acrescenta mais um a quantidade de pacientes em atendimento
            if ($pacientes[$indexPaciente]['STATUS_RECEPCAO'] == 'A') {
                $filaTemporaria['pacientes']++;
            }

            // Atribui a espera total do paciente a variavel
            $esperaTotal = $pacientes[$indexPaciente]['ESPERA_RECEPCAO'];
            if ($esperaTotal > $filaTemporaria['tempo'] && $pacientes[$indexPaciente]['STATUS_RECEPCAO'] == 'A') {
                $filaTemporaria['tempo'] = intval($esperaTotal);
            }
        }

        // Define o conteúdo gerado como valores da fila
        $fila['primeiro-subtitulo-valores']['primeiro-valor'] = $filaTemporaria['pacientes'] ?? 0;
        $fila['segundo-subtitulo-valores']['primeiro-valor'] = $filaTemporaria['tempo'] ?? 0;
    }

    // Método responsável por distribuir os pacientes para as filas
    // preenchendo-as com conteúdo
    private static function getFilasDados(string $tituloFila, array &$fila)
    {
        // Switch que distribuir conteúdos para as filas
        switch ($tituloFila) {
            case "triagem-pediatrica":
                $pacientesPediatria = Pediatria::getTriagemPediatrica();
                $pacientesMaterno = Pediatria::getTriagemMaterna();
                self::setFilasFragmentadasPediatria($pacientesPediatria, $pacientesMaterno, $fila);
                break;
            case "recepcao-pediatrica":
                $pacientesRecepcao = Pediatria::getRecepcaoPediatrica();
                $pacientesMaterno = Pediatria::getRecepcaoMaterna();
                self::setFilasFragmentadasPediatria($pacientesRecepcao, $pacientesMaterno, $fila);
                break;
            case "consultorio-pediatrica":
                $pacientesConsultoriosPed = Pediatria::getConsultorioPediatrico();
                self::setFilasPediatria($pacientesConsultoriosPed, $fila);
                break;
            case "consultorio-go":
                $pacientesCounsultoriosMat = Pediatria::getConsultorioMaterno();
                self::setFilasPediatria($pacientesCounsultoriosMat, $fila);
                break;
            case "triagem-adulto":
                $pacientesAdultoTriagemRecepcao = Adulto::getTriagemRecepcaoAdulto();
                self::setFilasAdultoTriagem($pacientesAdultoTriagemRecepcao, $fila);
                break;
            case "recepcao-adulto":
                $pacientesAdultoTriagemRecepcao = Adulto::getTriagemRecepcaoAdulto();
                self::setFilasAdultoRecepcao($pacientesAdultoTriagemRecepcao, $fila);
                break;
            case "clinica-medica":
                $pacientesAdultoAtd = Adulto::getAtendimentoAdulto();
                $pacientesAdultoReav = Adulto::getReavaliacaoAdulto();
                self::setFilasAdulto($pacientesAdultoAtd, $pacientesAdultoReav, $fila, '900290');
                break;
            case "ortopedia":
                $pacientesAdultoAtd = Adulto::getAtendimentoAdulto();
                $pacientesAdultoReav = Adulto::getReavaliacaoAdulto();
                self::setFilasAdulto($pacientesAdultoAtd, $pacientesAdultoReav, $fila, '900289');
                break;
            case "cardiologia":
                $pacientesAdultoAtd = Adulto::getAtendimentoAdulto();
                $pacientesAdultoReav = Adulto::getReavaliacaoAdulto();
                self::setFilasAdulto($pacientesAdultoAtd, $pacientesAdultoReav, $fila, '900288');
                break;
        }
    }

    // Métódo responsável por retornar os valores da fila
    // (Quantidade de pacientes e tempo máximo na fila)
    private static function getFilaValores(array $filaValores)
    {
        // Verifica se o valor é duplo, se há dois campos de valores
        // Geralmente (1º Atendimento e Reavaliação)
        $valoresDuplo = count($filaValores) > 2;
        return $valoresDuplo ?
            View::render('pafilas/fila_valor_duplo', [
                'primeiro-rotulo' => $filaValores['primeiro-rotulo'],
                'primeiro-valor' => $filaValores['primeiro-valor'],
                'segundo-rotulo' => $filaValores['segundo-rotulo'],
                'segundo-valor' => $filaValores['segundo-valor'],
            ]) :
            View::render('pafilas/fila_valor', [
                'primeiro-rotulo' => $filaValores['primeiro-rotulo'],
                'primeiro-valor' => $filaValores['primeiro-valor'],
            ]);
    }
    // Método responsável por retornar a cor dependendo do tempo da fila
    private static function getCor(int $tempo)
    {
        if ($tempo <= 60) return 'bg-success';
        else if ($tempo > 60 && $tempo < 120) return 'bg-warning';
        else if ($tempo >= 120) return 'bg-danger';
    }
    // Método responsável por retornar a cor da fila com base
    // no maior tempo dos seus valores (atendimento ou reavaliação)
    private static function getFilaCor(array $fila)
    {
        $valoresDuplo = count($fila['primeiro-subtitulo-valores']) > 2;
        $maiorTempo = 0;
        if ($valoresDuplo) {
            $primeiraParteFila = $fila['primeiro-subtitulo-valores'];
            $segundaParteFila = $fila['segundo-subtitulo-valores'];
            $maiorTempo = $primeiraParteFila['segundo-valor'];

            if ($segundaParteFila['segundo-valor'] > $primeiraParteFila['segundo-valor'])
                $maiorTempo = $segundaParteFila['segundo-valor'];

            return self::getCor($maiorTempo);
        }

        $unicaFila = $fila['segundo-subtitulo-valores'];
        return self::getCor($unicaFila['primeiro-valor']);
    }
    // Método responsável por retornar o card collapsado da fila e
    // inserir o conteúdo da fila (titulo, icone, subtitulo)
    private static function getFilaCollapse(array $fila)
    {
        $corCollapse = self::getFilaCor($fila);
        return View::render('pafilas/fila', [
            'titulo' => $fila['titulo'],
            'icone' => $fila['icone'],
            'cor' => $corCollapse,
            'primeiro-subtitulo' => $fila['primeiro-subtitulo'],
            'segundo-subtitulo' => $fila['segundo-subtitulo'],
            'primeiro-valor' => self::getFilaValores($fila['primeiro-subtitulo-valores']),
            'segundo-valor' => self::getFilaValores($fila['segundo-subtitulo-valores']),
        ]);
    }
    public static function getFilas(Request $request = null)
    {
        $filas = '<h5>Maternidade</h5>';
        foreach (self::filasMaternidade as $tituloFila => $fila) {
            self::getFilasDados($tituloFila, $fila);
            $filas .= self::getFilaCollapse($fila);
        }
        $filas .= '<h5>HRG</h5>';
        foreach (self::filasHRG as $tituloFila => $fila) {
            self::getFilasDados($tituloFila, $fila);
            $filas .= self::getFilaCollapse($fila);
        }
        return $filas;
    }
    // Método responsável por retornar a página inicial com as filas
    public static function getHome(Request $request)
    {

        $content = View::render('pafilas/home', [
            'filas' => self::getFilas(),
        ]);
        return parent::getPage('P.A Filas', 'pa-filas', $content, $request);
    }
}
