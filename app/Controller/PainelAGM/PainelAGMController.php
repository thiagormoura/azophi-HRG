<?php

namespace App\Controller\PainelAGM;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Http\Request;
use \App\Model\PainelAGM\PainelAGMModel;
use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;

class PainelAGMController extends LayoutPage
{
    public static function getHome(Request $request)
    {
        $cirurgias = PainelAGMModel::getCirurgias();

        // $countCirurgias = count($cirurgias) <= 10 ? count($cirurgias) : 11;

        $aux = 0;
        $rows = "";
        foreach ($cirurgias as $paciente) {

            $TAM = $paciente['TAM'];
            $INT = $paciente['INT'];
            $local_internacao = "";

            if ($INT != "-") {
                $local_internacao = $INT;
            } else {
                if ($INT != "-") {
                    $local_internacao = $TAM;
                } else {
                    $local_internacao = '-';
                }
            }

            /* TRATAR DATA  */
            $data_hora = explode('T', $paciente['HORA_INICIO']);
            $data = explode('-', $data_hora[0]);
            $hora = $data_hora[1];

            /*TRATAR SALA */
            $sala = explode(' ', $paciente['SALA']);
            $cor_hora = '';

            /*TRATAR NOME DO MÉDICO (2 NOMES) */
            $p = explode(" ", $paciente['NOME_PACIENTE']);
            $p_nome = "";

            if (sizeof($p) < 3) {
                $p_nome = $p[0] . " " . $p[1];
            }
            if (sizeof($p) > 2) {
                $p_nome = $p[0] . " " . $p[1];

                if (strlen($p[2]) > 2 && strlen($p[0] . " " . $p[1] . " " . $p[2]) < 21) {
                    $p_nome = $p[0] . " " . $p[1] . " " . $p[2];
                }
            }

            /*TRATAR NOME DO MÉDICO (2 NOMES) */
            $medico = explode(" ", $paciente['MEDICO']);
            $medico_nome = "";

            if (sizeof($medico) > 0) {
                $medico_nome = $medico[0] . " " . $medico[1];
            }

            /*TRATAR TEMPO(COR)*/
            $cor_hora = 'background-color: #90ef79';

            if ($paciente['TEMPO'] < 0) {
                $cor_hora = 'background-color: #3e70d0';
            } elseif ($paciente['TEMPO'] < 20) {
                $cor_hora = 'background-color: #e81818';
            } elseif ($paciente['TEMPO'] < 45) {
                $cor_hora = 'background-color: #f2994e';
            }

            // matriz de entrada
            $what = array('a', 'ã', 'à', 'á', 'â', 'ê', 'ë', 'è', 'é', 'ï', 'ì', 'í', 'ö', 'õ', 'ò', 'ó', 'ô', 'ü', 'ù', 'ú', 'û', 'À', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ', 'ç', 'Ç');

            // matriz de saída
            $by = array('a', 'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'A', 'A', 'E', 'I', 'O', 'U', 'n', 'n', 'c', 'C');

            $rows .= View::render('painelagm/rowagm', [
                "bg-color" => $cor_hora,
                "time" => $hora,
                "sala" => $sala[2],
                "paciente-nome" => substr(str_replace($what, $by, $p_nome), 0, 30),
                "servico" => substr(str_replace($what, $by, $paciente['SERVICO_NOME']), 0, 50),
                "medico" => $medico_nome,
                "convenio" => $paciente['CONVENIO'],
                "local-internacao" => $local_internacao,
                "opme" => $paciente['OPME'],
                "sangue" => $paciente['SANGUE'] == 'S' ? "<i class='icofont-blood-drop'></i>" : "",
                "internacao" => $paciente['INTERNACAO'] == 'S' ? "<i class='icofont-check-circled'></i>" : "",
                "intensificador" => $paciente['INTIMG'] == 'S' ? "<i class='icofont-check-circled'></i>" : ""
            ]);

            if (++$aux == 11) break;
        }

        $table = View::render('painelagm/tableagm', [
            "rows" => $rows
        ]);

        return View::render('painelagm/home', [
            "total-cirurgias" => count($cirurgias),
            "index" => $aux,
            "table" => $table
        ]);
    }


    public static function getNewPage(Request $request)
    {
        $initialInterval = $request->getPostVars()['initial'];
        $finalInterval = $request->getPostVars()['final'];

        $cirurgias = PainelAGMModel::getCirurgias();

        $result = self::paginate($cirurgias, $initialInterval, $finalInterval);

        if ($result['reiniciate'] && empty($result['rows'])) {
            $initialInterval = 0;
            $finalInterval = 11;
            $result = self::paginate($cirurgias, $initialInterval, $finalInterval);
        }

        return [
            "rows" => $result['rows'],
            "countRows" => $result['$index'],
            "index" => $finalInterval
        ];
    }

    public static function paginate($cirurgias, $initialInterval, $finalInterval)
    {
        $reiniciateInterval = false;
        $rows = "";
        $index = 0;

        for ($i = $initialInterval; $i < $finalInterval; $i++) {
            if (isset($cirurgias[$i])) {
                $paciente = $cirurgias[$i];
                $TAM = $paciente['TAM'];
                $INT = $paciente['INT'];
                $local_internacao = "";

                if ($INT != "-") {
                    $local_internacao = $INT;
                } else {
                    if ($INT != "-") {
                        $local_internacao = $TAM;
                    } else {
                        $local_internacao = '-';
                    }
                }

                /* TRATAR DATA  */
                $data_hora = explode('T', $paciente['HORA_INICIO']);
                $data = explode('-', $data_hora[0]);
                $hora = $data_hora[1];

                /*TRATAR SALA */
                $sala = explode(' ', $paciente['SALA']);
                $cor_hora = '';

                /*TRATAR NOME DO MÉDICO (2 NOMES) */
                $p = explode(" ", $paciente['NOME_PACIENTE']);
                $p_nome = "";

                if (sizeof($p) < 3) {
                    $p_nome = $p[0] . " " . $p[1];
                }
                if (sizeof($p) > 2) {
                    $p_nome = $p[0] . " " . $p[1];

                    if (strlen($p[2]) > 2 && strlen($p[0] . " " . $p[1] . " " . $p[2]) < 21) {
                        $p_nome = $p[0] . " " . $p[1] . " " . $p[2];
                    }
                }

                /*TRATAR NOME DO MÉDICO (2 NOMES) */
                $medico = explode(" ", $paciente['MEDICO']);
                $medico_nome = "";

                if (sizeof($medico) > 0) {
                    $medico_nome = $medico[0] . " " . $medico[1];
                }

                /*TRATAR TEMPO(COR)*/
                $cor_hora = 'background-color: #90ef79';

                if ($paciente['TEMPO'] < 0) {
                    $cor_hora = 'background-color: #3e70d0';
                } elseif ($paciente['TEMPO'] < 20) {
                    $cor_hora = 'background-color: #e81818';
                } elseif ($paciente['TEMPO'] < 45) {
                    $cor_hora = 'background-color: #f2994e';
                }

                // matriz de entrada
                $what = array('a', 'ã', 'à', 'á', 'â', 'ê', 'ë', 'è', 'é', 'ï', 'ì', 'í', 'ö', 'õ', 'ò', 'ó', 'ô', 'ü', 'ù', 'ú', 'û', 'À', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ', 'ç', 'Ç');

                // matriz de saída
                $by = array('a', 'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'A', 'A', 'E', 'I', 'O', 'U', 'n', 'n', 'c', 'C');

                $rows .= View::render('painelagm/rowagm', [
                    "bg-color" => $cor_hora,
                    "time" => $hora,
                    "sala" => $sala[2],
                    "paciente-nome" => substr(str_replace($what, $by, $p_nome), 0, 30),
                    "servico" => substr(str_replace($what, $by, $paciente['SERVICO_NOME']), 0, 50),
                    "medico" => $medico_nome,
                    "convenio" => $paciente['CONVENIO'],
                    "local-internacao" => $local_internacao,
                    "opme" => $paciente['OPME'],
                    "sangue" => $paciente['SANGUE'] == 'S' ? "<i class='icofont-blood-drop'></i>" : "",
                    "internacao" => $paciente['INTERNACAO'] == 'S' ? "<i class='icofont-check-circled'></i>" : "",
                    "intensificador" => $paciente['INTIMG'] == 'S' ? "<i class='icofont-check-circled'></i>" : ""
                ]);

                $index++;
            } else {
                $reiniciateInterval = true;
                break;
            }
        }

        return [
            "rows" => $rows,
            "index" => $index,
            "reiniciate" => $reiniciateInterval
        ];
    }
}
