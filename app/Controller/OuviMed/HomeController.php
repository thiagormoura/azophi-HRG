<?php

namespace App\Controller\OuviMed;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Http\Request;
use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use App\Model\OuviMed\Manifestacao;
use App\Model\OuviMed\Setor;
use App\Model\Entity\User;

class HomeController extends LayoutPage
{

    public static function getHome(Request $request){

        $content = View::render('ouvimed/home', [
            'title' => 'Ouvimed - Home',
            'cards' => self::getCardsComponent(),
            'table-filters' => self::getTableFilters(),
            'table' => self::getTable(),
        ]);

        return parent::getPage('Gestão de Manifestações/Ouvimed - Home', 'OuviMed', $content, $request);
    }

    private static function getCardsComponent(){
    
        $manifestacoesEmAberto = Manifestacao::getCountOfAllManifestacoesEmAberto();
        // $manifestacoesEmAbertoPorIdentificacao = Manifestacao::getCountOfAllManifestacoesEmAbertoByIdentificacao();
        // $manifestacoesEmAbertoElogio = 0;
        // $manifestacoesEmAbertoReclamacao = 0;
        // $manifestacoesEmAbertoSolicitacao = 0;
        // $manifestacoesEmAbertoInformacao = 0;
        // $manifestacoesEmAbertoSugestao = 0;
        // $manifestacoesEmAbertoCritica = 0;
        // $manifestacoesEmAbertoDenuncia = 0;

        // foreach($manifestacoesEmAbertoPorIdentificacao as $manifestacao){
        //     if($manifestacao['identificacao'] == 'ELOGIO'){
        //         $manifestacoesEmAbertoElogio = $manifestacao['quantidade'];
        //     }
        //     if($manifestacao['identificacao'] == 'RECLAMACAO'){
        //         $manifestacoesEmAbertoReclamacao = $manifestacao['quantidade'];
        //     }
        //     if($manifestacao['identificacao'] == 'SOLICITACAO'){
        //         $manifestacoesEmAbertoSolicitacao = $manifestacao['quantidade'];
        //     }
        //     if($manifestacao['identificacao'] == 'INFORMACAO'){
        //         $manifestacoesEmAbertoInformacao = $manifestacao['quantidade'];
        //     }
        //     if($manifestacao['identificacao'] == 'SUGESTAO'){
        //         $manifestacoesEmAbertoSugestao = $manifestacao['quantidade'];
        //     }
        //     if($manifestacao['identificacao'] == 'CRITICA'){
        //         $manifestacoesEmAbertoCritica = $manifestacao['quantidade'];
        //     }
        //     if($manifestacao['identificacao'] == 'DENUNCIA'){
        //         $manifestacoesEmAbertoDenuncia = $manifestacao['quantidade'];
        //     }

        // }

        $manifestacoesEmProcessamento = Manifestacao::getCountOfAllManifestacoesEmProcessamento();
        // $manifestacoesEmProcessamentoPorIdentificacao = Manifestacao::getCountOfAllManifestacoesEmProcessamentoByIdentificacao();
        // $manifestacoesEmProcessamentoElogio = 0;
        // $manifestacoesEmProcessamentoReclamacao = 0;
        // $manifestacoesEmProcessamentoSolicitacao = 0;
        // $manifestacoesEmProcessamentoInformacao = 0;
        // $manifestacoesEmProcessamentoSugestao = 0;
        // $manifestacoesEmProcessamentoCritica = 0;
        // $manifestacoesEmProcessamentoDenuncia = 0;

        // foreach($manifestacoesEmProcessamentoPorIdentificacao as $manifestacao){
        //     if($manifestacao['identificacao'] == 'ELOGIO'){
        //         $manifestacoesEmProcessamentoElogio = $manifestacao['quantidade'];
        //     }
        //     if($manifestacao['identificacao'] == 'RECLAMACAO'){
        //         $manifestacoesEmProcessamentoReclamacao = $manifestacao['quantidade'];
        //     }
        //     if($manifestacao['identificacao'] == 'SOLICITACAO'){
        //         $manifestacoesEmProcessamentoSolicitacao = $manifestacao['quantidade'];
        //     }
        //     if($manifestacao['identificacao'] == 'INFORMACAO'){
        //         $manifestacoesEmProcessamentoInformacao = $manifestacao['quantidade'];
        //     }
        //     if($manifestacao['identificacao'] == 'SUGESTAO'){
        //         $manifestacoesEmProcessamentoSugestao = $manifestacao['quantidade'];
        //     }
        //     if($manifestacao['identificacao'] == 'CRITICA'){
        //         $manifestacoesEmProcessamentoCritica = $manifestacao['quantidade'];
        //     }
        //     if($manifestacao['identificacao'] == 'DENUNCIA'){
        //         $manifestacoesEmProcessamentoDenuncia = $manifestacao['quantidade'];
        //     }

        // }

        $manifestacoesFinalizadas = Manifestacao::getCountOfAllManifestacoesFinalizadas();

        $manifestacoesCanceladas = Manifestacao::getCountOfAllManifestacoesCanceladas();

        return View::render('ouvimed/home/cards', [
            "manifestacoes-aberto" => $manifestacoesEmAberto[0]['quantidade'] ?? 0,
            // "manifestacoes-aberto-elogio" => $manifestacoesEmAbertoElogio,
            // "manifestacoes-aberto-reclamacao" => $manifestacoesEmAbertoReclamacao,
            // "manifestacoes-aberto-solicitacao" => $manifestacoesEmAbertoSolicitacao,
            // "manifestacoes-aberto-informacao" => $manifestacoesEmAbertoInformacao,
            // "manifestacoes-aberto-sugestao" => $manifestacoesEmAbertoSugestao,
            // "manifestacoes-aberto-critica" => $manifestacoesEmAbertoCritica,
            // "manifestacoes-aberto-denuncia" => $manifestacoesEmAbertoDenuncia,
            "manifestacoes-processamento" => $manifestacoesEmProcessamento[0]['quantidade'] ?? 0,
            // "manifestacoes-processamento-elogio" => $manifestacoesEmProcessamentoElogio,
            // "manifestacoes-processamento-reclamacao" => $manifestacoesEmProcessamentoReclamacao,
            // "manifestacoes-processamento-solicitacao" => $manifestacoesEmProcessamentoSolicitacao,
            // "manifestacoes-processamento-informacao" => $manifestacoesEmProcessamentoInformacao,
            // "manifestacoes-processamento-sugestao" => $manifestacoesEmProcessamentoSugestao,
            // "manifestacoes-processamento-critica" => $manifestacoesEmProcessamentoCritica,
            // "manifestacoes-processamento-denuncia" => $manifestacoesEmProcessamentoDenuncia,
            "manifestacoes-finalizadas" => $manifestacoesFinalizadas[0]['quantidade'] ?? 0,
            "manifestacoes-canceladas" => $manifestacoesCanceladas[0]['quantidade'] ?? 0
        ]);
    }

    private static function getTableFilters(){

        $options = '<option value="" selected disabled>Selecione os setores envolvidos</option>';
      
        foreach (Setor::getAllSectors() as $setor) {
            $options .= View::render('utils/option', [
                'id' => $setor['id'],
                'nome' => $setor['nome']
            ]);
        }
       
        $select = View::render('utils/select', [
            'class' => '',
            'name' => '',
            'id' => 'slSetoresEnvolvidosHome',
            'style' => '',
            'disabled' => '',
            'options' => $options,
            'required' => 'required'
        ]);

        return View::render('ouvimed/home/filtros', [
            'select-setores-envolvidos' => $select
        ]);
    }

    private static function getTable(){
        return View::render('ouvimed/home/table', []);
    }

    public static function getManifestacoes(Request $request){

        $filter_values = $request->getPostVars();    

        $manifestacoes = Manifestacao::getManifestacoes($filter_values, null);

        // es hora de gerar o protoloco de cada manifestação no formato nº da manigestação do ano / ano
        foreach($manifestacoes as $key => $manifestacao){
            $manifestacoes[$key]['PROTOCOLO'] = Manifestacao::getProtocolo($manifestacoes[$key]);
        }

        // christian fez essa monstruosidade colossal para ordenas as manifestações pelo protocolo
        // parabens christian, vc é o cara!
        $ordenacao = $filter_values['order'];

        $hasProtocolOrder = array_search(0, array_column($ordenacao, 'column'));

        if($hasProtocolOrder !== false){
            $protocolOrder = "asc";

            foreach ($ordenacao as $key => $value) {
                if($value['column'] == 0){
                    $protocolOrder = $ordenacao[$key]['dir'];
                    break;
                }
            }

            usort($manifestacoes, function($a, $b) use ($protocolOrder){
                $order = $protocolOrder == "asc" ? -1 : 1;

                $a_num = explode("/", $a['PROTOCOLO'])[0];
                $a_ano = explode("/", $a['PROTOCOLO'])[1];
    
                $b_num = explode("/", $b['PROTOCOLO'])[0];
                $b_ano = explode("/", $b['PROTOCOLO'])[1];
    
                if($b_ano == $a_ano) {
                    return (($a_num < $b_num) ? 1*$order : (($a_num > $b_num) ? -1*$order : 0));
                }
                else {
                    if($b_ano > $a_ano) return 1*$order;
                    else return -1*$order;
                }
            });
        }

        // limit datatables
        $manifestacoes = array_slice($manifestacoes, $filter_values['start'], $filter_values['length']);
        

        return array(
            "draw" => isset($filter_values['draw']) ? intval($filter_values['draw']) : 0,
            "recordsTotal" => count($manifestacoes),
            "recordsFiltered" => count(Manifestacao::getManifestacoes($filter_values, null)),
            "data" => $manifestacoes,
        );
    }

    // Gera pagina de manifestação com base no id da manifestação
    public static function getManifestacaoPage(Request $request, $id){

        // Adquire os dados da manifestação
        $manifestacao = Manifestacao::getManifestacao($id);

        // Adquire as acoes tomadas da manifestação
        $acoesTomadas = Manifestacao::getAcoesTomadas($id);

        // Adquire os nomes dos usuarios que registraram as acoes tomadas
        foreach ($acoesTomadas as $key => $acaoTomada) {
            $acoesTomadas[$key]['usuario_registro'] = User::getUserById($acaoTomada['usuario'])->nome . ' ' . User::getUserById($acaoTomada['usuario'])->sobrenome;
        }

        // Para cada ação tomada, cria um textarea contendo a descrição da ação tomada e um input de tempo
        // contendo a data/hora que aquela ação foi tomada
        // Não aparece se a manifestação estiver em aberto
        $displayAcoesTitle = 'd-none';
        if($manifestacao[0]['STATUS'] != 'A'){
            $acoesTomadasInputs = '';
            foreach ($acoesTomadas as $acaoTomada) {
                $acoesTomadasInputs .= View::render('ouvimed/visualizar/textarea_plus_dthr_input', [
                    'inUsuario_value' => $acaoTomada['usuario_registro'],
                    'inUsuario_disabled' => 'disabled',
                    'ta_id' => 'taAcaoTomada' . $acaoTomada['id'],
                    'ta_disabled' => 'disabled',
                    'ta_text' => $acaoTomada['acao'],
                    'inDate_id' => 'inDataAcaoTomada' . $acaoTomada['id'],
                    'inDate_value' => substr($acaoTomada['dthr_registro'], 0, 10),
                    'inDate_disabled' => 'disabled',
                    'inHour_id' => 'inHoraAcaoTomada' . $acaoTomada['id'],
                    'inHour_value' => substr($acaoTomada['dthr_registro'], 11, 5),
                    'inHour_disabled' => 'disabled'
                ]);
            }
            $displayAcoesTitle = '';
        }
        
        // Adquire todos os setores do hospital e procura os setores envolvidos na manifestação
        $setores = Setor::getAllSectors();

        // Para cada linha da manifestação (cada identificação)
        $sim = '';
        foreach($manifestacao as $key => $linha_manifestacao){
            
            $setoresEnvolvidosCodigoAtual = explode(",", $linha_manifestacao['SETORES_ENVOLVIDOS_ID']); 
            $setoresEnvolvidosAtual = [];
  
            foreach($setores as $setor){
                if(in_array($setor['id'], $setoresEnvolvidosCodigoAtual)){
                    $setoresEnvolvidosAtual[] = [
                        'CODIGO' => $setor['id'],
                        'NOME' => $setor['nome']
                    ];
                }
            }

            // Select com setores envolvidos
            $options = '';
            foreach ($setoresEnvolvidosAtual as $setor) {
                $options .= View::render('utils/option', [
                    'id' => $setor['CODIGO'],
                    'nome' => $setor['NOME'],
                    'selected' => 'selected',
                    'disabled' => ''
                ]);
            }

            $select = View::render('utils/select', [
                'class' => '',
                'name' => 'slSetoresEnvolvidosVisualizacao',
                'id' => '',
                'style' => '',
                'disabled' => '',
                'options' => $options,
                'required' => 'required',
                'multiple' => ''
            ]);

            // get all identificacoes in the database
            $identificacoes = Manifestacao::getAllIdentificacoes();
    
            $identificacaoCodigoAtual = $linha_manifestacao['IDENTIFICACAO_CODIGO'];
            $identificacaoDescricaoAtual = $linha_manifestacao['IDENTIFICACAO_DESCRICAO'];

            // create radio buttons for each identificacao
            $identificacoes_manifestacao = '';
            $i = 1;
            $j = 1;
            $rbs_atual = '';
            foreach ($identificacoes as $identificacao) {

                $checked = '';
    
                if($identificacao['codigo'] == $identificacaoCodigoAtual){
                    $checked = 'checked';
                }
    
                $rbs_atual .= View::render('ouvimed/visualizar/rb', [
                    'id' => 'rbIdentificacao' . $i,
                    'name' => 'rbIdentificacao' . $j,
                    'value' => $identificacao['codigo'],
                    'checked' => $checked,
                    'disabled' => 'disabled',
                    'label_for' =>  'rbIdentificacao' . $i,
                    'label_text' => $identificacao['descricao'],
                ]);
                    
                $i++;
                $j++;
            }

            // create text area with rbs and select
            $sim .= View::render('ouvimed/visualizar/rb_plus_ta', [
                'radio_buttons' => $rbs_atual,
                'select_setores_envolvidos' => $select,
                'ta_id' => 'taIdentificacao' . $linha_manifestacao['id'],
                'ta_disabled' => 'disabled',
                'ta_text' => $identificacaoDescricaoAtual,
            ]);

        }
     
        // Botões de status, variam de acordo com o status da manifestacao
        if($manifestacao[0]['STATUS'] == 'A'){
            $statusButtons = View::render('ouvimed/visualizar/buttonsAberto', []);
        } else if($manifestacao[0]['STATUS'] == 'EA'){
            $statusButtons = View::render('ouvimed/visualizar/buttonsEmProcessamento', []);
        } else if($manifestacao[0]['STATUS'] == 'F'){
            $statusButtons = View::render('ouvimed/visualizar/buttonsFinalizado', []);
        }

        $content = View::render('ouvimed/manifestacao', [
            'id' => $id,
            'autor' => $manifestacao[0]['NOME_AUTOR'],
            'data_manifestacao' => substr($manifestacao[0]['DTHR_MANIFESTACAO'], 0, 10),
            'hora_manifestacao' => substr($manifestacao[0]['DTHR_MANIFESTACAO'], 11, 5),
            'nome_paciente' => $manifestacao[0]['NOME_PACIENTE'],
            'registro_paciente' => $manifestacao[0]['REGISTRO_PACIENTE'],
            'telefone_paciente' => $manifestacao[0]['TELEFONE'],
            'checked_presencial' => $manifestacao[0]['VEICULO'] == 'PRESENCIAL' ? 'checked' : '',
            'checked_telefone' => $manifestacao[0]['VEICULO'] == 'TELEFONE' ? 'checked' : '',
            'checked_caixa_sugestoes' => $manifestacao[0]['VEICULO'] == 'CAIXA_SUGESTOES' ? 'checked' : '',
            'checked_email' => $manifestacao[0]['VEICULO'] == 'EMAIL' ? 'checked' : '',
            'checked_rede_social' => $manifestacao[0]['VEICULO'] == 'REDE_SOCIAL' ? 'checked' : '',
            'checked_outro' => $manifestacao[0]['VEICULO'] == 'OUTRO' ? 'checked' : '',
            'show_input_rede_social' => $manifestacao[0]['VEICULO'] == 'REDE_SOCIAL' ? '' : 'd-none',
            'rede_social' => $manifestacao[0]['VEICULO_DESCRICAO'],
            'show_input_outro' => $manifestacao[0]['VEICULO'] == 'OUTRO' ? '' : 'd-none',
            'outro' => $manifestacao[0]['VEICULO_DESCRICAO'],
            'identificacoes_manifestacao' => $sim,
            'display_acoes_tomadas_title' => $displayAcoesTitle,
            'acoes_tomadas' => $acoesTomadasInputs,
            'status_buttons' => $statusButtons
        ]);

        return parent::getPage('Gestão de Manifestações/OuviMed - Manifestação '.$id, 'OuviMed', $content, $request);

    }

    // Adquire os dados da manifestação para montar um PDF
    public static function getManifestacaoDataPDF(Request $request, $id){

        $manifestacao = Manifestacao::getManifestacao($id);

        // es hora de gerar o protoloco de cada manifestação no formato nº da manigestação do ano / ano
        foreach($manifestacao as $key => $man){
            $manifestacao[$key]['PROTOCOLO'] = Manifestacao::getProtocolo($manifestacao[$key]);
        }
        
        // Adquire as acoes tomadas da manifestação
        $acoesTomadas = Manifestacao::getAcoesTomadas($id);

        $resultado = [];
        $resultado['PROTOCOLO'] = $manifestacao[0]['PROTOCOLO'];
        $resultado['NOME_AUTOR'] = $manifestacao[0]['NOME_AUTOR'];
        $resultado['NOME_PACIENTE'] = $manifestacao[0]['NOME_PACIENTE'];
        $resultado['REGISTRO_PACIENTE'] = $manifestacao[0]['REGISTRO_PACIENTE'];
        $resultado['TELEFONE'] = $manifestacao[0]['TELEFONE'];
        $resultado['DTHR_MANIFESTACAO'] = $manifestacao[0]['DTHR_MANIFESTACAO'];
        $resultado['VEICULO'] = $manifestacao[0]['VEICULO'];
        $resultado['VEICULO_DESCRICAO'] = $manifestacao[0]['VEICULO_DESCRICAO'] ?? '';
        
        $resultado['IDENTIFICACOES'] = [];
        foreach($manifestacao as $linha_manifestacao){
            $resultado['IDENTIFICACOES'][] = [
                'IDENTIFICACAO' => $linha_manifestacao['IDENTIFICACAO_CODIGO'],
            ];
        }

        $resultado['DESCRICAO_MANIFESTACAO'] = '';
        foreach($manifestacao as $linha_manifestacao){
            // se desc atual não terminar com . adiciona um .
            if(substr($linha_manifestacao['IDENTIFICACAO_DESCRICAO'], -1) != '.'){
                $linha_manifestacao['IDENTIFICACAO_DESCRICAO'] .= '.';
            }
            $resultado['DESCRICAO_MANIFESTACAO'] .= $linha_manifestacao['IDENTIFICACAO_DESCRICAO'] . ' ';
        }

        $resultado['SETORES_ENVOLVIDOS'] = '';
        foreach($manifestacao as $linha_manifestacao){
            $resultado['SETORES_ENVOLVIDOS'] .= $linha_manifestacao['SETORES_ENVOLVIDOS'] . ', ';
        }
        $resultado['SETORES_ENVOLVIDOS'] = substr($resultado['SETORES_ENVOLVIDOS'], 0, -2);

        $resultado['ACOES_TOMADAS'] = '';
        foreach($acoesTomadas as $linha_acao){
            // se acao atual não terminar com . adiciona um .
            if(substr($linha_acao['acao'], -1) != '.'){
                $linha_acao['acao'] .= '.';
            }
            $resultado['ACOES_TOMADAS'] .= $linha_acao['acao'] . ' ';
        }

        // Depois alterar isso para a data/hora em que a manifestação foi finalizada
        $resultado['DTHR_RESOLVIDO'] = $acoesTomadas[count($acoesTomadas) - 1]['dthr_registro'];

        return $resultado;
                                

    }
   
}