<?php

namespace App\Controller\Azophi;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Controller\Utils\Setor;
use App\Http\Request;
use App\Model\Azophi\Atendimento;
use App\Model\Azophi\Convenio;
use App\Model\Azophi\Internacao;
use App\Model\Azophi\Ocupacao;
use App\Model\Azophi\Paciente;
use DateInterval;
use DateTime;
use DateTimeZone;
use IntlDateFormatter;

class Home extends LayoutPage
{
    // Variável responsável por definir quantos dias de ocupação serão exibidos
    const diasOcupacao = 7;
    // Variável responsável por definir os setores que serão buscados no filtro
    const convenios = "'110','111','112','113','114','150','200','201','202','01','011','02','03','032','033','035','036','037','038','039','041','042','043','044','045','046','048','049','05','050','051','053','058','06','061','064','066','068','069','070','079','08','083','084','09','091','092','094','1','10','11','12','13','15','16','19','2','21','23','28','30','32','33','34','35','36','37','38','39','4','41','42','43','44','45','46','48','49','5','51','52','54','55','56','59','6','60','61','62','63','64','65','66','67','7','70','73','74','75','76','78','8','80','81','86','87','88','9','90','92','93','94','95','97','BDS','DZ','E7','E9','EJ','EL','EP','I37','INC','NHC'";

    // Método responsável por retornar os datapoints do gráficos da quantidade pacientes
    // nos últimos sete dias da semana no pronto atendimento 
    private static function getPacientesPa(array $pacientes, string $tipo)
    {
        $data = array();
        $pacientesEspec = array();

        foreach ($pacientes as $paciente) {
            $especialidade = $paciente['CONSULTA'];

            $date = new DateTime($paciente['DATA']);
            $formatter = new IntlDateFormatter('pt_BR', IntlDateFormatter::MEDIUM, IntlDateFormatter::MEDIUM);
            $formatter->setPattern('eee');
            $label = str_replace('.', '', ucfirst($formatter->format($date)));

            $pacientesEspec[$especialidade][$label] += $paciente['QUANTIDADE'];
        }

        $dataPointsTotal = [];

        foreach ($pacientesEspec as $especialidade => $values) {
            $dataPoints = [];

            foreach ($values as $date => $quantidade) {
                $dataPointsTotal[$date] = array(
                    'label' => $date,
                    'y' => $dataPointsTotal[$date]['y'] + $quantidade
                );

                $dataPoints[] = array(
                    'label' => $date,
                    'y' => $quantidade,
                );
            }

            $data[] = array(
                'type' => 'line',
                'name' => $especialidade,
                'visible' => true,
                'lineThickness' => 1.8,
                'lineDashType' => "dash",
                'showInLegend' => true,
                'dataPoints' => $dataPoints
            );
        }

        $dataPointsTotal = array_values($dataPointsTotal);

        $data[] = array(
            'type' => 'line',
            'visible' => true,
            'name' => strtoupper($tipo) === 'HRG' ? 'Total HRG' : 'Total Maternidade',
            'showInLegend' => true,
            'dataPoints' => $dataPointsTotal,
        );

        return $data;
    }

    // Método responsável por retornar os datapoints do gráfico de internação convênio
    private static function getInternacaoConvenios($convenios)
    {
        $internacaoConvenios = Internacao::getInternacaoConvenios($convenios);
        $dataPoints = array();
        $arrayTemporario = array();
        $totalPacientes = 0;

        foreach ($internacaoConvenios as $convenio) {

            if($convenio['TIPO'] == "BER")
                continue;

            // Array temporário armazena no index do nome do convênio a quantidade total de internações
            // (Ex. 'INCOR/SUS' => 100)
            $arrayTemporario[$convenio['NOME']]['QUANTIDADE'] += $convenio['QUANTIDADE'];
            // Array temporário armazena no index tipo os tipos daquele determinado convênio 
            //e a quantidade de internações que esse tipo possui 
            // (Ex. 'INCOR/SUS' => 'TIPO' => ['A':100, 'U': 100])
            $arrayTemporario[$convenio['NOME']]['TIPO'][$convenio['TIPO']] = $convenio['QUANTIDADE'];
            $totalPacientes += intval($convenio['QUANTIDADE']);
        }


        foreach ($arrayTemporario as $convenio => $valor) {
            $porcentagem = number_format((intval($valor['QUANTIDADE']) / $totalPacientes) * 100, 1);
            $dataPoints[] = array("name" => $convenio, "label" => $porcentagem, "y" => $valor['QUANTIDADE'], "tipo" => $valor['TIPO']);
        }
        return $dataPoints;
    }

    // Método responsável por retornar os datapoints do gráfico dos últimos 7 dias
    // exibindo a quantidade de pacientes por convênio
    private static function getPacientes7DiasConvenios($convenios)
    {
        $pacientesConvenios = Paciente::getPacientesUltimos7DiasConvenios($convenios);
        $dataPoints = array();
        $arrayTemporario = array();
        $totalPacientes = 0;

        foreach ($pacientesConvenios as $paciente) {
            // Array temporário armazena no index do nome do convênio a quantidade 
            // total de pacientes que estão aqui a mais de 7 dias
            // (Ex. 'INCOR/SUS' => 100)
            $arrayTemporario[$paciente['CONVENIO']]['QUANTIDADE'] += $paciente['QUANTIDADE'];
            $totalPacientes += intval($paciente['QUANTIDADE']);
        }
        foreach ($arrayTemporario as $paciente => $valor) {
            $porcentagem = number_format((intval($valor['QUANTIDADE']) / $totalPacientes) * 100, 1);
            $dataPoints[] = array("name" => $paciente, "label" => $porcentagem, "y" => $valor['QUANTIDADE']);
        }
        return $dataPoints;
    }

    // Método responsável por retornar os convênios dos pacientes do pronto atendimento
    private static function getConveniosPacientes(array $convenios)
    {
        $dataPoints = array();

        foreach ($convenios as $convenio) {
            $dataPoints[] = array("name" => $convenio['NOME'], "label" => $convenio['NOME'], "y" => $convenio['QUANTIDADE']);
        }

        return $dataPoints;
    }

    // Método responsável por retornar o pico de pacientes entre as ocupações passadas (hrg e maternidade)
    private static function getOcupacaoGeralPico(array $ocupacoes, int $pacientesAtuais): int
    {
        $atual = $pacientesAtuais;
        // Define o maior como a quantidade atual de pacientes
        $maior = $pacientesAtuais;
        // Ideia principal: percorrer o vetor e ir incremetando e decrementando
        // a quantidade de pacientes que receberam alta e admissão, respectivamente
        foreach ($ocupacoes as $ocupacao) {
            // Caso seja alta reduzimos um paciente para verificarmos quantos pacientes tinhamos antes da admissão dele
            if ($ocupacao['status'] == 'ADMISSAO')
                $atual = $atual - 1;
            // Caso seja alta acrescentamos um paciente para verificarmos quantos pacientes tinhamos antes da alta dele
            else
                $atual = $atual + 1;
            // Verifica o maior valor e armazena
            if ($atual >= $maior)
                $maior = $atual;
        }
        return $maior;
    }

    // Método responsável por retornar o gráfico de ocupação geral
    private static function getOcupacaoGeral($conveniosFilter = null)
    {
        $dataPointOcupacao = array();
        $dataPointPico = array();

        // Percorremos os dias que queremos percorrer
        for ($i = self::diasOcupacao; $i >= 0; $i--) {
            // Buscamos no banco a quantidade de pacientes a partir dos dias passados
            // a frente ou antes de hoje
            // Exemplo: diasOcupacao = 7, pegamos os pacientes de 7 dias atrás até agora
            $ocupacaoAtual = Ocupacao::getPacientesOcupacao(-$i, empty($conveniosFilter) ? null : $conveniosFilter);
            $ocupacoesHoje = Ocupacao::getEntradaESaidaPacientes(-$i, empty($conveniosFilter) ? null : $conveniosFilter);
            $pico = self::getOcupacaoGeralPico($ocupacoesHoje, $ocupacaoAtual['PACIENTES']);
            // Transforma a data no dia da semana, exemplo: Seg., Ter.
            $dataOcupacao = new DateTime($ocupacaoAtual['DIA']);
            $formatter = new IntlDateFormatter('pt_BR', IntlDateFormatter::MEDIUM, IntlDateFormatter::MEDIUM);
            $formatter->setPattern('eee');
            // Trata os dados para o padrão do CanvasJs
            $label = str_replace('.', '', ucfirst($formatter->format($dataOcupacao)));
            $dataPointOcupacao[] = array("label" => $label, "y" => intval($ocupacaoAtual['PACIENTES']));
            $dataPointPico[] = array("label" => $label, "y" => $pico);
        }

        return array(
            'pacientes-atual' => $dataPointOcupacao,
            'pacientes-pico' => $dataPointPico,
        );
    }
    private static function getAltaAdmissaoAndTurno($altasAdmissoes, $altaAdmissao, $timezone, $formatter)
    {
        $dia = $altaAdmissao['DIA'];
        $diaAlta = new DateTime($altaAdmissao['DIA'], $timezone);

        $label = str_replace('.', '', ucfirst($formatter->format($diaAlta)));

        $altasPorTurno = array_filter($altasAdmissoes, function ($altaAdmissao) use ($dia) {
            return $altaAdmissao['DIA'] == $dia;
        });

        $altasPorTurno = array_values($altasPorTurno);
        usort($altasPorTurno, function ($a, $b) {
            return $a['TURNO'] > $b['TURNO'];
        });
        $quantidades = array_column($altasPorTurno, 'QUANTIDADE');
        $totalAltas = array_sum($quantidades);

        return array(
            "label" => $label,
            "data" => $diaAlta->format('Y-m-d'),
            "turnos" => array(
                'Manha' => (int) $quantidades[0] ?? 0,
                'Tarde' => (int) $quantidades[2] ?? 0,
                'Noite' => (int) $quantidades[1] ?? 0,
            ),
            "y" => $totalAltas
        );
    }
    private static function getAltasAdmissaoUltimosDias($dias, $conveniosFilter = null)
    {
        $altas = Internacao::getAltasUltimosDias($dias, empty($conveniosFilter) ? null : $conveniosFilter);
        $admissoes = Internacao::getAdmissaoUltimosDias($dias, empty($conveniosFilter) ? null : $conveniosFilter);

        $dataFormatadaAltas = array();
        $dataFormatadaAdmissao = array();
        $dataPoints = array();

        $fortalezaTimeZone = new DateTimeZone('America/Fortaleza');
        $formatter = new IntlDateFormatter('pt_BR', IntlDateFormatter::MEDIUM, IntlDateFormatter::MEDIUM);
        $formatter->setPattern('eee');

        $lastDay = null;

        foreach ($altas as $alta) {
            if ($lastDay == $alta['DIA'])
                continue;

            $dataFormatadaAltas[] = self::getAltaAdmissaoAndTurno($altas, $alta, $fortalezaTimeZone, $formatter);
            $lastDay = $alta['DIA'];
        }

        $lastDay = null;

        foreach ($admissoes as $admissao) {
            if ($lastDay == $admissao['DIA'])
                continue;

            $dataFormatadaAdmissao[] = self::getAltaAdmissaoAndTurno($admissoes, $admissao, $fortalezaTimeZone, $formatter);
            $lastDay = $admissao['DIA'];
        }

        $dataPoints['altas'] = $dataFormatadaAltas;
        $dataPoints['admissoes'] = $dataFormatadaAdmissao;


        $data_inexistentes_in_altas = array_diff(array_column($dataPoints['altas'], "data"), array_column($dataPoints['admissoes'], "data"));
        $data_inexistentes_in_admissao = array_diff(array_column($dataPoints['admissoes'], "data"), array_column($dataPoints['altas'], "data"));

        $data_inexistentes = array_merge($data_inexistentes_in_altas, $data_inexistentes_in_admissao);

        $array_day_of_week = ["Dom", "Seg", 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'];

        if(!empty($data_inexistentes)){
            foreach ($data_inexistentes as $data) {
                if(array_search($data, array_column($dataPoints['altas'], "data")) === false){
                    $dataPoints['altas'][] = array(
                        "label" => $array_day_of_week[(new DateTime($data))->format('w')],
                        "data" => $data,
                        "turnos" => array(
                            'Manha' => 0,
                            'Tarde' => 0,
                            'Noite' =>0,
                        ),
                        "y" => 0
                    );
                }

                if(array_search($data, array_column($dataPoints['admissoes'], "data")) === false){
                    $dataPoints['admissoes'][] = array(
                        "label" => $array_day_of_week[(new DateTime($data))->format('w')],
                        "data" => $data,
                        "turnos" => array(
                            'Manha' => 0,
                            'Tarde' => 0,
                            'Noite' => 0,
                        ),
                        "y" => 0
                    );
                }
            }
        }

        usort($dataPoints['admissoes'], function ($a, $b) {
            return new DateTime($a['data']) > new DateTime($b['data']);
        });

        usort($dataPoints['altas'], function ($a, $b) {
            return new DateTime($a['data']) > new DateTime($b['data']);
        });

        return $dataPoints;
    }
    // Método responsável por retornar os datapoints dos gráficos para a página principal
    public static function getDataPoints(Request $request, $isAsync = false)
    {
        $resultado = [];

        $postVars = $request->getPostVars();

        $convenios = self::convenios;

        if (!empty($postVars)) {
            $convenios = "'" . str_replace(' ', '', $postVars['convenios']) . "'";
            $convenios = str_replace(',', "','", $convenios);
        }


        $conveniosHrgHoje = Atendimento::getPAHrgConvenios(0, $convenios);
        $conveniosHrgOntem = Atendimento::getPAHrgConvenios(-1, $convenios);
        $conveniosMaternidadeHoje = Atendimento::getPAMaternidadeConvenios(0, $convenios);
        $conveniosMaternidadeOntem = Atendimento::getPAMaternidadeConvenios(-1, $convenios);

        $pacientesPAHrg = Atendimento::getPAHrgAtendimento(-6, 0, $convenios);
        $pacientesPAMaternidade = Atendimento::getPAMaternidadeAtendimento(-6, 0, $convenios);

        $resultado['graficos'] = [
            'internacaoConvenios' => self::getInternacaoConvenios($convenios),
            'pacientesConvenios' => self::getPacientes7DiasConvenios($convenios),
            'ocupacaoGeral' => self::getOcupacaoGeral(),
            'prontoAtendimentoConvenios' => [
                'pa-hrg-hoje' => self::getConveniosPacientes($conveniosHrgHoje),
                'pa-hrg-ontem' => self::getConveniosPacientes($conveniosHrgOntem),
                'pa-maternidade-hoje' => self::getConveniosPacientes($conveniosMaternidadeHoje),
                'pa-maternidade-ontem' => self::getConveniosPacientes($conveniosMaternidadeOntem)
            ],
            'prontoAtendimentoPacientes' => [
                'pa-hrg' => self::getPacientesPa($pacientesPAHrg, 'hrg'),
                'pa-maternidade' => self::getPacientesPa($pacientesPAMaternidade, 'maternidade'),
            ],
            'altaAdmissao' => self::getAltasAdmissaoUltimosDias(6, $convenios),
        ];

        if($isAsync)
            $resultado['cards'] = self::reloadPage($convenios);

        return $resultado;

    }
    // Método responsável por retornar a cor da ocupação dependendo da porcentagem
    private static function getOcupacaoCor(float $ocupacaoPorcentagem)
    {
        if ($ocupacaoPorcentagem <= 50)
            return 'bg-success';
        else if ($ocupacaoPorcentagem > 50 && $ocupacaoPorcentagem <= 75)
            return 'bg-warning';
        else if ($ocupacaoPorcentagem > 75) return 'bg-danger';
    }

    // Método responsável por retornar o padrão da tabela de internação
    private static function getInternacaoTabela(array $internacoes, array $isolamentos, array &$cards, $preserveLeitos = false)
    {
        $linhaTabela = '';
        $total = array();

        foreach ($internacoes as $internacao) {
            $leitosIsolados = array_keys(array_column($isolamentos, 'STR_COD'), $internacao['STR_COD']);
            $internacao['ISOLAMENTO'] = count($leitosIsolados);

            // Vagas = Total de leitos - (Total de leitos ocupados + Total de leitos reservados)
            $vagas = $preserveLeitos ? $internacao['LEITOS'] - ($internacao['PACIENTES_TOTAL'] + $internacao['RESERVA_TOTAL']) 
                : $internacao['LEITOS'] - ($internacao['PACIENTES'] + $internacao['RESERVA']);

            // Porcentagem de ocupação
            $ocupacaoPorcentagem = 0;

            if ($internacao['LEITOS'] > 0)
                $ocupacaoPorcentagem = number_format(($internacao['PACIENTES'] / $internacao['LEITOS']) * 100, 0);

            //Verifica se o setor é o terceiro andar da maternidade e busca os recem nascidos 
            $recemNascidos = '';
            $quantidadeRNascidos = 0;
            if ($internacao['STR_COD'] === 'UM3') {
                $recemNascidos = Internacao::getRecemNascidos();
                $quantidadeRNascidos = $recemNascidos;
                $recemNascidos = View::render('azophi/quantidade_recem_nascidos', [
                    'recem-nascidos' => $quantidadeRNascidos
                ]);
            }

            $linhaTabela .= View::render('azophi/tabela/tabela_internacao', [
                'negrito' => '',
                'setor' => "<button class='btn btn-link p-0 modal-setor-paciente' data-setor='" . $internacao['STR_COD'] . "'><i class='fas fa-info-circle me-1'></i> " . $internacao['SETOR'] . "</button>",
                'leitos' => $internacao['LEITOS'] + $internacao['ISOLAMENTO'],
                'isolamento' => $internacao['ISOLAMENTO'],
                'pacientes' => $internacao['PACIENTES'] . $recemNascidos,
                'reservados' => $internacao['RESERVA'],
                'vagas' => $vagas >= 0 ? $vagas : 0,
                'ocupacao' => $ocupacaoPorcentagem,
                'cor-ocupacao' => self::getOcupacaoCor($ocupacaoPorcentagem)
            ]);

            // Soma total dos elementos para exibição dos dados gerais

            $total['total-recem-nascido'] += $quantidadeRNascidos;
            $total['total-leitos'] += $internacao['LEITOS'] + $internacao['ISOLAMENTO'];
            $total['total-pacientes'] += $internacao['PACIENTES'];
            $total['total-reservados'] += $internacao['RESERVA'];
            $total['total-isolamentos'] += $internacao['ISOLAMENTO'];
            $total['total-vagas'] += $vagas;
        }

        // Porcentagem de ocupação
        $ocupacaoPorcentagemTotal = 0;
        if ($total['total-leitos'])
            $ocupacaoPorcentagemTotal = number_format(($total['total-pacientes'] / $total['total-leitos']) * 100, 0);

        $cards['leitos'] = $total['total-leitos'];
        $cards['pacientes'] = $total['total-pacientes'];
        $cards['recem-nascidos'] = $total['total-recem-nascido'];
        $cards['ocupacao'] = $ocupacaoPorcentagemTotal;

        $recemNascidos = View::render('azophi/quantidade_recem_nascidos', [
            'recem-nascidos' => $total['total-recem-nascido']
        ]);

        $pacientesTotal = $total['total-recem-nascido'] > 0 ?
            $total['total-pacientes'] . $recemNascidos : $total['total-pacientes'];

        $linhaTabela .= View::render('azophi/tabela/tabela_internacao', [
            'negrito' => 'fw-bold',
            'setor' => 'GERAL',
            'leitos' =>  $total['total-leitos'],
            'isolamento' =>  $total['total-isolamentos'],
            'pacientes' =>  $pacientesTotal,
            'reservados' =>  $total['total-reservados'],
            'vagas' =>  $total['total-vagas'],
            'ocupacao' => number_format($ocupacaoPorcentagemTotal, 0),
            'cor-ocupacao' => self::getOcupacaoCor($ocupacaoPorcentagemTotal)
        ]);

        return $linhaTabela;
    }

    // Método responsável por retornar o padrão da tabela de internação do dia anterior
    private static function getInternacaoTabelaOntem($internacoes)
    {
        $linhaTabela = '';
        $total = array();
        foreach ($internacoes as $internacao) {
            // Vagas = Total de leitos - (Total de leitos ocupados + Total de leitos reservados)
            $vagas = $internacao['LEITOS'] - ($internacao['PACIENTES'] + $internacao['RESERVA']);

            // Porcentagem de ocupação
            $ocupacaoPorcentagem = 0;
            if ($internacao['LEITOS'] > 0)
                $ocupacaoPorcentagem = number_format(($internacao['PACIENTES'] / $internacao['LEITOS']) * 100, 0);

            $linhaTabela .= View::render('azophi/tabela/tabela_internacao_ontem', [
                'negrito' => '',
                'setor' => $internacao['SETOR'],
                'leitos' => $internacao['LEITOS'],
                'pacientes' => $internacao['PACIENTES'],
                'reservados' => $internacao['RESERVA'],
                'vagas' => $vagas >= 0 ? $vagas : 0,
                'ocupacao' => $ocupacaoPorcentagem,
                'cor-ocupacao' => self::getOcupacaoCor($ocupacaoPorcentagem)
            ]);

            // Soma total dos elementos para exibição dos dados gerais
            $total['total-leitos'] += $internacao['LEITOS'];
            $total['total-pacientes'] += $internacao['PACIENTES'];
            $total['total-reservados'] += $internacao['RESERVA'];
            $total['total-vagas'] += $vagas;
        }

        // Porcentagem de ocupação
        $ocupacaoPorcentagemTotal = 0;
        if ($total['total-leitos'])
            $ocupacaoPorcentagemTotal = number_format(($total['total-pacientes'] / $total['total-leitos']) * 100, 0);

        $linhaTabela .= View::render('azophi/tabela/tabela_internacao_ontem', [
            'negrito' => 'fw-bold',
            'setor' => 'GERAL',
            'leitos' =>  $total['total-leitos'],
            'isolamento' =>  $total['total-isolamentos'],
            'pacientes' =>  $total['total-pacientes'],
            'reservados' =>  $total['total-reservados'],
            'vagas' =>  $total['total-vagas'],
            'ocupacao' => number_format($ocupacaoPorcentagemTotal, 0),
            'cor-ocupacao' => self::getOcupacaoCor($ocupacaoPorcentagemTotal)
        ]);

        return $linhaTabela;
    }

    // Método responsável por retornar os cards do topo da página
    private static function getCards($cards)
    {
        $totalLeitos = $cards['hrg']['leitos'] + $cards['maternidade']['leitos'];
        $totalPacientes = $cards['hrg']['pacientes'] + $cards['maternidade']['pacientes'];
        $totalRecemNascidos = $cards['hrg']['recem-nascidos'] + $cards['maternidade']['recem-nascidos'];
        $totalOcupacao = number_format(($totalPacientes / $totalLeitos) * 100, 0);
        $altas24h = Internacao::getAltas24h();
        $altas12h = Internacao::getAltas12h();

        return View::render('azophi/cards', [
            'hrg-leitos' => $cards['hrg']['leitos'],
            'maternidade-leitos' => $cards['maternidade']['leitos'],
            'hrg-pacientes' => $cards['hrg']['pacientes'],
            'maternidade-pacientes' => $cards['maternidade']['pacientes'],
            'hrg-ocupacao' => $cards['hrg']['ocupacao'],
            'maternidade-ocupacao' => $cards['maternidade']['ocupacao'],
            'maternidade-recem-nascidos' => $cards['maternidade']['recem-nascidos'],
            'total-leitos' => $totalLeitos,
            'total-pacientes' => $totalPacientes,
            'total-ocupacao' => $totalOcupacao,
            'altas-24h' => $altas24h,
            'altas-12h' => $altas12h,
        ]);
    }
    // Método responsável por retornar a tabela de pacientes dos últimos 7 dias
    private static function getTabelaPaciente7Dias(array $pacientes)
    {
        $linhaTabela = '';
        $pacienteTotal = 0;
        foreach ($pacientes as $paciente) {
            $pacienteTotal += intval($paciente['QUANTIDADE']);
            $linhaTabela .= View::render('azophi/tabela/tabela_pacientes_7dias', [
                'negrito' => '',
                'setor' => $paciente['SETOR'],
                'pacientes' => $paciente['QUANTIDADE'],
            ]);
        }

        $linhaTabela .= View::render('azophi/tabela/tabela_pacientes_7dias', [
            'negrito' => 'fw-bold',
            'setor' => 'GERAL',
            'pacientes' => $pacienteTotal,
        ]);

        return $linhaTabela;
    }

    // Método responsável por retornar os cards do pronto atendimento exibindo a quantidade
    // de atendimentos nas filas cardiologia, clinico geral, ortopedia e todos
    private static function getPACardFooterHrg(array $atendimentos)
    {
        $total = array_sum(array_column($atendimentos, 'QUANTIDADE'));

        $clinicoIndex = array_search('Clinico Geral', array_column($atendimentos, 'CONSULTA'));
        $cardiologiaIndex = array_search('Cardiologia', array_column($atendimentos, 'CONSULTA'));
        $ortopediaIndex = array_search('Ortopedia', array_column($atendimentos, 'CONSULTA'));

        return View::render('azophi/cards/pronto_atendimento_footer_hrg', [
            'cardiologia' => $cardiologiaIndex !== false ? $atendimentos[$cardiologiaIndex]['QUANTIDADE'] : 0,
            'clinico-geral' => $clinicoIndex !== false ? $atendimentos[$clinicoIndex]['QUANTIDADE'] : 0,
            'ortopedia' => $ortopediaIndex !== false ? $atendimentos[$ortopediaIndex]['QUANTIDADE'] : 0,
            'total' => $total ?? 0,
        ]);
    }

    // Método responsável por retornar os cards do pronto atendimento exibindo a quantidade
    // de atendimentos nas filas cardiologia, clinico geral, ortopedia e todos
    private static function getPACardFooterMat(array $atendimentos)
    {
        $total = array_sum(array_column($atendimentos, 'QUANTIDADE'));

        $pediatriaIndex = array_search('Pediatria', array_column($atendimentos, 'CONSULTA'));
        $obstetriciaIndex = array_search('Obstetricia', array_column($atendimentos, 'CONSULTA'));
        $outrosIndex = array_search('Outros Atend.', array_column($atendimentos, 'CONSULTA'));

        return View::render('azophi/cards/pronto_atendimento_footer_mat', [
            'obstetricia' => $obstetriciaIndex !== false ? $atendimentos[$obstetriciaIndex]['QUANTIDADE'] : 0,
            'pediatria' => $pediatriaIndex !== false ? $atendimentos[$pediatriaIndex]['QUANTIDADE'] : 0,
            'outros' => $outrosIndex !== false ? $atendimentos[$outrosIndex]['QUANTIDADE'] : 0,
            'total' => $total ?? 0,
        ]);
    }

    // Método responsável por retornar as opções do select para filtrar busca em determinados
    // gráficos e elementos da página
    private static function getConvenioOptions()
    {
        $options = '';
        $convenios = Convenio::getConvenios();

        foreach ($convenios as $convenio) {
            $options .= View::render('utils/option', [
                'id' => $convenio['code'],
                'nome' => $convenio['nome'],
            ]);
        }

        return $options;
    }

    // Método responsável por retornar a página inicial do azhopi
    public static function getHome(Request $request)
    {
        $cards = ['hrg' => array(), 'maternidade' => array()];

        $fortalezaTimeZone = new DateTimeZone(CURRENT_TIMEZONE);
        $obData = new DateTime('now', $fortalezaTimeZone);
        $hojeData = $obData->format('Y-m-d H:i:s');
        $obData->sub(new DateInterval('P1D'));
        $ontemData = $obData->format('Y-m-d');

        $internacoesHrgHoje = Internacao::getInternacaoHrgHoje($hojeData);
        $internacoesMaternidadeHoje = Internacao::getInternacaoMaternidadeHoje($hojeData);

        $isolamentosHrg = Internacao::getIsolamentoHrgHoje();
        $isolamentosMaternidade = Internacao::getIsolamentoMaternidadeHoje();

        $internacoesHrgOntem = Internacao::getInternacaoHrgHoje($ontemData);
        $internacoesMaternidadeOntem = Internacao::getInternacaoMaternidadeHoje($ontemData);

        $pacientes7Dias = Paciente::getPacientesUltimos7Dias();

        $paHrgHoje = Atendimento::getPAHrgAtendimento(0, 0);
        $paHrgOntem = Atendimento::getPAHrgAtendimento(-1, -1);

        $paMaternidadeHoje = Atendimento::getPAMaternidadeAtendimento(0, 0);
        $paMaternidadeOntem = Atendimento::getPAMaternidadeAtendimento(-1, -1);

        $content = View::render('azophi/home', [
            'internacao-hrg' => self::getInternacaoTabela($internacoesHrgHoje, $isolamentosHrg, $cards['hrg']),
            'internacao-hrg-ontem' => self::getInternacaoTabelaOntem($internacoesHrgOntem),
            'internacao-maternidade' => self::getInternacaoTabela($internacoesMaternidadeHoje, $isolamentosMaternidade, $cards['maternidade']),
            'internacao-maternidade-ontem' => self::getInternacaoTabelaOntem($internacoesMaternidadeOntem),
            'pacientes-7dias' => self::getTabelaPaciente7Dias($pacientes7Dias),
            'cards' => self::getCards($cards),
            'pa-hrg-hoje' => self::getPACardFooterHrg($paHrgHoje),
            'pa-hrg-ontem' => self::getPACardFooterHrg($paHrgOntem),
            'pa-maternidade-hoje' => self::getPACardFooterMat($paMaternidadeHoje),
            'pa-maternidade-ontem' => self::getPACardFooterMat($paMaternidadeOntem),
            'options' => self::getConvenioOptions(),
        ]);

        return parent::getPage('Azophi', 'azophi', $content, $request);
    }

    private static function getHospitalBedStatus(array $patient)
    {
        $bedHospitalStatus = '';
        
        // var_dump($patient['leito_status']);
        switch ($patient['leito_status']) {
            case 'B':
                $bedHospitalStatus =  "<span class='text-danger'><i class='fas fa-ban'></i> " . $patient['leito_bloqueado'] . "</span>";
                break;
            case 'L':
                $bedHospitalStatus = "<span class='text-success'><i class='fas fa-check'></i> Leito livre </span>";
                break;
            default:
                $bedHospitalStatus = '';
                break;
        }
        return $bedHospitalStatus;
    }

    private static function getBabyHospitalBed($patient, $newbornPatient, $bedHospitalStatus)
    {
        return View::render('azophi/modal/paciente_internado_bebe', [
            'leito' => $patient['leito'],
            'idade' => $patient['idade'] ? $patient['idade'] . ' ano(s)' : '',
            'convenio' => $patient['convenio'],
            'dias-internados' => $patient['dias_internados'] ? 'Dia(s): ' . $patient['dias_internados'] : '',
            'nome' => $patient['paciente_nome'] ?? $bedHospitalStatus,
            'medico' => $patient['medico_responsavel'] ? 'Dr. ' . $patient['medico_responsavel'] : '',
            'nome-bebe' => $newbornPatient['paciente_nome'],
            'bg-bebe' => str_contains($newbornPatient['paciente_nome'], 'RN M') ? 'bg-azul-bebe' : 'bg-rosa-bebe',
        ]);
    }

    private static function getPatientLineModal(array $patients)
    {
        $lines = '';
        $newborns = array_filter($patients, function ($patient) {

            return str_contains(strtolower($patient['leito']), 'berço') === true
                and $patient['leito_id'] === NULL and $patient['paciente_nome'] !== null;
        });

        $newbornsPatients = array();
        foreach ($newborns as $newborn) {
            $bedHospitalName = trim(substr(strtolower($newborn['leito']), 0, 5));
            $newbornsPatients[$bedHospitalName] = $newborn;
        }

        foreach ($patients as $patient) {

            if (str_contains(strtolower($patient['leito']), 'berço')) {
                unset($patient);
                continue;
            }

            $newbornPatient = $newbornsPatients[trim(substr(strtolower($patient['leito']), 0, 5))];
            $bedHospitalStatus = self::getHospitalBedStatus($patient);

            if ($newbornPatient) {
                $lines .= self::getBabyHospitalBed($patient, $newbornPatient, $bedHospitalStatus);
                continue;
            }

            $lines .= View::render('azophi/modal/paciente_internado', [
                'leito' => $patient['leito'],
                'idade' => $patient['idade'] ? $patient['idade'] . ' ano(s)' : '',
                'convenio' => $patient['convenio'],
                'dias-internados' => $patient['dias_internados'] ? 'Dia(s): ' . $patient['dias_internados'] : '',
                'nome' => $patient['paciente_nome'] ?? $bedHospitalStatus,
                'medico' => $patient['medico_responsavel'] ? 'Dr. ' . $patient['medico_responsavel'] : '',
            ]);
        }

        return $lines;
    }

    public static function getModalSector(Request $request, string $sector): string
    {
        $convenios = [];
        $conveniosFilter = "";
        foreach ($request->getQueryParams()['convenios'] as $key => $value) {
            $convenios[] = "'".$value."'";
        }
        $conveniosFilter = implode(",", $convenios);

        $sectorName = Setor::getSetorByCode($sector);

        $patientsSector = Internacao::getPatientBySector($sector, $conveniosFilter);

        $quantityHospitalBed = array_filter($patientsSector, function ($patient) {
            return $patient['leito_id'] !== null and $patient['leito_status'] !== 'B';
        });
        $quantityHospitalBed = count($quantityHospitalBed);

        $quantityPatients = array_filter($patientsSector, function ($patient) {
            return $patient['leito_id'] !== null and $patient['leito_status'] === 'O';
        });
        $quantityPatients = count($quantityPatients);

        $linesModal = self::getPatientLineModal($patientsSector);

        $modal = View::render('azophi/modal/pacientes_setor', [
            'unidade' => $sectorName,
            'linhas-tabela' => $linesModal,
            'quantidade-leitos' => $quantityHospitalBed,
            'quantidade-pacientes' => $quantityPatients
        ]);

        return $modal;
    }

    public static function reloadPage($convenios)
    {
        $cards = [];

        $fortalezaTimeZone = new DateTimeZone(CURRENT_TIMEZONE);
        $obData = new DateTime('now', $fortalezaTimeZone);
        $hojeData = $obData->format('Y-m-d H:i:s');
        $obData->sub(new DateInterval('P1D'));
        $ontemData = $obData->format('Y-m-d');

        $conveniosFilter = $convenios;
        // if(!empty($convenios)){

        //     foreach ($convenios as $key => $convenio) {
        //         $convenios[$key] = "'".trim($convenio)."'";
        //     }
        //     // $conveniosFilter = implode(",", $convenios);
        // }


        $internacoesHrgHoje = Internacao::getInternacaoHrgHoje($hojeData, empty($conveniosFilter) ? null : $conveniosFilter);
        $internacoesMaternidadeHoje = Internacao::getInternacaoMaternidadeHoje($hojeData, empty($conveniosFilter) ? null : $conveniosFilter);
        $isolamentosHrg = Internacao::getIsolamentoHrgHoje();
        $isolamentosMaternidade = Internacao::getIsolamentoMaternidadeHoje();

        $total = array();

        $totalPacientes = 0;
        $totalLeitos = 0;

        foreach ($internacoesHrgHoje as $internacao) {
            $leitosIsolados = array_keys(array_column($isolamentosHrg, 'STR_COD'), $internacao['STR_COD']);
            $internacao['ISOLAMENTO'] = count($leitosIsolados);

            // Vagas = Total de leitos - (Total de leitos ocupados + Total de leitos reservados)
            $vagas = $internacao['LEITOS'] - ($internacao['PACIENTES'] + $internacao['RESERVA']);

            // Porcentagem de ocupação
            $ocupacaoPorcentagem = 0;

            if ($internacao['LEITOS'] > 0)
                $ocupacaoPorcentagem = number_format(($internacao['PACIENTES'] / $internacao['LEITOS']) * 100, 0);

            //Verifica se o setor é o terceiro andar da maternidade e busca os recem nascidos 
            $recemNascidos = '';
            $quantidadeRNascidos = 0;
            if ($internacao['STR_COD'] === 'UM3') {
                $recemNascidos = Internacao::getRecemNascidos($conveniosFilter);
                $quantidadeRNascidos = $recemNascidos;
            }

            // Soma total dos elementos para exibição dos dados gerais

            $total['total-recem-nascido'] += $quantidadeRNascidos;
            $total['total-leitos'] += $internacao['LEITOS'] + $internacao['ISOLAMENTO'];
            $total['total-pacientes'] += $internacao['PACIENTES'];
            $total['total-reservados'] += $internacao['RESERVA'];
            $total['total-vagas'] += $vagas;
        }

        // Porcentagem de ocupação
        $ocupacaoPorcentagemTotalHRG = 0;
        if ($total['total-leitos'])
            $ocupacaoPorcentagemTotalHRG = number_format(($total['total-pacientes'] / $total['total-leitos']) * 100, 0);

        $totalPacientes += $total['total-pacientes'];
        $totalLeitos += $total['total-leitos'];
        $leitosHRG = $total['total-leitos'];
        $pacientesHRG = $total['total-pacientes'];
        $rnHRG = $total['total-recem-nascido'];

        $total = array();

        foreach ($internacoesMaternidadeHoje as $internacao) {
            $leitosIsolados = array_keys(array_column($isolamentosMaternidade, 'STR_COD'), $internacao['STR_COD']);
            $internacao['ISOLAMENTO'] = count($leitosIsolados);

            $vagas = // Vagas = Total de leitos - (Total de leitos ocupados + Total de leitos reservados)
                $internacao['LEITOS'] - ($internacao['PACIENTES'] + $internacao['RESERVA']);

            //Verifica se o setor é o terceiro andar da maternidade e busca os recem nascidos 
            $recemNascidos = '';
            $quantidadeRNascidos = 0;
            if ($internacao['STR_COD'] === 'UM3') {
                $recemNascidos = Internacao::getRecemNascidos($conveniosFilter);
                $quantidadeRNascidos = $recemNascidos;
            }

            // Soma total dos elementos para exibição dos dados gerais

            $total['total-recem-nascido'] += $quantidadeRNascidos;
            $total['total-leitos'] += $internacao['LEITOS'] + $internacao['ISOLAMENTO'];
            $total['total-pacientes'] += $internacao['PACIENTES'];
            $total['total-reservados'] += $internacao['RESERVA'];
            $total['total-vagas'] += $vagas;
        }

        // Porcentagem de ocupação
        $ocupacaoPorcentagemTotalMaternidade = 0;
        if ($total['total-leitos'])
            $ocupacaoPorcentagemTotalMaternidade = number_format(($total['total-pacientes'] / $total['total-leitos']) * 100, 0);

        $totalPacientes += $total['total-pacientes'];
        $totalLeitos += $total['total-leitos'];

        $cards['pacientes']['total'] = "Pacientes - ".$totalPacientes;
        $cards['pacientes']['HRG'] = "HRG - ".$pacientesHRG;
        $cards['pacientes']['maternidade'] = "Maternidade - ".$total['total-pacientes'];
        $cards['pacientes']['rn'] = "Recém-nascidos - ".($total['total-recem-nascido']+$rnHRG);

        $cards['leitos']['total'] = "Leitos - ".$totalLeitos;
        $cards['leitos']['HRG'] = "HRG - ".$leitosHRG;
        $cards['leitos']['maternidade'] = "Maternidade - ".$total['total-leitos'];
        
        $cards['ocupacao']['total'] = "Ocupação - ".number_format(($totalPacientes / $totalLeitos) * 100, 0)."<small class='d-inline'>%</small>";
        $cards['ocupacao']['HRG'] = "HRG - ".$ocupacaoPorcentagemTotalHRG."<small class='d-inline'>%</small>";
        $cards['ocupacao']['maternidade'] = "Maternidade - ".$ocupacaoPorcentagemTotalMaternidade."<small class='d-inline'>%</small>";

        $cards['altas24h'] = "Últimas 24h - ".Internacao::getAltas24h($conveniosFilter);
        $cards['altas12h'] = "Últimas 12h - ".Internacao::getAltas12h($conveniosFilter);

        $arrayInutil = [];
        $internacoesHrgHojePreserve = Internacao::getInternacaoHrgHoje($hojeData, empty($conveniosFilter) ? null : $conveniosFilter, true);
        $internacoesHrgOntemPreserve = Internacao::getInternacaoHrgHoje($ontemData, empty($conveniosFilter) ? null : $conveniosFilter, true);
        $cards['internacaoHojeHrg'] = self::getInternacaoTabela($internacoesHrgHojePreserve, $isolamentosHrg, $arrayInutil, true);
        $cards['internacaoOntemHrg'] = self::getInternacaoTabelaOntem($internacoesHrgOntemPreserve);

        $arrayInutil = [];
        $internacoesMaternidadeHojePreserve = Internacao::getInternacaoMaternidadeHoje($hojeData, empty($conveniosFilter) ? null : $conveniosFilter, true);
        $internacoesHrgOntemPreserve = Internacao::getInternacaoMaternidadeHoje($ontemData, empty($conveniosFilter) ? null : $conveniosFilter, true);
        $cards['internacaoHojeMaternidade'] = self::getInternacaoTabela($internacoesMaternidadeHojePreserve, $isolamentosMaternidade, $arrayInutil, true);
        $cards['internacaoOntemMaternidade'] = self::getInternacaoTabelaOntem($internacoesHrgOntemPreserve);

        $pacientes7Dias = Paciente::getPacientesUltimos7Dias(empty($conveniosFilter) ? null : $conveniosFilter);
        $cards['pacientes7Dias'] = self::getTabelaPaciente7Dias($pacientes7Dias);

        $cards['ocupacaoGeral'] = self::getOcupacaoGeral(empty($conveniosFilter) ? null : $conveniosFilter);

        $cards['admissaoAlta'] = self::getAltasAdmissaoUltimosDias(6, empty($conveniosFilter) ? null : $conveniosFilter);

        $pacientesPAHrg = Atendimento::getPAHrgAtendimento(-6, 0, empty($conveniosFilter) ? null : $conveniosFilter);
        $pacientesPAMaternidade = Atendimento::getPAMaternidadeAtendimento(-6, 0, empty($conveniosFilter) ? null : $conveniosFilter);

        $cards['paHRG'] = self::getPacientesPa($pacientesPAHrg, 'hrg');
        $cards['paMaternidade'] = self::getPacientesPa($pacientesPAMaternidade, 'maternidade');

        return $cards;
    }
}