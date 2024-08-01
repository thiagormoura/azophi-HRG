<?php

namespace App\Controller\EscalaMedica;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Http\Request;
use App\Model\EscalaMedica\EscalaMedicaModel;
use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;

class EscalaMedicaController extends LayoutPage
{
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
                'id' => $option[$value] . "/" . trim($option[$label]),
                'nome' => "CRM: " . $option[$value] . " - " . trim($option[$label]),
                'selected' => $selected === $option[$value] ? 'selected' : '',
            ]);
        }

        return View::render('utils/select', [
            'name' => $select['name'],
            'id' => $select['id'],
            'class' => "form-control",
            'disabled' => $select['disabled'] ? 'disabled' : '',
            'options' => $optionsSelect,
        ]);
    }

    public static function getHome(Request $request)
    {
        $content = View::render("escalamedica/home", [
            "list-medicos" => self::getSelect(
                EscalaMedicaModel::getDoctors(),
                "CRM",
                "NOME",
                ["name" => "options", "id" => "options"]
            )
        ]);
        return parent::getPage("Escala médica PS", 'escala-medica-ps', $content, $request);
    }

    public static function getMedicosPlantaoTable(Request $request)
    {
        $colums = [
            "CRM",
            "MEDICO_NOME",
            "MEDICO_ESPECIALIDADE",
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
                if ($item['column'] == 3)
                    continue;

                if ($item == $firstItem)
                    $order = $colums[$item['column']] . " " . $item['dir'] . (count($post['order']) > 1 ? ", " : "");

                elseif ($item == $endItem)
                    $order .= $colums[$item['column']] . " " . $item['dir'];

                else
                    $order .= $colums[$item['column']] . " " . $item['dir'] . ", ";
            }
        }

        $where = null;
        if (!empty($post['search']['value'])) {
            $where = " AND (nome_med like '%" . $post['search']['value'] . "%' OR UPPER(nome_esp) like '%" . $post['search']['value'] . "%'" . (is_numeric($post['search']['value']) ? " OR CRM = " . $post['search']['value'] . ")" : ")");
        }

        $doctors = [];
        $button = View::render("escalamedica/button_ban_doctor");
        foreach (EscalaMedicaModel::getDoctorsRegistereds($where, $limit, $order) as $key => $doctor) {
            $doctors[$key]['CRM'] = trim($doctor['CRM']);
            $doctors[$key]['MEDICO_NOME'] = trim($doctor['MEDICO_NOME']);
            $doctors[$key]['MEDICO_ESPECIALIDADE'] = trim($doctor['MEDICO_ESPECIALIDADE']);
            $doctors[$key]['BUTTON'] = $button;
        }

        return array(
            "draw" => isset($post['draw']) ? intval($post['draw']) : 0,
            "recordsTotal" => count($doctors),
            "recordsFiltered" => count(EscalaMedicaModel::getDoctorsRegistereds()),
            "data" => $doctors
            // ,"teste" => $where
        );
    }

    public static function insertDoctorInDuty(Request $request)
    {
        $post = $request->getPostVars();

        if (empty($post['nome']) || empty($post['especialidades']) || !is_numeric($post['crm']))
            return [
                "success" => false,
                "message" => "Campos inválidos!"
            ];

        // prevenir cadastrar médico sem querer
        return [
            "success" => true,
            "message" => "Médico inserido com sucesso no plantão!"
        ];

        if (EscalaMedicaModel::insertDoctorInDuty($post) === false)
            return [
                "success" => false,
                "message" => "Erro na inserção!"
            ];
    }

    public static function getPainel(Request $request)
    {
        $content = View::render('escalamedica/painel', [
            "clinico-geral" => self::transformInRow(
                EscalaMedicaModel::getDoctorsRegistereds(" AND UPPER(nome_esp) = 'CLINICO GERAL'"),
                false
            ),
            "cardiologia" => self::transformInRow(
                EscalaMedicaModel::getDoctorsRegistereds(" AND UPPER(nome_esp) = 'CARDIOLOGIA'"),
                false
            ),
            "ortopedia" => self::transformInRow(
                EscalaMedicaModel::getDoctorsRegistereds(" AND UPPER(nome_esp) = 'ORTOPEDISTA'"),
                false
            ),
            "enfermeiro-psa" => self::transformInRow(
                EscalaMedicaModel::getDoctorsRegistereds(" AND UPPER(nome_esp) = 'ENFERMEIRO PSA'"),
                true
            ),
            "tecnico-gesso-psa" => self::transformInRow(
                EscalaMedicaModel::getDoctorsRegistereds(" AND UPPER(nome_esp) = 'TECNICO GESSO'"),
                true
            )
        ]);

        return self::getPanelLayout('Escala médica PS', $content);
    }

    private static function transformInRow($doctors, $isPSA)
    {
        $result = "";
        if (!$isPSA) {
            foreach ($doctors as $doctor) {
                $result .= View::render('/escalamedica/rowMedico', [
                    "crm" => $doctor['CRM'],
                    "nome" => $doctor['MEDICO_NOME'],
                    "rqe-cod" => $doctor['rqe_cod']
                ]);
            }
            return $result;
        } else {
            foreach ($doctors as $doctor) {
                $result .= View::render('/escalamedica/rowPSA', [
                    "coren" => $doctor['CRM'],
                    "nome" => $doctor['MEDICO_NOME']
                ]);
            }
            return $result;
        }
    }
}
