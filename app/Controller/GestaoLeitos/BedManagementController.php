<?php

namespace App\Controller\GestaoLeitos;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use \App\Controller\GestaoLeitos\CommonsController;
use App\Http\Request;
use App\Model\GestaoLeitos\Leito;
use App\Model\GestaoLeitos\Paciente;
use App\Model\GestaoLeitos\Setor;
use DateTime;
use DateTimeZone;
use Exception;

class BedManagementController extends LayoutPage
{
    /**
     * Método responsável por retornar a página de Gereciamento de leitos
     * 
     * @param Request $request Requisição do usuário
     * @return string HTML da página
     */
    public static function getBedManagement(Request $request): string
    {
        $unities = "";
        $hospitalzationUnits = Setor::getHospitalizationUnits();
        $hospitalzationUnits = CommonsController::getCodeAndUnits($hospitalzationUnits);

        foreach ($hospitalzationUnits as $code => $unity) {
            $unities .= View::render('utils/option', [
                "nome" => $unity,
                "id" => $code,
                "selected" => "",
            ]);
        }

        $unityCode = key($hospitalzationUnits);
        $beds = self::getBeds($request, $unityCode);

        $content = View::render('gestaoleitos/bed_management', [
            'setores' => $unities,
            'leitos' => $beds
        ]);

        return parent::getPage('Gestão de Leitos | Gerenciador de Leitos', 'gestaoleitos', $content, $request);
    }

    /**
     * Método responsável por retornar cards de leitos de uma unidade e seus modais
     * 
     * @param Request $request Requisição do usuário
     * @param string $unity Código da unidade
     * @return string Leitos da unidade em HTML
     */
    public static function getBeds(Request $request, string $unity): string
    {

        $beds = Leito::getAllBedsByUnit($unity);
        $blockedBeds = Leito::getBlockedBeds();
        $blockedBeds = array_column($blockedBeds, 'codigo_leito');
        $bedsOptions = '';

        $order = array('L', 'R', 'B', 'O');

        usort($beds, function ($a, $b) use ($order) {
            $pos_a = array_search($a['status'], $order);
            $pos_b = array_search($b['status'], $order);

            return $pos_a - $pos_b;
        });

        foreach ($beds as $bed) {
            $isBlocked = in_array(trim($bed['codigo']), $blockedBeds);

            if (!$isBlocked && trim($bed['status']) === 'L')
                $bed['status'] = 'P';

            $bedsOptions .= View::render('gestaoleitos/utils/bed', [
                'responsive-classes' => "col-6 col-md-4 col-lg-2 col-xl-1",
                'color' => BedPanelController::statusColor[$bed['status']],
                'icon' => BedPanelController::statusIcon[$bed['status']],
                'leito' => BedPanelController::formatBedName($bed['nome']),
                'leito-codigo' => trim($bed['codigo']),
                'tipo-leito' => strtoupper($bed['tipo']) === 'NORMAL' ? 'Normal' : 'Virtual',
                'icon-tipo-leito' => strtoupper($bed['tipo']) === 'NORMAL' ? 'fas fa-circle' : 'fas fa-circle-notch',
            ]);
        }

        return $bedsOptions;
    }

    /**
     * Método responsável por retornar o modal de um leito com
     * com as opções de bloquear e desbloquear o leito
     * 
     * @param Request $request Requisição do usuário
     * @param string $bed Código do leito
     * @return string Modal do leito em HTML
     */
    public static function getBedModal(Request $request, string $bed): string
    {
        $bed = Leito::getBedByCode($bed);
        $patient = Paciente::getPatientByBed(trim($bed['codigo']));
        $unity = Setor::getUnitByCode(trim($bed['setor_codigo']));
        $currentTimezone = new DateTimeZone(CURRENT_TIMEZONE);
        $content = '';

        switch ($bed['status']) {
            case 'O':
                $occupationDate = Leito::getOccupationBedDate($bed['codigo'], $patient['registro']);
                $occupationDate = new DateTime($occupationDate, $currentTimezone);
                $todayDate = new Datetime('now', $currentTimezone);
                $diff = $occupationDate->diff($todayDate);

                $occupationDays = $diff->format('%a');
                $occupationHours = $diff->format('%h');

                $birthdate = new DateTime($patient['data_nascimento'], $currentTimezone);
                $age = $birthdate->diff($todayDate)->format('%y');

                $content = "Registro: " . $patient['registro'] . "<br>Sexo: " . ($patient['sexo'] == "M" ? "Masculino" : ($patient['sexo'] == "F" ? "Feminino" : "Outro")) . " <br>Idade: " . $age . " anos<br>Tempo de ocupação: " . $occupationDays . " dias e " . $occupationHours . " horas";
                break;
            case 'R':
                $reservationDate = Leito::getReservationBedDate($bed['codigo'], $patient['registro']);
                $reservationDate = new DateTime($reservationDate, $currentTimezone);
                $todayDate = new Datetime('now', $currentTimezone);
                $diff = $reservationDate->diff($todayDate);

                $reservationDays = $diff->format('%a');
                $reservationHours = $diff->format('%h');

                $birthdate = new DateTime($patient['data_nascimento'], $currentTimezone);
                $age = $birthdate->diff($todayDate)->format('%y');

                $content = "Registro: " . $patient['registro'] . "<br>Sexo: " . ($patient['sexo'] == "M" ? "Masculino" : ($patient['sexo'] == "F" ? "Feminino" : "Outro")) . " <br>Idade: " . $age . " anos<br>Tempo de reserva: " . $reservationDays . " dias e " . $reservationHours . " horas";

                break;
            case 'B':
                $blockBedInfo = Leito::getBlockBedInfo($bed['codigo']);
                if (is_null($blockBedInfo['data'])) {
                    $content = "Leito nunca usado";
                    break;
                }

                $blockDate = new DateTime($blockBedInfo['data'], $currentTimezone);
                $todayDate = new Datetime('now', $currentTimezone);
                $diff = $blockDate->diff($todayDate);

                $blockDays = $diff->format('%a');
                $blockHours = $diff->format('%h');

                $content = "Motivo Bloqueio: " . $blockBedInfo['motivo'] . "<br>Tempo de bloqueio: " . $blockDays . " dias e " . $blockHours . " horas";
                break;
            case 'L':
                $emptyDate = Leito::getEmptyBedDate($bed['codigo']);
                $emptyDate = new DateTime($emptyDate, $currentTimezone);
                $todayDate = new Datetime('now', $currentTimezone);
                $diff = $emptyDate->diff($todayDate);

                $emptyDays = $diff->format('%a');
                $emptyHours = $diff->format('%h');

                $content = "Tempo em que o leito está livre: " . $emptyDays . " dias e " . $emptyHours . " horas";
                break;
        }

        $isBlocked = CommonsController::isBlocked($bed['codigo']);

        return View::render('gestaoleitos/bed_management/bed_modal', [
            'title' => $unity['nome'],
            'content' => $content,
            'button' => $bed['status'] !== 'L' ? '' : View::render('gestaoleitos/bed_management/modal_button'),
            'label' => $isBlocked ? 'Desbloquear leito' : 'Bloquear leito',
            'color' => $isBlocked ? 'success' : 'secondary',
        ]);
    }

    /**
     * Método responsável por bloquear/desbloquear um leito
     * 
     * @param Request $request Requisição do usuário
     * @return array Array com o status da operação e a mensagem de retorno
     */
    public static function setBlockBed(Request $request, string $bedCode): array
    {
        $isBlocked = CommonsController::isBlocked($bedCode);
        $user = $request->user;

        try {
            if ($isBlocked) {
                Leito::insertLockBed($bedCode, 'desbloqueio', $user->id);

                return [
                    'success' => true,
                    'message' => 'Leito desbloqueado com sucesso!'
                ];
            }

            Leito::insertLockBed($bedCode, 'bloqueio', $user->id);

            return [
                'success' => true,
                'message' => 'Leito bloqueado com sucesso!'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao bloquear/desbloquear leito!'
            ];
        }
    }
}
