<?php

namespace App\Controller\GestaoLeitos;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Controller\GestaoLeitos\CommonsController;
use App\Http\Request;
use App\Model\GestaoLeitos\Leito;
use App\Model\GestaoLeitos\Setor;

class BedPanelController extends LayoutPage
{
    public const statusColor = [
        'P' => 'teal',
        'L' => 'success',
        'O' => 'secondary',
        'R' => 'indigo',
        'B' => 'danger',
    ];

    public const statusIcon = [
        'P' => 'badge-check',
        'L' => 'lock-open-alt',
        'O' => 'procedures',
        'R' => 'unlock-alt',
        'B' => 'lock-alt',
    ];

    /**
     * Método responsável por retornar as unidades com suas informações
     * e os leitos contidos nela em seus respectivos status
     *
     * @param array $unities Unidades que irão ser exibidas no painel
     * @param array $beds Leitos que irão ser exibidos no painel
     * @param array $virtualBeds Leitos virtuais que irão ser exibidos no painel
     * @return string
     */
    private static function getUnitsInfo(array $unities, array $beds): string
    {
        $unitOptions = '';
        $blockedBeds = Leito::getBlockedBeds();
        $blockedBeds = array_column($blockedBeds, 'codigo_leito');
        foreach ($unities as $unity) {
            if ($unity['codigo'] === "CIR" || $unity['codigo'] === "HEM")
                continue;

            $infos = Setor::getInfoUnit($unity['codigo']);
            $ocupationPercentage = number_format($infos['ocupacao'], 1, '.', '');

            $bedContent = self::getBedOption($beds, $unity, $blockedBeds);

            $unitOptions .= View::render('gestaoleitos/bed_panel/info_unit', [
                'setor-codigo' => $unity['codigo'],
                'setor-nome' => $unity['nome'],
                'leitos' => $bedContent['options'],
                'total-leitos' => $bedContent['count-beds'],
                'leitos-virtuais' => $bedContent['count-virtual-beds'],
                'leitos-bloqueados' => $infos['leitos_bloqueados'],
                'leitos-disponiveis' => $bedContent['disponible-beds'],
                'leitos-reservados' => $infos['leitos_reservados'],
                'leitos-ocupados' => $infos['leitos_ocupados'],
                'leitos-vagos' => intval($infos['leitos_vagos'] - $bedContent['disponible-beds']) ?? 0,
                'ocupacao-porcentagem' => $ocupationPercentage,
            ]);
        }
        return $unitOptions;
    }

    /**
     * Método responsável por retornar os leitos não bloqueados do array
     * @param array $beds Leitos da unidade
     * @return array
     */
    private static function getUnblockedBeds(array $beds): array
    {
        $disponibleBeds = [];
        foreach ($beds as $bed) {
            $isBlocked = CommonsController::isBlocked($bed['leito_codigo']);

            if ($isBlocked)
                continue;

            $disponibleBeds['total']++;
            $disponibleBeds[trim($bed['acomodacao'])]++;
        }
        return $disponibleBeds;
    }

    /**
     * Método responsável por retornar as listagem de leitos fragmentadas por acomdocação
     * @param array $beds Leitos que serão separados
     * @return array
     */
    private static function getFragmentedBeds(array $beds): array
    {
        $fragmentedBeds = [];
        foreach ($beds as $bed) {
            $fragmentedBeds['total']++;
            $fragmentedBeds[trim($bed['acomodacao'])]++;
        }
        return $fragmentedBeds;
    }

    /**
     * Método responsável por retornar os cards do topo da página
     * @param int $totalBeds Total de leitos
     * @param int $virtualBeds Total de leitos virtuais
     * @param string $unitsCode Códigos das unidades que serão buscadas
     * @return string
     */
    private static function getCards(
        int $totalBeds,
        int $virtualBeds,
        string $unitsCode,
    ): string {
        $emptyBeds = Leito::getBedsByStatus($unitsCode, 'L');
        $ocupedBeds = Leito::getBedsByStatus($unitsCode, 'O');
        $blockedBeds = Leito::getBedsByStatus($unitsCode, 'B');
        $reservedBeds = Leito::getBedsByStatus($unitsCode, 'R');

        $disponibleBeds = self::getUnblockedBeds($emptyBeds);
        $ocupedBeds = self::getFragmentedBeds($ocupedBeds);
        $blockedBeds = self::getFragmentedBeds($blockedBeds);
        $reservedBeds = self::getFragmentedBeds($reservedBeds);
        $emptyBeds = self::getFragmentedBeds($emptyBeds);

        $ocupationPercentage = number_format($ocupedBeds['total'] * 100 / ($totalBeds + $virtualBeds), 1, '.', '');

        return View::render('gestaoleitos/bed_panel/cards', [
            'leitos' => $totalBeds + $virtualBeds,
            'leitos-reais' => $totalBeds,
            'leitos-virtuais' => $virtualBeds,
            'ocupacao-porcentagem' => $ocupationPercentage,
            'leitos-disponiveis' => $disponibleBeds['total'] ?? 0,
            'leitos-disponiveis-uti' => $disponibleBeds['UTI'] ?? 0,
            'leitos-disponiveis-apt' => $disponibleBeds['APT'] ?? 0,
            'leitos-disponiveis-enf' => $disponibleBeds['ENF'] ?? 0,
            'leitos-disponiveis-neo' => $disponibleBeds['NEO'] ?? 0,
            'leitos-vagos' => $emptyBeds['total'] ?? 0,
            'leitos-vagos-uti' => $emptyBeds['UTI'] ?? 0,
            'leitos-vagos-apt' => $emptyBeds['APT'] ?? 0,
            'leitos-vagos-enf' => $emptyBeds['ENF'] ?? 0,
            'leitos-vagos-neo' => $emptyBeds['NEO'] ?? 0,
            'leitos-ocupados' => $ocupedBeds['total'] ?? 0,
            'leitos-ocupados-uti' => $ocupedBeds['UTI'] ?? 0,
            'leitos-ocupados-apt' => $ocupedBeds['APT'] ?? 0,
            'leitos-ocupados-enf' => $ocupedBeds['ENF'] ?? 0,
            'leitos-ocupados-neo' => $ocupedBeds['NEO'] ?? 0,
            'leitos-reservados' => $reservedBeds['total'] ?? 0,
            'leitos-reservados-uti' => $reservedBeds['UTI'] ?? 0,
            'leitos-reservados-apt' => $reservedBeds['APT'] ?? 0,
            'leitos-reservados-enf' => $reservedBeds['ENF'] ?? 0,
            'leitos-reservados-neo' => $reservedBeds['NEO'] ?? 0,
            'leitos-bloqueados' => $blockedBeds['total'] ?? 0,
            'leitos-bloqueados-uti' => $blockedBeds['UTI'] ?? 0,
            'leitos-bloqueados-apt' => $blockedBeds['APT'] ?? 0,
            'leitos-bloqueados-enf' => $blockedBeds['ENF'] ?? 0,
            'leitos-bloqueados-neo' => $blockedBeds['NEO'] ?? 0,
        ]);
    }

    /**
     * Método responsável por retornar o nome do leito formato
     * @param string $bedName
     * @return string
     */
    public static function formatBedName(string $bedName): string
    {
        $pattern = [
            'ENFERMARIA' => 'ENF',
            'ENFE' => 'ENF',
            'APTO' => 'APT',
            'LEITO EXTRA' => 'EXTRA',
            'SALA VERMELHA PS' => 'SALA VERM. (PS)',
        ];

        foreach ($pattern as $key => $value) {
            $bedName = str_replace($key, $value, strtoupper($bedName));
        }

        return trim($bedName);
    }

    /**
     * Método responsável por retornar os leitos de uma unidade com seus status e respectivas cores
     * @param array $beds Leitos normais
     * @param array $unity Unidade dos respectivos leitos
     * @param array $leitos bloqueados
     * @return array
     */
    private static function getBedOption(array $beds, array $unity, array $blockedBeds): array
    {
        $order = array('L', 'R', 'B', 'O');

        usort($beds, function ($a, $b) use ($order) {
            $pos_a = array_search($a['status'], $order);
            $pos_b = array_search($b['status'], $order);

            return $pos_a - $pos_b;
        });

        $unitCodes = array_column($beds, 'setor_codigo');
        $bedKeys = array_keys(array_map('trim', $unitCodes), $unity['codigo']);

        $bedsOptions = '';
        $disponibleBeds = 0;
        $countBeds = 0;
        $countVirtualBeds = 0;

        for ($i = 0; $i < count($bedKeys); $i++) {
            $bed = $beds[$bedKeys[$i]];
            $isBlocked = in_array(trim($bed['codigo']), $blockedBeds);

            if (!$isBlocked && trim($bed['status']) === 'L') {
                $bed['status'] = 'P';
                $disponibleBeds++;
            }

            $isVirtual = strtoupper($bed['tipo']) === 'VIRTUAL' ? true : false;
            $isVirtual ? $countVirtualBeds++ : $countBeds++;

            $bedsOptions .= View::render('gestaoleitos/utils/bed', [
                'responsive-classes' => "col-6 col-md-4 col-lg-2 col-xl-1",
                'color' => self::statusColor[$bed['status']],
                'leito-codigo' => $bed['codigo'],
                'icon' => self::statusIcon[$bed['status']],
                'leito' => self::formatBedName($bed['nome']) . $bed['codigo'],
                'tipo-leito' => $isVirtual ? 'Virtual' : 'Real',
                'icon-tipo-leito' => $isVirtual ? 'fas fa-circle-notch' : 'fas fa-circle',
            ]);
        }

        return [
            'disponible-beds' => $disponibleBeds,
            'options' => $bedsOptions,
            'count-beds' => $countBeds,
            'count-virtual-beds' => $countVirtualBeds,
        ];
    }

    /**
     * Método responsável por retornar os leitos de determinada unidade
     *
     * @param Request $request Requisição do usuário
     * @param string $unity Unidade que será exibida no painel
     * @return string
     */
    public static function getBedsOptions(Request $request, string $unity): array
    {
        $unity = Setor::getUnitByCode($unity);
        $unitsCode = array_column([$unity], 'codigo');
        $unitsCode = implode("','", $unitsCode);

        // $beds = Leito::getBeds($unitsCode);
        // $virtualBeds = Leito::getVirtualBeds($unitsCode);
        $beds = Leito::getAllBedsByUnit($unitsCode);

        $blockedBeds = Leito::getBlockedBeds();
        $blockedBeds = array_column($blockedBeds, 'codigo_leito');

        $infos = Setor::getInfoUnit($unity['codigo']);
        $ocupationPercentage = number_format($infos['ocupacao'], 1, '.', '');

        $bedContent = self::getBedOption($beds, $unity, $blockedBeds);

        $information = [
            'ocupation' => $ocupationPercentage,
            'blocked-beds' => $infos['leitos_bloqueados'],
            'reserved-beds' => $infos['leitos_reservados'],
            'occupied-beds' => $infos['leitos_ocupados'],
        ];

        return array_merge($bedContent, $information);
    }

    /**
     * Método responsável por retornar a página incial do painel de leitos
     *
     * @param Request $request
     * @return string
     */
    public static function getBedPanel(Request $request): string
    {
        $unities = Setor::getHospitalizationUnits();
        $unitsCode = array_column($unities, 'codigo');
        $unitsCode = implode("','", $unitsCode);

        $beds = Leito::getAllBedsByUnit($unitsCode);

        $typeBeds = array_column($beds, 'tipo');
        $bedsCount = array_count_values($typeBeds);

        $content = View::render('gestaoleitos/bed_panel', [
            'cards' => self::getCards(
                $bedsCount['normal'],
                $bedsCount['virtual'],
                $unitsCode
            ),
            'info-setores' => self::getUnitsInfo($unities, $beds),
        ]);

        return parent::getPage('Gestão de Leitos | Painel de Leitos', 'gestaoleitos', $content, $request);
    }
}
