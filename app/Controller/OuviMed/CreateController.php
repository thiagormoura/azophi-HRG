<?php

namespace App\Controller\OuviMed;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Http\Request;
use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use \App\Model\OuviMed\Setor;
use \App\Model\OuviMed\Manifestacao;
use \App\Model\Entity\User;

class CreateController extends LayoutPage
{

    public static function getCreatePage(Request $request)
    {

        $content = View::render('ouvimed/criar_manifestacao', [
        ]);

        return parent::getPage('Criar Manifestação/Ouvimed - Registrar Manifestação', 'OuviMed', $content, $request);
    }

    public static function getNewIdentificacaoElement(Request $request)
    {

        $actual_id = $request->getPostVars()['id'];
        
        // get all identificacao in database and create radio buttons for each
        $identificacao = Manifestacao::getAllIdentificacoes();
        $identificacaoElement = '';
        foreach ($identificacao as $ident) {
            $identificacaoElement .= View::render('ouvimed/criar/rb', [
                'id' => $ident['codigo'].$actual_id,
                'name' => 'identificacao'.$actual_id,
                'value' => $ident['codigo'],
                'label_for' => $ident['codigo'].$actual_id,
                'label_text' => $ident['descricao']

            ]);
        }

        // get view select
        $options = '<option value="" selected disabled>Selecione os setores</option>';
        foreach (Setor::getAllSectors() as $setor) {
            $options .= View::render('utils/option', [
                'id' => $setor['id'],
                'nome' => $setor['nome']
            ]);
        }

        $select = View::render('utils/select', [
            'options' => $options,
            'required' => 'required',
            'name' => 'slSetoresEnvolvidos',
            'id' => 'slSetoresEnvolvidos'.$actual_id,
            'class' => '',
            'style' => '',
            'multiple' => '',
            'disabled' => ''
        ]);

        // create text area with rbs and select
        $ta = View::render('ouvimed/criar/rb_plus_ta', [
            'radio_buttons' => $identificacaoElement,
            'ta_name' => 'taIdentificacao'.$actual_id,
            'name' => 'IdentificacaoRow'.$actual_id,
            'select_setores_envolvidos' => $select,
            'ta_id' => 'taIdentificacao'.$actual_id,
        ]);

    

        return $ta;

    }

    public static function createManifestacao(Request $request)
    {

        $dados = $request->getPostVars();

        $id = Manifestacao::createManifestacao($dados);

        foreach($dados['identificacoes_manifestacao'] as $key => $identificacao) {
            $id_manifestacao_identificacao = Manifestacao::setIdentificacaoAndDescricaoOnCreateManifestacao($id, $identificacao['identificacao'], $identificacao['descricao']);
            foreach($dados['setores_envolvidos'][$key] as $setor){
                Manifestacao::setSetorEnvolvidoOnCreteManifestacao($id_manifestacao_identificacao, $setor);
            }
        }

        return [true];
    }

    // Cancela solicitação pelo id
    public static function cancelarManifestacao(Request $request, $id)
    {
        Manifestacao::cancelarManifestacao($id);
        return [true];
    }

    // Retorna a pagina de edição da manifestação
    public static function getEditarManifestacaoPage(Request $request, $id)
    {

        // Adquire os dados da manifestação
        $manifestacao = Manifestacao::getManifestacao($id);

        // Adquire todos os setores do hospital e procura os setores envolvidos na manifestação
        $setores = Setor::getAllSectors();

        // Para cada linha da manifestação (cada identificação)
        $sim = '';
        $k = 0 ;
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
            foreach (Setor::getAllSectors() as $setor) {
                
                // Se o setor estiver envolvido na manifestação, ele é selecionado
                if(in_array($setor['id'], $setoresEnvolvidosCodigoAtual)){

                    $options .= View::render('utils/option', [
                        'id' => $setor['id'],
                        'nome' => $setor['nome'],
                        'selected' => 'selected',
                        'disabled' => ''
                    ]);
                } 
                
                else {
                    $options .= View::render('utils/option', [
                        'id' => $setor['id'],
                        'nome' => $setor['nome'],
                        'selected' => '',
                        'disabled' => ''
                    ]);
                }
            }

            $select = View::render('utils/select', [
                'class' => '',
                'name' => 'slSetoresEnvolvidosEdicao',
                'id' => '',
                'style' => '',
                'disabled' => '',
                'options' => $options,
                'required' => 'required',
                'multiple' => 'multiple'
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
    
                $rbs_atual .= View::render('ouvimed/editar/rb', [
                    'id' => 'rbIdentificacao' . $i,
                    'name' => 'identificacao-' . $k,
                    'value' => $identificacao['codigo'],
                    'checked' => $checked,
                    'disabled' => '',
                    'label_for' =>  'rbIdentificacao' . $i,
                    'label_text' => $identificacao['descricao'],
                ]);
                    
                $i++;
                $j++;
            }

            // create text area with rbs and select
            $sim .= View::render('ouvimed/editar/rb_plus_ta', [
                'row_name' => 'IdentificacaoRow' . $linha_manifestacao['id'],
                'radio_buttons' => $rbs_atual,
                'select_setores_envolvidos' => $select,
                'ta_id' => 'taIdentificacao-' . $linha_manifestacao['id'],
                'ta_disabled' => '',
                'ta_text' => $identificacaoDescricaoAtual,
            ]);

            $k++;

        }

        // Botões de status, variam de acordo com o status da manifestacao
        $statusButtons = ($manifestacao[0]['STATUS'] == 'A') ? View::render('ouvimed/editar/buttonsEditar', []) : '';

        $content = View::render('ouvimed/editar_manifestacao', [
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
            'descricao_manifestacao' => $manifestacao[0]['DESCRICAO'],
            'select_setores_envolvidos' => $select,
            'status_buttons' => $statusButtons,
            'identificacoes' => $sim,
        ]);

        return parent::getPage('Editar Manifestação/OuviMed - Editar Manifestação '.$id, 'OuviMed', $content, $request);
    }

    // Edita a manifestação
    public static function editarManifestacao(Request $request, $id)
    {
        $dados = $request->getPostVars();

        Manifestacao::editarManifestacao($dados, $id);

        // Deleta as identificações, devido a fk os setores envolvidos são deletados automaticamente
        Manifestacao::deleteIdentificacoes($id);

        // Para cada identificação, adiciona no banco e para cada setor envolvido na mesma adiciona no banco
        foreach($dados['identificacoes_manifestacao'] as $key => $identificacao) {
            $id_manifestacao_identificacao = Manifestacao::setIdentificacaoAndDescricaoOnCreateManifestacao($id, $identificacao['identificacao'], $identificacao['descricao']);
            foreach($dados['setores_envolvidos'][$key] as $setor){
                Manifestacao::setSetorEnvolvidoOnCreteManifestacao($id_manifestacao_identificacao, $setor);
            }
        }

        return [true];
    }

    public static function processarManifestacao(Request $request, $id)
    {
        Manifestacao::processarManifestacao($id);
        return [true];
    }

    public static function cancelarProcessamento(Request $request, $id)
    {
        Manifestacao::cancelarProcessamento($id);
        return [true];
    }

    public static function getAtualizarAcaoPage(Request $request, $id)
    {

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
        
        // Se o id do usuario corrente for o mesmo id do usuario que fez o registro da manifestação, então
        // habilita a edição dos campos de ação tomada
        
        $displayAcoesTitle = 'd-none';
        if ($manifestacao[0]['STATUS'] != 'A') {
            $acoesTomadasInputs = '';
            foreach ($acoesTomadas as $acaoTomada) {
                $acoesTomadasInputs .= View::render('ouvimed/atualizarAcao/textarea_plus_dthr_input', [
                    'inUsuario_value' => $acaoTomada['usuario_registro'],
                    'inUsuario_disabled' => 'disabled',
                    'ta_id' => 'taAcaoTomada' . $acaoTomada['id'],
                    'ta_disabled' => $acaoTomada['usuario'] == $request->user->id ? '' : 'disabled',
                    'ta_text' => $acaoTomada['acao'],
                    'inDate_id' => 'inDataAcaoTomada' . $acaoTomada['id'],
                    'inDate_value' => substr($acaoTomada['dthr_registro'], 0, 10),
                    'inDate_disabled' => $acaoTomada['usuario'] == $request->user->id ? '' : 'disabled',
                    'inHour_id' => 'inHoraAcaoTomada' . $acaoTomada['id'],
                    'inHour_value' => substr($acaoTomada['dthr_registro'], 11, 5),
                    'inHour_disabled' => $acaoTomada['usuario'] == $request->user->id ? '' : 'disabled',
                    'btnRemover_disabled' => $acaoTomada['usuario'] == $request->user->id ? '' : 'disabled',
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

        // Botões de status
        $statusButtons = View::render('ouvimed/atualizarAcao/buttonsAtualizarAcao', []);

        $content = View::render('ouvimed/atualizarAcao', [
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
            'display_acoes_tomadas_title' => $displayAcoesTitle,
            'acoes_tomadas' => $acoesTomadasInputs,
            'status_buttons' => $statusButtons,
            'identificacoes_manifestacao' => $sim,
        ]);

        return parent::getPage('Gestão de Manifestações/Ouvimed - Atualizar Ação', 'OuviMed', $content, $request);
    }

    public static function AtualizarAcao(Request $request, $id)
    {
        $dados = $request->getPostVars();
        $acoesTomadas = $dados['acoesTomadas'];
        $dthr_acoes = $dados['dthrAcoesTomadas'];
        $id_usuario = $request->user->id;
        Manifestacao::atualizarAcao($id, $acoesTomadas, $dthr_acoes, $id_usuario);
        return [true];
    }

    public static function finalizarProcessamento(Request $request, $id)
    {
        Manifestacao::finalizarProcessamento($id);
        return [true];
    }
}
