<?php

namespace App\Controller\GestaoLeitos;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use \App\Controller\Error\ErrorController;
use App\Http\Request;
use App\Model\GestaoLeitos\Leito;
use App\Model\GestaoLeitos\Setor;
use App\Model\GestaoLeitos\Solicitacao as SolicitationModel;
use App\Model\GestaoLeitos\Paciente;
use DateTime;
use DateTimeZone;
use Exception;

date_default_timezone_set("America/Recife");

class SolicitationController extends LayoutPage
{

    public static function validateInputs($inputs, $statusSolicitation)
    {
        switch ($statusSolicitation) {
            case 'L':
                $inputsRequired = [
                    "id" => ["ptBR" => "id da solicitação"],
                    "uni" => ["ptBR" => "setor selecionado"],
                    "lei" => ["ptBR" => "leito selecionado"],
                    "registro" => ["ptBR" => "invisivel do registro"],
                    "gender" => ["ptBR" => "gênero"]
                ];
                foreach ($inputsRequired as $input => $value) {
                    if (empty($inputs[$input])) {
                        return [
                            "title" => "Erro: Campo vazio!",
                            "message" => "O campo <b>" . $value['ptBR'] . "</b> está vazio!",
                            "succeeded" => false
                        ];
                    }
                }

                return ["succeeded" => true];
                break;
            case 'P':

                $inputsRequired = [
                    "id" => ["ptBR" => "id da solicitação"],
                    "uni" => ["ptBR" => "setor selecionado"],
                    "lei" => ["ptBR" => "leito selecionado"],
                    "registro" => ["ptBR" => "invisivel do registro"],
                    "gender" => ["ptBR" => "gênero"]
                ];

                foreach ($inputsRequired as $input => $value) {
                    if (empty($inputs[$input])) {
                        return [
                            "title" => "Erro: Campo vazio!",
                            "message" => "O campo <b>" . $value['ptBR'] . "</b> está vazio!",
                            "succeeded" => false
                        ];
                    }
                }

                return ["succeeded" => true];
                break;

            default:

                $inputsRequired = [
                    "patientName" => ["isFalseyValue" => false, "ptBR" => "paciente"],
                    "patientRegistration" => ["isFalseyValue" => false, "ptBR" => "invisivel do registro"],
                    "patientBirth" => ["isFalseyValue" => false, "ptBR" => "data de nascimento"],
                    "patientGender" => ["isFalseyValue" => false, "ptBR" => "gênero"],
                    "patientHealthInsurance" => ["isFalseyValue" => false, "ptBR" => "covênio"],
                    "patientAccommodation" => ["isFalseyValue" => false, "ptBR" => "acomodação do contrato"],
                    "solicitationProfile" => ["isFalseyValue" => false, "ptBR" => "perfil do paciente"],
                    "isCovid" => ["isFalseyValue" => true, "ptBR" => "COVID"],
                    "solicitationAdmissionDate" => ["isFalseyValue" => false, "ptBR" => "data/hora da admissão"],
                    "covidSuspect" => ["isFalseyValue" => true, "ptBR" => "suspeito de COVID"],
                    "solicitationPediatric" => ["isFalseyValue" => true, "ptBR" => "pediátrico"],
                    "solicitationPriority" => ["isFalseyValue" => false, "ptBR" => "prioridade"],
                    "solicitationAccommodation" => ["isFalseyValue" => false, "ptBR" => "acomodação solicitada"],
                    "solicitationIsolation" => ["isFalseyValue" => true, "ptBR" => "isolamento"],
                    "solicitationUnit" => ["isFalseyValue" => false, "ptBR" => "unidade/setor solicitante"],
                    "solicitationReason" => ["isFalseyValue" => false, "ptBR" => "motivo da solicitação"],
                    "solicitationDoctor" => ["isFalseyValue" => false, "ptBR" => "médico assistente"],
                    "patientDiagnosis" => ["isFalseyValue" => false, "ptBR" => "diagnostico da internação"]
                ];

                foreach ($inputsRequired as $input => $value) {
                    if ($value['isFalseyValue']) {
                        if (is_null($inputs[$input]) || $inputs[$input] == "") {
                            return [
                                "title" => "Erro: Campo vazio!",
                                "message" => "O campo <b>" . $value['ptBR'] . "</b> está vazio!",
                                "succeeded" => false,
                                $inputs
                            ];
                        }
                    } else {
                        if (empty($inputs[$input])) {
                            return [
                                "title" => "Erro: Campo vazio!",
                                "message" => "O campo <b>" . $value['ptBR'] . "</b> está vazio!",
                                "succeeded" => false
                            ];
                        }
                    }
                }

                return ["succeeded" => true];
                break;
        }
    }

    private static function getSelect(
        array $options,
        string $value,
        string $label,
        array $select,
        ?string $selected = null
    ) {
        $optionsSelect = View::render('utils/option', [
            'id' => '',
            'nome' => $select['placeholder'] ?? 'Selecione uma opção',
            'selected' => $selected === null ? 'selected' : '',
            'disabled' => 'disabled'
        ]);

        foreach ($options as $option) {
            $optionsSelect .= View::render('utils/option', [
                'id' => $option[$value],
                'nome' => $option[$label],
                'selected' => $selected === $option[$value] ? 'selected' : '',
            ]);
        }

        return View::render('utils/select', [
            'name' => $select['name'],
            'id' => $select['id'],
            "class" => "form-control",
            'disabled' => $select['disabled'] ? 'disabled' : '',
            'options' => $optionsSelect,
        ]);
    }

    private static function getRadioButtons(array $data, string $name, ?string $checked = null, ?bool $disabled = false)
    {
        $options = '';

        foreach ($data as $code => $value) {
            $options .= View::render('gestaoleitos/solicitacao/radio_button', [
                'name' => $name,
                'id' => $value['id'],
                'value' => $value['value'] ?? $value['id'],
                'label' => $value['label'],
                'checked' => $code == strtoupper($checked) ? 'checked' : '',
                "disabled" => !$disabled ? "" : 'disabled'
            ]);
        }

        return $options;
    }

    public static function getPatientFormData(string $register, string $idSolicitacao = null)
    {
        
        $patient = Paciente::getPatient($register);
        if($patient === false){
            $patient = Paciente::getPatientMySQL($register, $idSolicitacao);
            $precautions = SolicitationModel::getRisksAndPrecautionsInSolicitation($idSolicitacao);
        }
        else{
            $precautions = Paciente::getPatientPrecautions($register);
            $risks = Paciente::getPatientRisks($register);
        }

        $currentTimeZone = new DateTimeZone(CURRENT_TIMEZONE);
        $patientBirthdate = new Datetime($patient['DTNASC'], $currentTimeZone);
        $patientHealthInsurance = $patient['CONVENIO'];

        $diffDates = $patientBirthdate->diff(new Datetime());
        $patientAge = $diffDates->y ? $diffDates->y . ' anos'
            : ($diffDates->m ? $diffDates->m . ' meses' : $diffDates->d . ' dias');

        $patientGender = $patient['SEXO'] === 'F' ? 'Feminino'
            : ($patient['SEXO'] === 'M' ? 'Masculino' : 'Outro');

        $accomodations = [
            'APT' => [
                'id' => 'APT',
                'label' => 'Apartamento'
            ],
            'ENF' => [
                'id' => 'ENF',
                'label' => 'Enfermaria'
            ]
        ];

        $accomodationsOptions = self::getRadioButtons($accomodations, 'acomodacao-contrato', strtoupper($patient['ACOMODACAO']), true);

        foreach ($precautions as $precaution) {
            switch ($precaution['PRECAUCAO_VALOR']) {
                case '26':
                    $contactPrecaution = 'checked';
                    break;
                case '22':
                    $vigilantPrecaution = 'checked';
                    break;
                case '23':
                    $dropletsPrecaution = 'checked';
                    break;
                case '24':
                    $protectorPrecaution = 'checked';
                    break;
                case '25':
                    $aerosolPrecaution = 'checked';
                    break;
                case '39':
                    $fallRisk = 'checked';
                    break;
            }
        }


        return View::render('gestaoleitos/solicitacao/section_paciente_data', [
            'data-nascimento' => $patientBirthdate->format('Y-m-d'),
            'idade' => $patientAge,
            'convenio' => $patientHealthInsurance,
            'genero' => $patientGender,
            'acomodacao-contrato' => $accomodationsOptions,
            'precaucoes-contato' => $contactPrecaution,
            'precaucoes-vigilancia' => $vigilantPrecaution,
            'precaucoes-goticulas' => $dropletsPrecaution,
            'precaucoes-protetor' => $protectorPrecaution,
            'precaucoes-aerosol' => $aerosolPrecaution,
            'riscos-queda' => $fallRisk,
        ]);
    }

    private static function getPatientSection(?array $solicitation = null, ?bool $disabled = false)
    {
        $patientSelect = [
            'name' => 'paciente',
            'id' => 'gleitos-paciente',
            'placeholder' => 'Selecione um paciente',
        ];
        
        if (is_null($solicitation)) {
            
            $hospitalizationUnits = Setor::getHospitalizationUnits();
            $unitsCode = array_column($hospitalizationUnits, 'codigo');
            $unitsString = "'" . implode("','", $unitsCode) . "'";
            $patients = Paciente::getPatients($unitsString);

            foreach ($patients as $index => $patient) {
                $reservation = Paciente::getPatientReservation($patient['PACIENTE_REGISTRO']);
                if (!empty($reservation))
                    unset($patients[$index]);
            }



            $patientSelect = self::getSelect(
                $patients,
                'PACIENTE_REGISTRO',
                'PACIENTE_NOME',
                $patientSelect
            );

            $data = [
                'pacientes' => $patientSelect,
                'paciente-data' => '',
                'paciente-disabled' => '',
                'historico' => ''
            ];
        } else {
            
            $patient = Paciente::getPatientMySQL($solicitation['REGISTRO'], $solicitation['idSOLICITACAO'], true);

            if($patient === false)
                throw new Exception("Paciente não existe nessa solicitação", 404);

            $patientSelect = self::getSelect(
                [["REGISTRO" => $patient['REGISTRO'], "NOME" => $patient['NOME']]],
                'REGISTRO',
                'NOME',
                $patientSelect,
                $patient['REGISTRO']
            );

                
            $patientData = self::getPatientFormData($patient['REGISTRO'], $solicitation['idSOLICITACAO']);

            $data = [
                'pacientes' => $patientSelect,
                'paciente-data' => $patientData,
                'paciente-disabled' => 'disabled',
                'historico' => CommonsController::getHistoricSolic($solicitation['idSOLICITACAO'])
            ];
        }

        $content = View::render('gestaoleitos/solicitacao/section_paciente', $data);

        return $content;
    }

    public static function getSectorAndBedLiberate($request)
    {
        $idSolicitation = $request->getPostVars()['id'];
        $solicitation = SolicitationModel::getSolicitation($idSolicitation);

        return [
            "html" => self::getButtonsByPermAndStatus($request, $idSolicitation),
            "status" => $solicitation['SOLICITACAO_STATUS']
        ];
    }

    private static function getSolicitationSection(Request $request, ?array $solicitation = null, ?bool $edit = false)
    {
        $solicitationId = $solicitation['idSOLICITACAO'];

        $profiles = [
            'CL' => [
                'id' => 'CL',
                'label' => 'Clínico'
            ],
            'CR' => [
                'id' => 'CR',
                'label' => 'Cirurgico'
            ],
            'OU' => [
                'id' => 'OU',
                'label' => 'Outro'
            ],
        ];

        $profileOptions = self::getRadioButtons($profiles, 'perfil', $solicitation['SOLICITACAO_PERFIL']);

        $covid = [
            1 => [
                'id' => 'covid-sim',
                'label' => 'Sim',
                'value' => 1
            ],
            0 => [
                'id' => 'covid-nao',
                'label' => 'Não',
                'value' => 0
            ]
        ];

        $covidOptions = self::getRadioButtons($covid, 'covid', $solicitation['ISCOVID']);

        $suspectCovid = [
            1 => [
                'id' => 'suspeito-covid-sim',
                'label' => 'Sim'
            ],
            0 => [
                'id' => 'suspeito-covid-nao',
                'label' => 'Não'
            ]
        ];

        $suspectCovid = self::getRadioButtons($suspectCovid, 'covid-suspeito', $solicitation['COVID_SUSPEITO']);

        $covidObservations = $solicitation['COVID_OBSERVACAO'];

        $admissionDate = substr($solicitation['SOLICITACAO_DTHR_ADMISSAO'], 0, 10);
        $admissionHour = substr($solicitation['SOLICITACAO_DTHR_ADMISSAO'], 11, 8);

        $pediatric = [
            1 => [
                'id' => 'pediatrico-sim',
                'label' => 'Sim',
                'value' => 1
            ],
            0 => [
                'id' => 'pediatrico-nao',
                'label' => 'Não',
                'value' => 0
            ]
        ];

        $pediatricOptions = self::getRadioButtons($pediatric, 'pediatrico', $solicitation['SOLICITACAO_PED']);

        $priorities = [
            '1' => 'Muito Baixa',
            '2' => 'Baixa',
            '3' => 'Média',
            '4' => 'Alta',
            '5' => 'Muito Alta',
        ];

        $priorityLabel = $priorities[$solicitation['SOLICITACAO_PRIORIDADE']];

        $accomodations = [
            'APT' => [
                'id' => 'acomodacao-solicitada-apt',
                'label' => 'Apartamento',
                'value' => 'APT'
            ],
            'ENF' => [
                'id' => 'acomodacao-solicitada-enf',
                'label' => 'Enfermaria',
                'value' => 'ENF'
            ],
            'UTI' => [
                'id' => 'acomodacao-solicitada-uti',
                'label' => 'UTI',
                'value' => 'UTI'
            ]
        ];

        $accomodationsOptions = self::getRadioButtons($accomodations, 'acomodacao-solicitada', $solicitation['SOLICITACAO_ACOMODACAO']);

        $isolation = [
            1 => [
                'id' => '0',
                'label' => 'Sim'
            ],
            0 => [
                'id' => '1',
                'label' => 'Não'
            ]
        ];

        $isolationOptions = self::getRadioButtons($isolation, 'isolamento', $solicitation['SOLICITACAO_ISOLAMENTO']);

        $solicitationReasons = [
            ['codigo' => 'ADCIRE', 'label' => 'Admissao Cirurgica Eletiva'],
            ['codigo' => 'ADCIRU', 'label' => 'Admissao Cirurgica de Urgencia'],
            ['codigo' => 'ADCLIU', 'label' => 'Admissao Clinica de Urgencia'],
            ['codigo' => 'TROINS', 'label' => 'Transferencia de Outra Instituição'],
        ];

        $reasonSelect = [
            'name' => 'motivo-solicitacao',
            'id' => 'gleitos-motivo-solicitacao',
        ];

        $reasonSelect = self::getSelect(
            $solicitationReasons,
            'codigo',
            'label',
            $reasonSelect,
            $solicitation['SOLICITACAO_MOTIVO']
        );

        $solicitationUnits = Setor::getSolicitationUnits();
        $unitsSelect = [
            'name' => 'unidades-solicitante',
            'id' => 'gleitos-unidades-solicitante',
        ];

        $unitsSelect = self::getSelect(
            $solicitationUnits,
            'codigo',
            'nome',
            $unitsSelect,
            $solicitation['SOLICITACAO_SETOR']
        );

        $relevantInformation = $solicitation['OUTRAS_INFOS'];
        $internationDiagnostic = $solicitation['DIAGNOSTICO'];
        $solicitationMedic = $solicitation['SOLICITACAO_MEDICO_SOLIC'];
        $status = self::getFormattedStatus($solicitation['SOLICITACAO_STATUS']);

        $content = View::render('gestaoleitos/solicitacao/section_solicitacao', [
            'id' => is_null($solicitationId) ? '' : '#' . $solicitationId,
            'status' => $status['label'],
            'status-color' => $status['color'],
            'perfis' => $profileOptions,
            'covid' => $covidOptions,
            'covid-suspeito' => $suspectCovid,
            'covid-observacao' => $covidObservations,
            'data-admissao' => $admissionDate,
            'hora-admissao' => $admissionHour,
            'pediatrico' => $pediatricOptions,
            'prioridade' => $solicitation['SOLICITACAO_PRIORIDADE'],
            'prioridade-label' => $priorityLabel,
            'acomodacao-solicitada' => $accomodationsOptions,
            'isolamento' => $isolationOptions,
            'motivo-solicitacao' => $reasonSelect,
            'unidades-solicitantes' => $unitsSelect,
            'diagnostico-internacao' => $internationDiagnostic,
            'diagnostico-internacao-disabled' => $edit ? 'disabled' : '',
            'informacoes-relevantes' => $relevantInformation,
            'medico-assistente' => $solicitationMedic,
            'botoes-adicionais' => self::getButtonsByPermAndStatus($request, $solicitationId ?? null, $edit),
            'solicitacao-disabled' => (empty($status['label']) || $edit) ? '' : 'disabled'
        ]);

        return $content;
    }

    private static function getButtonsByPermAndStatus(Request $request, string $idSolicitation = null, bool $edit = false)
    {

        $permEdit = CommonsController::checkPermissao($request->user, "admin", "gleitos-editar-solicitacao");

        if (is_null($idSolicitation) && CommonsController::checkPermissao($request->user, "admin", "gleitos-solicitar"))
            return View::render('gestaoleitos/solicitacao/buttons_novo');

        elseif ($edit && $permEdit)
            return View::render('gestaoleitos/solicitacao/buttons_edition');

        else {

            $solicitation = SolicitationModel::getSolicitation($idSolicitation);
            $status = $solicitation['SOLICITACAO_STATUS'];

            $permPrepare = CommonsController::checkPermissao($request->user, "admin", "gleitos-preparar-solicitacao");
            $permReserve = CommonsController::checkPermissao($request->user, "admin", "gleitos-liberar-solicitacao");
            $permCancel = CommonsController::checkPermissao($request->user, "admin", "gleitos-cancelar-solicitacao");
            $permFinish = CommonsController::checkPermissao($request->user, "admin", "gleitos-encerrar-reserva");

            $buttons = [];

            switch ($status) {
                case 'A':

                    $hospitalizationOptions = self::getBedsToReservation($solicitation);
                    if ($permPrepare || $permReserve) {
                        $buttons['group_beds'] = View::render('gestaoleitos/solicitacao/group_bed_adequates', [
                            "unidades-internacao" => $hospitalizationOptions
                        ]);

                        $buttons['prepare'] = View::render('gestaoleitos/solicitacao/buttons/button_prepare_bed');
                        $buttons['reserve'] = View::render('gestaoleitos/solicitacao/buttons/button_reserve_bed');
                    }

                    if ($permEdit)
                        $buttons['edit'] = View::render('gestaoleitos/solicitacao/buttons/button_edit_solic');


                    if ($request->user->id == SolicitationModel::getSolicitationCreator($idSolicitation) && $permCancel)
                        $buttons['cancelOrReserve'] = View::render('gestaoleitos/solicitacao/buttons/button_cancel_solic');

                    elseif ($permFinish && $solicitation['SOLICITACAO_STATUS'] != "L")
                        $buttons['cancelOrReserve'] = View::render('gestaoleitos/solicitacao/buttons/button_finish_reserve');

                    return View::render('gestaoleitos/solicitacao/buttons_aberta', [
                        "group_beds" => $buttons['group_beds'],
                        "prepareBed" => $buttons['prepare'] ?? "",
                        "reserveBed" => $buttons['reserve'] ?? "",
                        "editSolicitation" => $buttons['edit'] ?? "",
                        "cancelOrReserveSolicitation" => $buttons['cancelOrReserve'] ?? ""
                    ]);

                    break;

                case 'P':

                    $permChangeBed = CommonsController::checkPermissao($request->user, "admin", "gleitos-alterar-leito-liberado");
                    $permCancelPreparation = CommonsController::checkPermissao($request->user, "admin", "gleitos-cancelar-preparacao");

                    $bedAndSector =  self::getBedAndSectorChooseToSolicitation($idSolicitation);
                    $buttons['group_beds'] = View::render('gestaoleitos/solicitacao/group_bed_selected', [
                        'unidade-internacao' => $bedAndSector['sector'],
                        'leito-internacao' => $bedAndSector['bed']
                    ]);

                    if ($permReserve)
                        $buttons['reserve'] = View::render('gestaoleitos/solicitacao/buttons/button_reserve_bed');

                    if ($permChangeBed)
                        $buttons['changeBed'] = View::render('gestaoleitos/solicitacao/buttons/button_change_bed');

                    if ($permCancelPreparation)
                        $buttons['cancelPreparation'] = View::render('gestaoleitos/solicitacao/buttons/button_cancel_preparation');

                    if ($permFinish)
                        $buttons['finishReserve'] = View::render('gestaoleitos/solicitacao/buttons/button_finish_reserve');

                    return View::render('gestaoleitos/solicitacao/buttons_preparo', [
                        "group_bed_selected" => $buttons['group_beds'],
                        "reserveButton" => $buttons['reserve'] ?? "",
                        "changeBedButton" => $buttons['changeBed'] ?? "",
                        "cancelPreparationButton" => $buttons['cancelPreparation'] ?? "",
                        "finishReserveButton" => $buttons['finishReserve'] ?? ""
                    ]);
                    break;
                case 'L':
                    $bedAndSector =  self::getBedAndSectorChooseToSolicitation($idSolicitation);
                    $buttons['group_beds'] = View::render('gestaoleitos/solicitacao/group_bed_selected', [
                        'unidade-internacao' => $bedAndSector['sector'],
                        'leito-internacao' => $bedAndSector['bed']
                    ]);

                    if ($permFinish)
                        $buttons['finishReserve'] = View::render('gestaoleitos/solicitacao/buttons/button_finish_reserve');

                    return View::render('gestaoleitos/solicitacao/buttons_liberado', [
                        "group_bed_selected" => $buttons['group_beds'],
                        "finishReserveButton" => $buttons['finishReserve'] ?? ""
                    ]);

                    break;
            }
        }
    }

    public static function getBedAndSectorChooseToSolicitation($idSolicitation)
    {
        $bedAndSector = SolicitationModel::getBedAndSectorLiberateFromSolicitation($idSolicitation);

        $sector = Setor::getUnitByCode($bedAndSector['codigo_setor']);
        $bed = Leito::getBedByCode($bedAndSector['codigo_leito']);

        $hospitalizationOption = View::render('utils/option', [
            'nome' => $sector['nome'],
            'id' => $sector['codigo'],
            'selected' => "selected"
        ]);
        $bedOption = View::render('utils/option', [
            'nome' => $bed['nome'],
            'id' => $bed['codigo'],
            'selected' => "selected"
        ]);

        return ['bed' => $bedOption, "sector" => $hospitalizationOption];
    }

    /**
     * Método responsável por retornar a página da Solicitação
     * */
    public static function getSolicitation(Request $request, ?string $id = null, ?bool $edit = false)
    {
        if (is_null($id)) {
            $content = View::render('gestaoleitos/solicitacao', [
                'paciente' => self::getPatientSection(),
                'solicitacao' => self::getSolicitationSection($request)
            ]);
        } elseif ($edit) {
            if(!is_numeric($id))
                return ErrorController::getError('404', "Essa solicitação não existe.");

            $solicitation = SolicitationModel::getSolicitation($id);
            if($solicitation === false)
                return ErrorController::getError('404', "Essa solicitação não existe.");
            try{
                $content = View::render('gestaoleitos/solicitacao', [
                    'paciente' => self::getPatientSection($solicitation, true),
                    'solicitacao' => self::getSolicitationSection($request, $solicitation, true)
                ]);
            }
            catch(\Exception $e){
                return ErrorController::getError($e->getCode(), $e->getMessage());
            }
        } else {
            if(!is_numeric($id))
                return ErrorController::getError('404', "Essa solicitação não existe.");
                
            $solicitation = SolicitationModel::getSolicitation($id);
            if($solicitation === false)
                return ErrorController::getError('404', "Essa solicitação não existe.");

            $content = View::render('gestaoleitos/solicitacao', [
                'paciente' => self::getPatientSection($solicitation, true),
                'solicitacao' => self::getSolicitationSection($request, $solicitation)
            ]);
        }

        return parent::getPage('Gestão de Leitos | Visualização da Solicitação', 'gestaoleitos', $content, $request);
    }

    public static function getHospitalBeds(Request $request, string $unit)
    {
        $postVars = $request->getPostVars();
        $filters = $postVars['filters'];
        $ignoreIncompatible = $postVars['ignoreIncompatible'];

        $filters['gender'] = $filters['gender'] === 'Feminino'
            ? 'F' : ($filters['gender'] === 'Masculino' ? 'M' : 'O');

        $hospitalBeds = [];

        if ($ignoreIncompatible) {
            $hospitalBeds = Leito::getBedsByStatusAndUnit($unit, 'L');
        } else {
            switch (true) {
                case $filters['covid']:
                    $hospitalBeds = Leito::getCovidBedsByUnitAndAcc($unit, $filters['accommodation']);
                    break;
                case $filters['pediatric']:
                    $hospitalBeds = Leito::getPediatricBedsByUnitAndAcc($unit, $filters['accommodation']);
                    break;
                case strtoupper($filters['accommodation']) === 'ENF':
                    $hospitalBeds = Leito::getBedsByUnitAccAndGender($unit, $filters['accommodation'], $filters['gender']);
                    break;
                default:
                    $hospitalBeds = Leito::getBedsByUnitAndAcc($unit, $filters['accommodation']);
                    break;
            }
        }

        $options = [];

        foreach ($hospitalBeds as $bed) {
            $options[] = [
                'value' => trim($bed['leito_codigo']),
                'label' => trim($bed['leito_nome'])
            ];
        }

        return $options;
    }

    private static function getFormattedStatus($status)
    {
        switch (true) {
            case $status === 'A':
                return [
                    "label" => "Aberta",
                    "color" => "warning"
                ];
                break;

            case $status === 'P':
                return [
                    "label" => "Preparo",
                    "color" => "success"
                ];
                break;

            case $status === 'L':
                return [
                    "label" => "Liberado",
                    "color" => "primary"
                ];
                break;

            case $status === 'E':
                return [
                    "label" => "Encerrada",
                    "color" => "dark"
                ];
                break;

            case $status === 'RU' || $status === 'RU2':
                return [
                    "label" => "Admitido",
                    "color" => "success"
                ];
                break;

            case $status === 'RC' || $status === 'RC2':
                return [
                    "label" => "Reserva Encerrada",
                    "color" => "dark"
                ];
                break;
            case $status === 'C':
                return [
                    "label" => "Cancelada",
                    "color" => "danger"
                ];
                break;
            default:
                return [
                    "label" => "",
                    "color" => ""
                ];
                break;
        }
    }

    public static function getAllSectorsAndBedsWithoutDifference(Request $request)
    {
        $leitos = CommonsController::pullAdequateBeds($request);

        if (is_null($request->getPostVars()['filters']['sector']) or $request->getPostVars()['filters']['sector'] == "")
            return $leitos;

        $beds = [];
        foreach ($leitos as $leito) {
            $isBlocked = CommonsController::isBlocked($leito['leito_codigo']);
            if ($isBlocked)
                continue;

            $beds[] = $leito;
        }

        return $beds;
    }


    public static function createSolicitation(Request $request)
    {
        $result = [];
        $post = $request->getPostVars();

        $verifyResult = self::validateInputs($post, null);

        if ($verifyResult['succeeded'] !== true) {
            return $verifyResult;
        }

        $BedCaracteristics = array(
            "ignoreIncompatible" => 0,
            "covid" => $post['isCovid'],
            "sector" => null,
            "pediatric" => $post['solicitationPediatric'],
            "accommodationSolicited" => $post['solicitationAccommodation'],
            "gender" => $post['patientGender']
        );
        $leitoDisponiveis = [];
        $leitos = CommonsController::pullAdequateBeds(null, $BedCaracteristics);
        foreach ($leitos as $leito) {
            $isBlocked = CommonsController::isBlocked($leito['LOC_COD']);

            if ($isBlocked)
                continue;

            $leitoDisponiveis[] = $leito;
        }

        $result['status_leito'] = $leitoDisponiveis == [] ?  false : true;
        $idPatient = SolicitationModel::createPatient($post);
        if($idPatient === false)
            $result['succeeded'] = false;
        else{
            $post['idPatient'] = $idPatient;
            $idSolicitacao = SolicitationModel::createSolicitation($post);
            if ($idSolicitacao !== false) {

                $dateNow = new DateTime();
                $dateNow->setTimezone(new DateTimeZone(CURRENT_TIMEZONE));
                SolicitationModel::addAlteration($idSolicitacao, $request->user->id, $dateNow->format("Y-m-d H:i:s"), "A", 0);
    
                if (!empty($post['precaucoes']))
                    foreach ($post['precaucoes'] as $precaution)
                        SolicitationModel::setRisksAndPrecautionsInSolicitation($precaution, $idSolicitacao);
    
                if (!empty($post['riscos']))
                    foreach ($post['riscos'] as $risco)
                        SolicitationModel::setRisksAndPrecautionsInSolicitation($risco, $idSolicitacao);
    
                $result['idSOLICITACAO'] = $idSolicitacao;
                $result['succeeded'] = true;
            } else $result['succeeded'] = false;
        }

        return $result;
    }

    public static function verifyReservationToChange(Request $request)
    {
        $post = $request->getPostVars();

        $solicitation = SolicitationModel::getSolicitation($post['id']);
        if ($solicitation['SOLICITACAO_STATUS'] == "L") {
            // Inicialmente verifica se existe alguma reserva ativa para o paciente
            $reservations = Paciente::verifyReservation($post['registro']);
            if (count($reservations) == 0)
                return array(
                    "title" => "Reserva Inválida.",
                    "message" => "Paciente não possui nenhuma reserva ativa no sistema.",
                    "succeeded" => false
                );

            $codigo_reserva = SolicitationModel::getCodigoReserva($post['id'])['CODIGO_RESERVA'];
            //Se não existir é uma solicitação antiga e o recurso não ira funcionar
            if ($codigo_reserva == NULL)
                return array(
                    "title" => "Recurso indisponível!",
                    "message" => "O recurso de alteração de leito só é possivel em solicitações recentes.",
                    "succeeded" => false
                );

            // Se existir, verifica se existe reserva ativa com esse codigo de reserva. 
            $reservations = Paciente::verifyReservation($post['registro'], $codigo_reserva);

            // Se não for encontrado reserva significa que a reserva ativa do paciente possui um codigo mais recente ou seja
            // essa reserva foi usada/encerrada
            if (count($reservations) == 0)
                return array(
                    "title" => "Reserva Inválida.",
                    "message" => "Paciente possui reserva mais recente no sistema.",
                    "succeeded" => false
                );
        }

        $groupInputNewBed = View::render('gestaoleitos/solicitacao/new_leito_group_input', [
            'unidades-internacao' => self::getBedsToReservation(SolicitationModel::getSolicitation($post['id']))
        ]);
        return array(
            "succeeded" => true,
            "html" => $groupInputNewBed
        );
    }

    public static function getBedsToReservation($solicitation)
    {
        $resultSectors = [];
        $aux = 0;
        $hospitalizationOptions = "";

        $hospitalizationUnits = CommonsController::pullAdequateBeds(
            null,
            [
                "ignoreIncompatible" => 0,
                "filters" => [
                    "sector" => null,
                    "covid" => $solicitation['ISCOVID'],
                    "accommodation" => $solicitation['SOLICITACAO_ACOMODACAO'],
                    "pediatric" => $solicitation['SOLICITACAO_PED'],
                    "gender" => $solicitation['PACIENTE_SEXO']
                ]
            ]
        );

        foreach ($hospitalizationUnits as $bed) {
            if (in_array($bed["setor_codigo"], array_column($resultSectors, "setor_codigo")) == false) {
                $resultSectors[$aux]["setor_codigo"] = $bed["setor_codigo"];
                $resultSectors[$aux++]["setor_nome"] = $bed["setor_nome"];
            }
        }

        foreach ($resultSectors as $unit) {
            $hospitalizationOptions .= View::render('utils/option', [
                'nome' => $unit['setor_nome'],
                'id' => $unit['setor_codigo']
            ]);
        }

        return $hospitalizationOptions;
    }

    public static function returnCont()
    {
        $sql = "SELECT cnt_num FROM cnt WHERE ( cnt_tipo ='RLT' ) AND ( cnt_serie =0 )";

        $contagem = Leito::getContRLT()['cnt_num'] + 1;

        Leito::updateContRLT($contagem);

        return $contagem;
    }

    public static function preparateBed(Request $request)
    {
        $post = $request->getPostVars();
        $verifyResult = self::validateInputs($post, $post['mode']);
        if ($verifyResult['succeeded'] !== true) {
            return $verifyResult;
        }

        /* Confirmar se existe já existe um leito reservado ou bloqueado para o paciente X */
        $reservation = Paciente::getPacienteReservation($post['registro']);
        // Se ja houver uma reserva no smart
        if ($reservation !== false) {

            $codigo_rlt = $reservation['RLT_NUM'];

            // Liberar leito em preparação (bloqueado) se houver
            $blockBed = Leito::verifySMARTBed($post['lei']);

            // Se o leito estiver bloqueado, deixa-o livre
            if ($blockBed["BLC_STATUS"] == "B") {

                if (!Leito::CloseBlockSMARTBed($post['lei']))
                    return array(
                        "title" => "Erro",
                        "message" => "Erro na atualização do leito para fechá-lo no SMART.",
                        "succeeded" => false
                    );

                $newBlockBed = [
                    "bedCode" => $post['lei'],
                    "dthr_initial" => date("Y-m-d H:i:s"),
                    "status" => 'L',
                    "observation" => null,
                    "user" => "GLEITOS",
                    "reasonType" => null,
                    "reasonCode" => null,
                    "dthr_prev" => null
                ];
                Leito::openNewFreeBlockSMARTBed($newBlockBed);

                if (!Leito::setBedToFreeSMART($post['lei']))
                    return array(
                        "title" => "Erro",
                        "message" => "Erro na atualização do leito ao tentar deixá-lo livre no SMART.",
                        "succeeded" => true
                    );
            }

            $post["modo_atendimento"] = "L";
            $post["uni"] = trim($reservation["STR_COD"]);
            $post["lei"] = trim($reservation["LOC_COD"]);
            $post["data"] = $reservation["RLT_DTHR"];
            $post["user"] = trim($reservation["USR_NOME"]) . " Via SMART";

            SolicitationModel::addAlteration($post['id'], $post["user"], $post["data"], "P", 1, $post['lei'], $post['uni']);
            SolicitationModel::addAlteration($post['id'], $post["user"], $post["data"], "L", 1, $post['lei'], $post['uni'], $codigo_rlt);

            return array(
                "title" => "SMART com modificações recentes",
                "message" => "Já foi feita uma reserva no SMART e será atualizado no Gestão de Leitos / Unidade: " . Setor::getUnitByCode($post['uni'])['setor_nome'] . " --- Leito: " . Leito::getBedByCode($post['lei'])['nome'],
                "succeeded" => true
            );
        }
        // Não havendo reserva no smart
        elseif ($post['mode'] == "P") {
            // Preparando (bloqueando) Leito
            if (!Leito::finishBlockActiveOfBed($post['lei']))
                return array(
                    "title" => "Erro",
                    "message" => "Erro na atualização do leito para encerrar o bloqueio no SMART.",
                    "succeeded" => false
                );

            $newBlockBed = [
                "bedCode" => $post['lei'],
                "dthr_initial" => 'GETDATE()',
                "status" => 'B',
                "observation" => 'PREPARAÇÃO ID: ' . $post["id"] . ' REGISTRO: ' . $post["registro"] . ' (S:' . $post["gender"] . ')',
                "user" => 'GLEITOS',
                "reasonType" => null,
                "reasonCode" => null,
                "dthr_prev" => null
            ];
            Leito::openNewFreeBlockSMARTBed($newBlockBed);

            if (!Leito::setBedToBlockSMART($post['id'], $post['lei'], $post['registro'], $post['gender']))
                return array(
                    "title" => "Erro",
                    "message" => "Erro na atualização do leito para bloquear o leito no SMART.",
                    "succeeded" => false
                );

            SolicitationModel::addAlteration($post['id'], $request->user->id, date("Y-m-d H:i:s"), "P", 0, $post['lei'], $post['uni']);
            $newPreparationBed = [
                "status" => "P",
                "id" => $post['id']
            ];
            SolicitationModel::prepareBed($newPreparationBed);

            return array(
                "title" => "Preparação realizada com sucesso!",
                "succeeded" => true
            );
        } else {

            // Reservando Leito
            $leitoBLC = Leito::verifySMARTBed($post['lei']);

            // Se o leito que esta sendo reservado esta em preparação (bloqueado)
            if ($leitoBLC["BLC_STATUS"] == "B") {

                if (!Leito::startBlockActiveOfBed($post['lei']))
                    return array(
                        "title" => "Erro",
                        "message" => "Erro na atualização do leito para realizar o bloqueio no SMART.",
                        "succeeded" => false
                    );


                $newBlockBed = [
                    "bedCode" => $post['lei'],
                    "dthr_initial" => 'GETDATE()',
                    "status" => 'L',
                    "observation" => null,
                    "user" => 'GLEITOS',
                    "reasonType" => null,
                    "reasonCode" => null,
                    "dthr_prev" => null
                ];
                Leito::closeNewFreeBlockSMARTBed($newBlockBed);

                if (!Leito::setBedToFreeSMART($post['lei']))
                    return array(
                        "message" => "Erro na atualização do leito para bloquear o leito no SMART.",
                        "succeeded" => false
                    );
            }

            $cont = self::returnCont();
            if (!Leito::reserveBed($post['lei']))
                return array(
                    "title" => "Erro",
                    "message" => "Erro na atualização do leito para realizar a reserva no SMART.",
                    "succeeded" => false
                );

            $newReserveBed = [
                "bedCode" => $post['lei'],
                "sector" => $post['uni'],
                "contagem" => $cont
            ];
            Leito::createReserveBedInRLT($newReserveBed);

            if (!Leito::putPatientInNewReservedBed($post['registro'], $post['lei']))
                return array(
                    "title" => "Erro",
                    "message" => "Erro na atualização do leito para colocar o paciente no leito reservado SMART.",
                    "succeeded" => false
                );

            // return Leito::putPatientInNewRLTBed($post['registro'], $post['uni'], $cont);
            if (!Leito::putPatientInNewRLTBed($post['registro'], $post['uni'], $cont))
                return array(
                    "title" => "Erro",
                    "message" => "Erro na atualização do leito para colocar o paciente no RLT SMART.",
                    "succeeded" => false
                );

            
            SolicitationModel::addAlteration($post['id'], $request->user->id, date("Y-m-d H:i:s"), "L", 0, $post['lei'], $post['uni'], $cont);
            $updateSolicitation = [
                "id" => $post['id'],
                "status" => "L"
            ];
            SolicitationModel::liberateBed($updateSolicitation);

            return array(
                "title" => "Liberação realizada com sucesso!",
                "succeeded" => true
            );
        }
    }

    public static function cancelSolicitation(Request $request)
    {
        $idSolicitation = $request->getPostVars()['id'];
        SolicitationModel::addAlteration($idSolicitation, $request->user->id, date("Y-m-d H:i:s"), "C", 0);
        $cancelBed = [
            "id" => $idSolicitation,
            "status" => 'C'
        ];

        if (!SolicitationModel::cancelSolicitation($cancelBed))
            return array(
                "message" => "Erro na cancelação do leito.",
                "succeeded" => false
            );

        return array(
            "message" => "Solicitação cancelada com sucesso!",
            "succeeded" => true
        );
    }

    public static function verifyReserve($registro, $idSolicitation)
    {
        // Inicialmente verifica se existe alguma reserva ativa para o paciente
        $reservation = Paciente::verifyReservation($registro);
        // Se não for encontrado nenhuma reserva ativa...
        if (count($reservation) == 0)
            return array(
                "title" => "Reserva invalida!",
                "message" => "Paciente não possui nenhuma reserva ativa no sistema.",
                "succeeded" => false
            );

        // Existindo reserva ativa...
        // Verifica se existe codigo de reserva na solicitação.
        $codigo_reserva = SolicitationModel::getCodigoReserva($idSolicitation)['CODIGO_RESERVA'];
        //Se não existir é uma solicitação antiga e o recurso não ira funcionar
        if (is_null($codigo_reserva))
            return array(
                "title" => "Recurso indisponivel!",
                "message" => "Recurso não disponivel para solicitações antigas.",
                "succeeded" => false
            );

        // Se existir, verifica se existe reserva ativa com esse codigo de reserva.
        $reservation = Paciente::verifyReservation($registro, $codigo_reserva);
        // Se não for encontrado reserva significa que a reserva ativa do paciente possui um codigo mais recente ou seja
        // essa reserva foi usada/encerrada
        if (count($reservation) == 0)
            return array(
                "title" => "Reserva invalida!",
                "message" => "Paciente possui reserva mais recente no sistema.",
                "succeeded" => false
            );

        // Se foi encontrada, é a reserva atual e valida do paciente
        return array(
            "succeeded" => true
        );
    }

    public static function confirmChangeReserve(Request $request)
    {
        $post = $request->getPostVars();

        // Verifica novamente se a reserva é valida
        $resposta = self::verifyReserve($post['registro'], $post['id']);
        $status = SolicitationModel::getSolicitation($post['id'])['SOLICITACAO_STATUS'];
        if (!$resposta['succeeded'] && $status == "L")
            return $resposta;
        else {

            // Verifica se o leito destino está livre
            $bed = Leito::getBedByCode($post['newBed']);
            if ($bed['status'] != 'L')
                return [
                    "title" => "Ops",
                    "succeeded" => false
                ]; // Leito destino não esta livre

            //Atualiza reserva
            $newReserveBed = [
                "newSector" => $post['newSector'],
                "oldSector" => $post['oldSector'],
                "newBed" => $post['newBed'],
                "oldBed" => $post['oldBed'],
                "registro" => $post['registro']
            ];
            $rowsAffecteds = Leito::updateReserve($newReserveBed);
            if ($rowsAffecteds != 1)
                return [
                    "title" => "Ops",
                    "succeeded" => false
                ];    // Erro ao atualizar Reserva. Nenhuma reserva encontrada

            // Atualiza estados dos leitos
            $rowsAffecteds = Leito::updateBedStatus($post['oldBed']);
            if ($rowsAffecteds != 1)
                return [
                    "title" => "Ops",
                    "succeeded" => false
                ];    // Erro ao atualizar Reserva. Nenhuma reserva encontrada

            $rowsAffecteds = Leito::updateNewBedChanged($post['registro'], $post['newBed']);
            if ($rowsAffecteds != 1)
                return [
                    "title" => "Ops",
                    "succeeded" => false
                ];    // Erro ao atualizar Reserva. Nenhuma reserva encontrada

            SolicitationModel::addAlteration($post['id'], $request->user->id, date("Y-m-d H:i:s"), 'CLB', 0, $post['newBed'], $post['newSector']);

            return [
                "title" => "Alteração realizada com sucesso",
                "succeeded" => true
            ];
        }
    }

    public static function finishReserve(Request $request)
    {
        $post = $request->getPostVars();

        // Verifica se a reserva é valida			
        $resposta = self::verifyReserve($post['registro'], $post['id']);
        $reservation = Paciente::getPacienteReservation($post['registro']);

        if (!$resposta['succeeded'])
            return $resposta;
        elseif ($reservation !== false) {

            $codigo_rlt = $reservation['RLT_NUM'];

            // Liberar leito em preparação (bloqueado) se houver
            $blockBed = Leito::verifySMARTBed($post['lei']);

            // Se o leito estiver bloqueado, deixa-o livre
            if ($blockBed["BLC_STATUS"] == "B") {

                if (!Leito::CloseBlockSMARTBed($post['lei']))
                    return array(
                        "message" => "Erro na atualização do leito para fechá-lo no SMART.",
                        "succeeded" => false
                    );

                $newBlockBed = [
                    "bedCode" => $post['lei'],
                    "dthr_initial" => date("Y-m-d H:i:s"),
                    "status" => 'L',
                    "observation" => null,
                    "user" => "GLEITOS",
                    "reasonType" => null,
                    "reasonCode" => null,
                    "dthr_prev" => null
                ];
                Leito::openNewFreeBlockSMARTBed($newBlockBed);

                if (!Leito::setBedToFreeSMART($post['lei']))
                    return array(
                        "message" => "Erro na atualização do leito ao tentar deixá-lo livre no SMART.",
                        "succeeded" => true
                    );
            }

            $post["modo_atendimento"] = "L";
            $post["uni"] = trim($reservation["STR_COD"]);
            $post["lei"] = trim($reservation["LOC_COD"]);
            $post["data"] = $reservation["RLT_DTHR"];
            $post["user"] = trim($reservation["USR_NOME"]) . " Via SMART";

            SolicitationModel::addAlteration($post['id'], $post["user"], $post["data"], "P", 1, $post['lei'], $post['uni']);
            SolicitationModel::addAlteration($post['id'], $post["user"], $post["data"], "L", 1, $post['lei'], $post['uni'], $codigo_rlt);

            return array(
                "message" => "Já foi feita uma reserva no SMART e será atualizado no Gestão de Leitos / Unidade: " . Setor::getUnitByCode($post['uni'])['setor_nome'] . " --- Leito: " . Leito::getBedByCode($post['lei'])['nome'],
                "succeeded" => true
            );
        } else {

            $codigo_reserva = SolicitationModel::getCodigoReserva($post['id'])['CODIGO_RESERVA'];

            // Atualiza status da reserva para CANCELADA
            $rowsAffecteds = Leito::cancelReserve($post['registro'], $codigo_reserva);

            if ($rowsAffecteds != 1)
                return [
                    "title" => "Ops",
                    "succeeded" => false
                ];

            // Atualiza o estado do leito para livre
            $rowsAffecteds = Leito::updateBedStatus($post['bedCode']);

            if ($rowsAffecteds != 1)
                return [
                    "title" => "Ops",
                    "succeeded" => false
                ];

            $dateFinish = date("Y-m-d H:i:s");
            SolicitationModel::addAlteration($post['id'], $request->user->id, $dateFinish, "E", 0);
            $rowsAffecteds = SolicitationModel::cancelReserveInMySQL($post['id']);

            /* Principal do rowCounte() */
            if ($rowsAffecteds != 1)
                return [
                    "title" => "Ops",
                    "succeeded" => false
                ];

            return [
                "title" => "Reserva Encerrada",
                "succeeded" => true
            ];
        }
    }

    public static function cancelPreparation(Request $request)
    {
        $post = $request->getPostVars();

        if (!Leito::CloseBlockSMARTBed($post['bedCode']))
            return [
                "title" => "Ops",
                "succeeded" => false
            ];

        $newBlockBed = [
            "bedCode" => $post['bedCode'],
            "dthr_initial" => 'GETDATE()',
            "status" => 'L',
            "observation" => null,
            "user" => 'GLEITOS',
            "reasonType" => null,
            "reasonCode" => null,
            "dthr_prev" => null
        ];
        if (Leito::openNewFreeBlockSMARTBed($newBlockBed) === false)
            return [
                "title" => "Ops",
                "succeeded" => false
            ];

        if (!Leito::setBedToFreeSMART($post['bedCode']))
            return [
                "title" => "Ops",
                "succeeded" => false
            ];


        if (!SolicitationModel::addAlteration($post['id'], $request->user->id, date("Y-m-d H:i:s"), "CP", 0))
            return [
                "title" => "Ops",
                "succeeded" => false
            ];
        if (!SolicitationModel::cancelPreparation($post['id']))
            return [
                "title" => "Ops",
                "succeeded" => false
            ];


        return [
            "title" => "Preparação cancelada com sucesso!",
            "succeeded" => true
        ];
    }

    public static function editSolicitation(Request $request)
    {
        $post = $request->getPostVars();

        $array_type_status = array(
            "C" => "A solicitação foi cancelada",
            "L" => "A solicitação foi liberada",
            "P" => "O leito foi preparado",
            "E" => "A solicitação foi encerrada",
            "RC" => "A reserva foi cancelada",
            "RC2" => "A reserva foi cancelada",
            "RU" => "O paciente foi admitido",
            "RU2" => "O paciente foi admitido"
        );

        $solicitation_status = SolicitationModel::getSolicitation($post['id'])['SOLICITACAO_STATUS'];

        if ($solicitation_status != "A")
            return array(
                "title" => "Ocorreu um problema ao editar a solicitação!",
                "message" => $array_type_status[$solicitation_status] . " antes da edição ser confirmada.",
                "succeeded" => false
            );

        else {

            SolicitationModel::addAlteration($post['id'], $request->user->id, date("Y-m-d H:i:s"), "EDT", 0);
            SolicitationModel::editSolicitation($post);

            return array(
                "title" => "Solicitação editada com sucesso!",
                "succeeded" => true
            );
        }
    }

    public static function getHistoricoModal(Request $request)
    {
        $labels = SolicitationModel::getLabelsFromHistoric($request->getPostVars()['id']);
        $events = SolicitationModel::getHistoricoAtendimentoFromNewSolicitation($request->getPostVars()['id']);

        $allGroupsHTML = "";
        foreach ($labels as $label) {
            $allGroupsHTML .= View::render('gestaoleitos/solicitacao/historico/date-label-timeline', [
                "date" => (new DateTime($label['datesActions']))->format("d/m/Y")
            ]);

            foreach ($events as $event) {
                if ((new DateTime($event['dateAction']))->format("Y-m-d") == $label['datesActions']) {
                    $statusInfo = CommonsController::getActionTimeLine($event['statusChange']);
                    $allGroupsHTML .= View::render('gestaoleitos/solicitacao/historico/group-timeline-item', [
                        "icon" => $statusInfo['icon'],
                        "background-color" => $statusInfo['color'],
                        "hour" => (new DateTime($event['dateAction']))->format("H:i"),
                        "username" => !is_null($event['usuario']) ? CommonsController::getUsername($event['usuario']) : $event['SMARTUsername'],
                        "action" => $statusInfo['label'],
                        "has-body" => is_null($event['leito']) ? 'no-border' : "",
                        "body-timeline" => is_null($event['leito']) ? "" :
                            View::render('gestaoleitos/solicitacao/historico/body-group-bed-timeline', [
                                'sector' => Setor::getUnitByCode($event['setor'])['nome'],
                                'bed' => View::render('gestaoleitos/utils/bed', [
                                    'responsive-classes' => "",
                                    'color' => $statusInfo['color'],
                                    'leito-codigo' => $event['leito'],
                                    "icon" => "check",
                                    "icon-tipo-leito" => "fas fa-bed",
                                    "leito" => Leito::getBedByCode($event['leito'])['nome']
                                ])
                            ])
                    ]);
                }
            }
        }

        return View::render('gestaoleitos/solicitacao/historico', [
            'groups-itens-timeline' => $allGroupsHTML
        ]);
    }
}
