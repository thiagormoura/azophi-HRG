<?php

namespace App\Controller\GestaoLeitos;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Http\Request;
use App\Model\GestaoLeitos\Setor;
use App\Model\GestaoLeitos\Solicitacao as SolicitationModel;
use App\Controller\GestaoLeitos\CommonsController;
use App\Model\GestaoLeitos\Leito;
use App\Model\Utils\Setor as SmartSetor;
use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;

class HomeController extends LayoutPage
{
    const accomodations = [
        "APT" => "Apartamento",
        "ENF" => "Enfermaria",
        "UTI" => "UTI",
        "UTIP" => "UTI Pediatrica",
        "ENFP" => "Enfermaria Pediatrica"
    ];

    private static function getSolicitationByAccommodation(array $solicitations)
    {
        $solicitationAccs = [];

        foreach ($solicitations as $solicitation) {
            $accommodation = trim(strtoupper($solicitation['SOLICITACAO_ACOMODACAO']));
            $solicitationAccs[$accommodation]++;
        }

        return $solicitationAccs;
    }

    private static function getCardsComponent()
    {
        $openSolicitation = SolicitationModel::getSolicitationByStatus('A');
        $progressSolicitation = SolicitationModel::getSolicitationByStatus('P');
        $openAccommodation = self::getSolicitationByAccommodation($openSolicitation);
        $progressAccommodation = self::getSolicitationByAccommodation($progressSolicitation);

        return View::render('gestaoleitos/home/cards', [
            "solicitacoes-aberto" => count($openSolicitation),
            "solicitacoes-aberto-enf" => $openAccommodation['ENF'] ?? 0,
            "solicitacoes-aberto-apt" => $openAccommodation['APT'] ?? 0,
            "solicitacoes-aberto-uti" => $openAccommodation['UTI'] ?? 0,
            "solicitacoes-preparo" => count($progressAccommodation),
            "solicitacoes-preparo-enf" => $progressAccommodation['ENF'] ?? 0,
            "solicitacoes-preparo-apt" => $progressAccommodation['APT'] ?? 0,
            "solicitacoes-preparo-uti" => $progressAccommodation['UTI'] ?? 0,
        ]);
    }

    private static function getFilterComponent(array $filters)
    {
        return View::render('gestaoleitos/home/filtros', [
            'unidades-liberada' => $filters['released'] ?? '',
            'acomodacoes' => $filters['accommodation'] ?? '',
            'unidades-solicitantes' => $filters['solicitation'] ?? '',
        ]);
    }

    public static function getHome(Request $request)
    {
        // $solicitations = SolicitationModel::getFiltersHome();
        // $filters = self::getTableFilters($solicitations);
        $currentTimeZone = new DateTimeZone(CURRENT_TIMEZONE);

        $currentDate = new DateTime('now', $currentTimeZone);
        $now = $currentDate->format('Y-m-d');
        $currentDate->sub(new DateInterval('P15D'));
        $twoWeeksAgo = $currentDate->format('Y-m-d');

        $content = View::render('gestaoleitos/home', [
            'cards' => self::getCardsComponent(),
            'filtros' =>
            self::getFilterComponent([]),
            // json_encode($filters, JSON_UNESCAPED_UNICODE),
            "data-agora" => $now,
            "data-15-dias" => $twoWeeksAgo
        ]);
        return parent::getPage('Gestão de Leitos | Central de Vagas', 'gestaoleitos', $content, $request);
    }

    public static function getTableFilters(array $solicitations)
    {
        $hospitalizationUnits = Setor::getHospitalizationUnits();
        $solicitationUnits = Setor::getSolicitationUnits();

        $hospitalizationUnits = CommonsController::getCodeAndUnits($hospitalizationUnits);
        $solicitationUnits = CommonsController::getCodeAndUnits($solicitationUnits);

        $filters = [];
        foreach ($solicitations as $solicitation) {

            $bedAndSector = SolicitationModel::getBedAndSectorLiberateFromSolicitation($solicitation['idSOLICITACAO'], $solicitation['SOLICITACAO_STATUS']);
            if ($bedAndSector) {
                $unitCode = trim($bedAndSector['codigo_setor']);
                $filters['released'][$unitCode] = $hospitalizationUnits[$unitCode];
            }

            if (!is_null($solicitation["SOLICITACAO_SETOR"])) {
                $unitCode = trim($solicitation["SOLICITACAO_SETOR"]);
                if (!empty($solicitationUnits[$unitCode]))
                    $filters['solicitation'][$unitCode] = $solicitationUnits[$unitCode];
            }

            if (!is_null($solicitation["SOLICITACAO_ACOMODACAO"])) {
                $accommodation = strtoupper(trim($solicitation["SOLICITACAO_ACOMODACAO"]));
                $filters['accommodation'][$accommodation] = self::accomodations[$accommodation];
            }
        }

        $elementFilters = [];

        foreach ($filters as $type => $values) {
            foreach ($values as $key => $value) {
                $elementFilters[$type] .= View::render('utils/option', [
                    "id" => $key,
                    "nome" => $value,
                    "selected" => "",
                    "disabled" => ""
                ]);
            }
        }

        return $elementFilters;
    }

    private static function getBadgesByStatus($status)
    {
        switch ($status) {
            case 'A':
                return "<span class='badge badge-warning' data-status='A'>Solicitação<br>Aberta</span>";
                break;

            case 'P':
                return "<span class='badge badge-success' data-status='P'>Vaga em<br>Preparo</span>";
                break;

            case 'L':
                return "<span class='badge badge-info' data-status='L'>Liberado</span>";
                break;

            case 'E':
                return "<span class='badge badge-dark' data-status='E'>Solicitação<br>Encerrada</span>";
                break;

            case 'RU':
                return "<span class='badge badge-primary' data-status='RU'>Paciente<br>Admitido</span>";
                break;

            case 'RU2':
                return "<span class='badge badge-primary' data-status='RU'>Paciente<br>Admitido</span>";
                break;

            case 'RC':
                return "<span class='badge badge-dark' data-status='RC'>Reserva<br>Encerrada</span>";
                break;

            case 'RC2':
                return "<span class='badge badge-dark' data-status='RC'>Reserva<br>Encerrada</span>";
                break;

            case 'C':
                return "<span class='badge badge-danger' data-status='C'>Solicitação<br>Cancelada</span>";
                break;
        }
    }

    public static function getModalDescricao($request)
    {
        $solicitacao = SolicitationModel::getSolicitation($request->getPostVars()['id']);

        $niver = new Datetime($solicitacao['DTNASC']);
        $hj = new Datetime();
        $inter = $hj->diff($niver);
        $textAge = "";
        if ($inter->y == 0) $textAge = $inter->format("%m meses");
        elseif ($inter->m == 0) $textAge = $inter->format("%d dias");
        else $textAge = $inter->format("%y anos");

        $reasons = array(
            "ADCIRE" => "Admissao Cirurgica Eletiva",
            "ADCIRU" => "Admissao Cirurgica de Urgencia",
            "ADCLIU" => "Admissao Clinica de Urgencia",
            "TROINS" => "Transferencia de Outra Instituição"
        );

        $unitiesSolic = CommonsController::getCodeAndUnits(Setor::getSolicitationUnits());
        $unitiesHosp = CommonsController::getCodeAndUnits(Setor::getHospitalizationUnits());

        return View::render('gestaoleitos/home/modalDescricaoSolicitacao', [
            'id-solicitacao' => $solicitacao['idSOLICITACAO'],
            'registro' => $solicitacao['REGISTRO'],
            'origem' => $solicitacao['ORIGEM'] == "I" ? "Interno" : "Externo",
            'nome' => $solicitacao['NOME'],
            'idade' => $textAge,
            'sexo' => $solicitacao['SEXO'] == "M" ? "Masculino" : ($solicitacao['SEXO'] == "F" ? "Feminino" : "Outro"),
            'perfil' => $solicitacao['SOLICITACAO_PERFIL'] == "Cr" ? "Cirurgico" : ($solicitacao['SOLICITACAO_PERFIL'] == "Cl" ? "Clinico" : "Outro"),
            'convenio' => $solicitacao['CONVENIO'],
            'prioridade' => $solicitacao['SOLICITACAO_PRIORIDADE'],
            'is-covid' => $solicitacao['ISCOVID'] == "1" ? "Sim" : "Não",
            'is-pediatrico' => $solicitacao['SOLICITACAO_PED'] == "1" ? "Sim" : "Não",
            'acomodacao-solicitada' => $solicitacao['SOLICITACAO_ACOMODACAO'] == "Apt" ? "Apartamento" : ($solicitacao['SOLICITACAO_ACOMODACAO'] == "Enf" ? "Enfermaria" : "UTI"),
            'motivo-solicitacao' => $reasons[$solicitacao['SOLICITACAO_MOTIVO']],
            'unidade-solicitante' => $unitiesSolic[$solicitacao['SOLICITACAO_SETOR']],
            'isolamento' => $solicitacao['SOLICITACAO_ISOLAMENTO'] == "1" ? "Sim" : "Não",
            'medico-assistente' => $solicitacao['SOLICITACAO_MEDICO_SOLIC'],
            'dthr-admissao' => $solicitacao['SOLICITACAO_DTHR_ADMISSAO'] != null ? (new Datetime($solicitacao['SOLICITACAO_DTHR_ADMISSAO']))->format("d/m/Y H:i:s") : '',
            'dthr-criacao-solicitacao' => "Solicitado em " . (new Datetime($solicitacao['SOLICITACAO_DTHR_REGISTRO']))->format("d/m/Y H:i:s") . " por " . $solicitacao['SOLICITACAO_SOLICITANTE'],
            'dthr-atendimento' => "Atendido em ". ($solicitacao['SOLICITACAO_DTHR_ATENDIMENTO'] != null ? (new Datetime($solicitacao['SOLICITACAO_DTHR_ATENDIMENTO']))->format("d/m/Y H:i:s") : '') . " por ". $solicitacao['USUARIO_ATENDIMENTO'],
            'dthr-liberacao' => "Reservado em ". ($solicitacao['SOLICITACAO_DTHR_RESERVADO'] != null ? (new Datetime($solicitacao['SOLICITACAO_DTHR_RESERVADO']))->format("d/m/Y H:i:s") : '') . " por ". $solicitacao['USUARIO_LIBERACAO'],
            'nome-unidade' => $unitiesHosp[$solicitacao['setorLiberado']],
            'nome-leito' => Leito::getBedByCode($solicitacao['leitoLiberado'])['nome']
        ]);
    }

    public static function getSolicitationsByTimeAndFilter(Request $request)
    {
        $colums = [
            "idSOLICITACAO",
            "REGISTRO",
            "PACIENTE_NOME",
            "SOLICITACAO_DTHR_REGISTRO",
            "SOLICITACAO_SETOR",
            "SOLICITACAO_ACOMODACAO",
            "SOLICITACAO_STATUS",
            "UNIDADE_LIBERADA",
            "COMPATIBLE",
            "BUTTON"
        ];

        $post = $request->getPostVars();

        if (isset($post['start']) && $post['length'] != -1) {
            $limit = intval($post['start']) . ", " . intval($post['length']);
        }

        $order = "";
        if (!empty($post['order'])) {
            $endItem = end($post['order']);
            $firstItem = reset($post['order']);

            foreach ($post['order'] as $item) {
                if ($item['column'] == 9 || $item['column'] == 10)
                    continue;

                if ($item == $firstItem)
                    $order = $colums[$item['column']] . " " . $item['dir'] . (count($post['order']) > 1 ? ", " : "");

                elseif ($item == $endItem)
                    $order .= $colums[$item['column']] . " " . $item['dir'];

                else
                    $order .= $colums[$item['column']] . " " . $item['dir'] . ", ";
            }
        }

        $inputTextSearch = !empty($post['name']) ? strtoupper($post['name']) : null;

        $unitLiberate = null;
        if (!empty($post['unitLiberate'])) {
            $post['unitLiberate'] = array_map(function ($value) {
                return "'" . $value . "'";
            }, $post['unitLiberate']);
            $unitLiberate = implode(",", $post['unitLiberate']);
        }

        $accomodation = null;
        if (!empty($post['accomodation'])) {
            $post['accomodation'] = array_map(function ($value) {
                return "'" . $value . "'";
            }, $post['accomodation']);
            $accomodation = implode(",", $post['accomodation']);
        }

        $status = null;
        if (!empty($post['status'])) {
            $post['status'] = array_map(function ($value) {
                return "'" . $value . "'";
            }, $post['status']);
            $status = implode(",", $post['status']);
        }

        $solicitationUnit = null;
        if (!empty($post['solicitationUnit'])) {
            $post['solicitationUnit'] = array_map(function ($value) {
                return "'" . $value . "'";
            }, $post['solicitationUnit']);
            $post['solicitationUnit'] = array_map('trim', $post['solicitationUnit']);

            $solicitationUnit = implode(",", $post['solicitationUnit']);
        }

        $filters = [
            "inputText" => !is_null($inputTextSearch) ?
                "(REGISTRO like '%" . $inputTextSearch . "%' OR 
                PACIENTE_NOME like '%" . $inputTextSearch . "%' OR
                idSOLICITACAO like '%" . $inputTextSearch . "%')" : null,
            "unitLiberate" => !is_null($unitLiberate) ?
                "(hist_unit.sectorLiberate in (" . $unitLiberate . "))" : null,

            "typeOfBed" => !is_null($accomodation) ?
                "(SOLICITACAO_ACOMODACAO in (" . $accomodation . "))" : null,

            "solicitationUnits" => !is_null($solicitationUnit) ?
                "(SOLICITACAO_SETOR in (" . $solicitationUnit . "))" : null,

            "statusSolicitation" => !is_null($status) ?
                "(SOLICITACAO_STATUS in (" . $status . "))" : null
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

        $solicitations = self::getSolicitationHTMLToColumn(SolicitationModel::getSolicitations($limit, $filterString));
        return array(
            "draw" => isset($post['draw']) ? intval($post['draw']) : 0,
            "recordsTotal" => count($solicitations['solicitacoes']),
            "recordsFiltered" => SolicitationModel::getSolicitations(null, $filterString, true)[0]['qtd'],
            "data" => $solicitations['solicitacoes'],
            "acomodacao" => self::beautify(array_values(array_column(SolicitationModel::getGroupOfSolicitations("s.SOLICITACAO_ACOMODACAO as acomodacao", "acomodacao", $filterString), "acomodacao")), "A"),
            "unidadeReservada" => self::beautify(array_values(array_column(SolicitationModel::getGroupOfSolicitations("hist_unit.sectorLiberate", "hist_unit.sectorLiberate", $filterString." AND hist_unit.sectorLiberate is not null"), "sectorLiberate")), "UR"),
            "unidadeSolicitante" => self::beautify(array_values(array_column(SolicitationModel::getGroupOfSolicitations("s.SOLICITACAO_SETOR", "s.SOLICITACAO_SETOR", $filterString), "SOLICITACAO_SETOR")), "US")
        );
    }

    public static function getSolicitationHTMLToColumn($solicitations)
    {
        $releasedUnit = '---';
        $newSolicitations = [];
        $typesOfBed = [];

        foreach ($solicitations as $key => $solicitation) {
            $newSolicitation[$key] = $solicitation;

            $typesOfBed[strtoupper($solicitation['SOLICITACAO_ACOMODACAO'])]++;

            $newSolicitation[$key]['idSOLICITACAO'] = View::render('gestaoleitos/home/id_link_column', [
                "id-solicitacao" => $solicitation['idSOLICITACAO']
            ]);

            $bedAndSector = SolicitationModel::getBedAndSectorLiberateFromSolicitation($solicitation['idSOLICITACAO'], $solicitation['SOLICITACAO_STATUS']);
            if ($bedAndSector)
                $newSolicitation[$key]['UNIDADE_LIBERADA'] = SmartSetor::getSetorByCode($bedAndSector['codigo_setor'])->nome;

            else $newSolicitation[$key]['UNIDADE_LIBERADA'] = $releasedUnit;

            $newSolicitation[$key]['SOLICITACAO_ACOMODACAO'] = View::render('gestaoleitos/home/value_filter_on_table', [
                "value" => strtoupper($solicitation['SOLICITACAO_ACOMODACAO']),
                "label" => strtoupper(self::accomodations[strtoupper($solicitation['SOLICITACAO_ACOMODACAO'])])
            ]);

            $newSolicitation[$key]['SOLICITACAO_STATUS'] = self::getBadgesByStatus($solicitation['SOLICITACAO_STATUS']);

            $newSolicitation[$key]["SOLICITACAO_SETOR"] = strtoupper(SmartSetor::getSetorByCode(trim($solicitation["SOLICITACAO_SETOR"]))->nome);

            if (!in_array($solicitation['SOLICITACAO_STATUS'], ["L", "C", "E", "RC"])) {
                $newSolicitation[$key]["COMPATIBLE"] = count((array) CommonsController::pullAdequateBeds(
                    null,
                    array(
                        "ignoreIncompatible" => 0,
                        "filters" => array(
                            "covid" => $solicitation['ISCOVID'],
                            "accommodation" => $solicitation['SOLICITACAO_ACOMODACAO'],
                            "gender" => $solicitation["PACIENTE_SEXO"],
                            "sector" => null,
                            "pediatric" => $solicitation['SOLICITACAO_PED']
                        )
                    )
                )) > 0 ? "<i class='fas fa-check-circle fa-2x text-success'></i>" : "<i class='fas fa-times-circle fa-2x text-danger'></i>";
            } else $newSolicitation[$key]["COMPATIBLE"] = "---";

            $newSolicitation[$key]['BUTTON'] = View::render('gestaoleitos/home/buttons/modal_button', [
                "id-solicitacao" => $solicitation['idSOLICITACAO']
            ]);

            $newSolicitation[$key]['SOLICITACAO_DTHR_REGISTRO'] = (new DateTime($solicitation['SOLICITACAO_DTHR_REGISTRO']))->format("d/m/Y H:i:s");
            
            $newSolicitation[$key]['PACIENTE_NOME'] = $solicitation['PACIENTE_NOME'] ?? "";

            $newSolicitations[] = $newSolicitation[$key];
        }

        return [
            "solicitacoes" => $newSolicitations,
            "tipo-leito" => $typesOfBed
        ];
    }

    public static function beautify(array $data, string $type)
    {
        $newArray = [];
        switch (true) {
            case ($type == 'A'):
                foreach ($data as $value) {
                    $newArray[] = [
                        "text" => strtoupper(self::accomodations[strtoupper($value)]),
                        "value" => $value
                    ];
                }
                return $newArray;
                break;

            case (($type == 'UR') || ($type == 'US')):
                foreach ($data as $value) {

                    $nome = Setor::getUnitByCode(trim($value))['nome'];
                    if(is_null($nome)) continue;

                    $newArray[] = [
                        "text" => $nome,
                        "value" => $value
                    ];
                }
                return $newArray;
                break;
            
            default:
                return $data;
                break;
        }
    }
}
