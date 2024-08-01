<?php

namespace App\Controller\GestaoLeitos;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Controller\Utils\UnitController;
use App\Http\Request;
use App\Model\GestaoLeitos\Solicitacao;
use App\Model\Utils\Setor;
use DateTime;
use DateTimeZone;
use IntlDateFormatter;

class IndicatorsController extends LayoutPage
{
    const profiles = [
        'CL' => 'Clínico',
        'CR' => 'Cirurgico',
        'OU' => 'Outro',
    ];

    const accomodations = [
        'APT' => 'Apartamento',
        'ENF' => 'Enfermaria',
        'UTI' => 'UTI',
    ];
    /**
     * Método responsável por retornar os cards da página de indicadores
     * @return string Retorna os cards da página de indicadores 
     */
    private static function getCards()
    {
        $solicitations = Solicitacao::getAllSolicitationsWithoutHistoric();
        $solicitationCount = [];
        foreach ($solicitations as $solicitation) {
            $solicitationCount[$solicitation['status']][] = $solicitation;
        }
        
        $openSolicitation = count($solicitationCount['A'] ?? []);
        $prepareSolicitation = count($solicitationCount['P'] ?? []);
        $releasedSolicitation = count($solicitationCount['L'] ?? []);
        $canceledSolicitation = count($solicitationCount['C'] ?? []);

        $totalSolicitation = $openSolicitation + $releasedSolicitation +
            $prepareSolicitation + $canceledSolicitation;

        return View::render('gestaoleitos/indicators/cards', [
            'solicitacao-total' => $totalSolicitation,
            'solicitacao-aberto' => $openSolicitation,
            'solicitacao-liberada' => $releasedSolicitation,
            'solicitacao-atendimento' => $prepareSolicitation,
            'solicitacao-cancelada' => $canceledSolicitation,
        ]);
    }
    /**
     * Método responsável por retornar a página de indicadores
     * 
     * @param Request $request
     * @return string Página de indicadores
     */
    public static function getIndicators(Request $request): string
    {
        $dataPoints = self::getDataPoints($request);
        $content = View::render('gestaoleitos/indicators', [
            'cards' => self::getCards(),
        ]);

        return parent::getPage(
            'Gestão de Leitos | Indicadores',
            'gestaoleitos',
            $content,
            $request
        );
    }

    /**
     * Método responsáel por retornar as solicitações por setores
     * @param array $solicitations Solicitações
     * @return array Datapoints para os gráficos
     */
    private static function getSolicitationsByUnit(array $solicitations)
    {
        $solicitationUnits = [];
        $units = Setor::getStores();

        foreach ($solicitations as $solicitation) {
            $unit = trim($solicitation['setor']);
            $unitIndex = array_search($unit, array_column($units, 'codigo'));
            $solicitationUnits[$unit]['label'] = trim($units[$unitIndex]->nome);
            $solicitationUnits[$unit]['y']++;
        }

        return array_values($solicitationUnits);
    }

    /**
     * Método responsável por retornar a quantidade de solicitações por perfil 
     * (clinico, cicrurgico, outro)
     * @param array $solicitations Solicitações
     * @return array Datapoints para os gráficos
     */
    private static function getSolicitationProfile(array $solicitations)
    {
        $solicitationProfiles = [];

        foreach ($solicitations as $solicitation) {
            $solicitationProfile = strtoupper(trim($solicitation['perfil']));

            if (!$solicitationProfile)
                continue;

            $profile = self::profiles[$solicitationProfile];

            $solicitationProfiles[$solicitationProfile]['label'] = $profile;
            $solicitationProfiles[$solicitationProfile]['y']++;
        }
        return array_values($solicitationProfiles);
    }

    /**
     * Método responsável por retornar a quantidade de solicitações por acomodação
     * @param array $solicitations Solicitações 
     * @return array Datapoints para os gráficos
     */
    private static function getSolicitationAccomodation(array $solicitations)
    {

        $solicitationAccomodations = [];

        foreach ($solicitations as $solicitation) {
            $accomodation = strtoupper(trim($solicitation['acomodacao']));

            if (!$accomodation)
                continue;

            $solicitationAccomodations[$accomodation]['label'] = self::accomodations[$accomodation];
            $solicitationAccomodations[$accomodation]['y']++;
        }

        return array_values($solicitationAccomodations);
    }

    /**
     * Método responsável por retornar a quantidade de solicitações por hora
     * @param array $solicitations Solicitações
     * @return array Datapoints para os gráficos
     */
    private static function getSolicitationsByHour(array $solicitations)
    {

        $solicitationHours = array_map(function ($n) {
            $label = str_pad($n - 1, 2, '0', STR_PAD_LEFT) . "h";
            return [
                'label' => $label,
                'y' => 0,
            ];
        }, range(1, 24));

        $currentTimeZone = new DateTimeZone(CURRENT_TIMEZONE);
        foreach ($solicitations as $solicitation) {
            $date = new DateTime($solicitation['data_solicitacao'], $currentTimeZone);
            $index = $date->format("H");
            $solicitationHours[$index]['y']++;
        }
        return array_values($solicitationHours);
    }

    /**
     * Método responsável por retornar as solicitações dos setores por hora do dia
     * @param array $solicitations Solicitações
     * @return array Datapoints para os gráficos
     */
    private static function getSolicitationsByHoursUnit(array $solicitations)
    {
        $unitsCode = array_unique(array_column($solicitations, 'setor'));
        $unitsCode = implode("','", $unitsCode);
        $units = Setor::getUnitsByCode($unitsCode);
        $currentTimeZone = new DateTimeZone(CURRENT_TIMEZONE);

        $solicitationHours = array_map(function ($n) {
            $label = str_pad($n - 1, 2, '0', STR_PAD_LEFT) . "h";
            return [
                'label' => $label,
                'y' => 0,
                'x' => $n,
            ];
        }, range(1, 24));

        $solicitationsData = array_map(function ($unit) use ($solicitationHours) {
            return $solicitationHours;
        }, $units);

        $solicitationsData = array_combine(
            array_map('trim', array_column($units, 'codigo')),
            $solicitationsData
        );

        $data = [];
        $mostSolicitation = [];

        foreach ($solicitations as $solicitation) {
            $unit = trim($solicitation['setor']);
            $unitIndex = array_search($unit, array_column($units, 'codigo'));
            if ($unitIndex === false)
                continue;
            $date = new DateTime($solicitation['data_solicitacao'], $currentTimeZone);
            $index = $date->format('H');
            $solicitationsData[$unit][$index]['y']++;

            $mostSolicitation[$unit]++;
            
            $data[$unit] = [
                'name' => trim($units[$unitIndex]->nome),
                'type' => 'spline',
                'showInLegend' => true,
                'dataPoints' => $solicitationsData[$unit],
            ];
            
        }
        arsort($mostSolicitation);
        $mostSolicitation = array_slice($mostSolicitation, 0, 10, true);

        return array_values(array_intersect_key($data, $mostSolicitation));
    }
    /**
     * Método responsável por retornar a quantidade de solicitações por tempo médio de atendimento
     * @param array $solicitations Solicitações
     * @return array Datapoints para os gráficos
     */
    private static function getAttendanceTime(array $solicitations)
    {
        $unitsCode = array_unique(array_column($solicitations, 'setor'));
        $unitsCode = implode("','", $unitsCode);
        $units = Setor::getUnitsByCode($unitsCode);
        $currentTimeZone = new DateTimeZone(CURRENT_TIMEZONE);

        $mostSolicitation = [];
        $solicitationsData = [];

        foreach ($solicitations as $solicitation) {
            $unit = trim($solicitation['setor']);
            $unitIndex = array_search($unit, array_column($units, 'codigo'));
            if ($unitIndex === false)
                continue;

            if (is_null($solicitation['data_atendimento']))
                continue;

            $createDate = new DateTime($solicitation['data_solicitacao'], $currentTimeZone);
            $attendanceDate = new DateTime($solicitation['data_atendimento'], $currentTimeZone);

            $diff = $createDate->diff($attendanceDate);
            $seconds = $diff->days * 24 * 60 + $diff->h * 60 + $diff->i * 60 + $diff->s;

            $solicitationsData[$unit]['label'] = trim($units[$unitIndex]->nome);
            $solicitationsData[$unit]['y'] += $seconds;
            $profile = self::profiles[trim(strtoupper($solicitation['perfil']))];
            $acommodation = self::accomodations[trim(strtoupper($solicitation['acomodacao']))];
            $solicitationsData[$unit]['profiles'][$profile] += $seconds;
            $solicitationsData[$unit]['accomodations'][$acommodation] += $seconds;

            $mostSolicitation[$unit]++;
        }
        arsort($mostSolicitation);
        $mostSolicitation = array_slice($mostSolicitation, 0, 10, true);
        $solicitationsData = array_intersect_key($solicitationsData, $mostSolicitation);
        
        
        $data = array_map(function ($key, $value) use ($mostSolicitation) {
            $countSolicitation = $mostSolicitation[$key];
            $time = ($value['y'] / $countSolicitation) / 60;

            return [
                'profiles' => array_map(function ($key, $value) use ($countSolicitation) {
                    $time = ($value / $countSolicitation) / 60;
                    return [
                        'label' => $key,
                        'y' => floor($time),
                    ];
                }, array_keys($value['profiles']), array_values($value['profiles'])),
                'accomodations' => array_map(function ($key, $value) use ($countSolicitation) {
                    $time = ($value / $countSolicitation) / 60;
                    return [
                        'label' => $key,
                        'y' => floor($time) ,
                    ];
                }, array_keys($value['accomodations']), array_values($value['accomodations'])),
                'label' => $value['label'],
                'y' => floor($time),
            ];
        }, array_keys($mostSolicitation), $solicitationsData);
        
        return array_values($data);
    }

    /**
     * Método responsável por retornar a quantidade de solicitações por tempo médio de liberação
     * @param array $solicitations Solicitações
     * @return array Datapoints para os gráficos
     */
    private static function getReleaseTime(array $solicitations)
    {
        $unitsCode = array_unique(array_column($solicitations, 'setor'));
        $unitsCode = implode("','", $unitsCode);
        $units = Setor::getUnitsByCode($unitsCode);
        $currentTimeZone = new DateTimeZone(CURRENT_TIMEZONE);

        $mostSolicitation = [];
        $solicitationsData = [];

        foreach ($solicitations as $solicitation) {
            $unit = trim($solicitation['setor']);
            $unitIndex = array_search($unit, array_column($units, 'codigo'));
            if ($unitIndex === false)
                continue;

            if (is_null($solicitation['data_liberacao']))
                continue;

            $createDate = new DateTime($solicitation['data_solicitacao'], $currentTimeZone);
            $attendanceDate = new DateTime($solicitation['data_liberacao'], $currentTimeZone);

            $diff = $createDate->diff($attendanceDate);
            $seconds = $diff->days * 24 * 60 + $diff->h * 60 + $diff->i * 60 + $diff->s;

            $solicitationsData[$unit]['label'] = trim($units[$unitIndex]->nome);
            $solicitationsData[$unit]['y'] += $seconds;
            $profile = self::profiles[trim(strtoupper($solicitation['perfil']))];
            $acommodation = self::accomodations[trim(strtoupper($solicitation['acomodacao']))];
            $solicitationsData[$unit]['profiles'][$profile] += $seconds;
            $solicitationsData[$unit]['accomodations'][$acommodation] += $seconds;

            $mostSolicitation[$unit]++;
        }
        arsort($mostSolicitation);
        $mostSolicitation = array_slice($mostSolicitation, 0, 10, true);
        $solicitationsData = array_intersect_key($solicitationsData, $mostSolicitation);

        $data = array_map(function ($key, $value) use ($mostSolicitation) {
            $countSolicitation = $mostSolicitation[$key];
            $time = ($value['y'] / $countSolicitation) / 60;

            return [
                'profiles' => array_map(function ($key, $value) use ($countSolicitation) {
                    $time = ($value / $countSolicitation) / 60;
                    return [
                        'label' => $key,
                        'y' => floor($time),
                    ];
                }, array_keys($value['profiles']), array_values($value['profiles'])),
                'accomodations' => array_map(function ($key, $value) use ($countSolicitation) {
                    $time = ($value / $countSolicitation) / 60;
                    return [
                        'label' => $key,
                        'y' => floor($time),
                    ];
                }, array_keys($value['accomodations']), array_values($value['accomodations'])),
                'label' => $value['label'],
                'y' => floor($time),
            ];
        }, array_keys($mostSolicitation), $solicitationsData);

        return array_values($data);
    }

    public static function normalizeSolicitations($solicitations)
    {
        $solicitationsNormalized = [];
        foreach ($solicitations as $solicitation) {
            $solicitationsNormalized[$solicitation['id']]['perfil'] = $solicitation['perfil'];
            $solicitationsNormalized[$solicitation['id']]['acomodacao'] = $solicitation['acomodacao'];
            $solicitationsNormalized[$solicitation['id']]['status_atual'] = $solicitation['status'];
            $solicitationsNormalized[$solicitation['id']]['setor'] = $solicitation['setor'];

            $dthr_mudanca = "";
            if($solicitation['status_mudanca'] == "A")
                $dthr_mudanca = "data_solicitacao";
            elseif ($solicitation['status_mudanca'] == "P")
                $dthr_mudanca = "data_atendimento";
            elseif ($solicitation['status_mudanca'] == "L")
                $dthr_mudanca = "data_liberacao";

            $solicitationsNormalized[$solicitation['id']][$dthr_mudanca] = $solicitation['data_mudanca'];
        }

        return $solicitationsNormalized;
    }


    /**
     * Método responsável por retornar os dados do gráficos da página de 
     * indicadores
     * @param Request $request Requisição do usuário
     * @return array Datapoints para os gráficos
     */
    public static function getDataPoints(Request $request): array
    {
        $solicitations = self::normalizeSolicitations(Solicitacao::getAllSolicitations());
        $solicitationsWithoutHistoric = Solicitacao::getAllSolicitationsWithoutHistoric();

        return [
            'solicitation-units' => self::getSolicitationsByUnit($solicitationsWithoutHistoric),
            'solicitation-profiles' => self::getSolicitationProfile($solicitationsWithoutHistoric),
            'solicitation-accomodations' => self::getSolicitationAccomodation($solicitationsWithoutHistoric),
            'solicitation-hours' => self::getSolicitationsByHour($solicitationsWithoutHistoric),
            'solicitation-hours-unit' => self::getSolicitationsByHoursUnit($solicitationsWithoutHistoric),
            'time-attendance' => self::getAttendanceTime($solicitations),
            'time-release' => self::getReleaseTime($solicitations),
        ];
    }
}
