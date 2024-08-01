<?php

namespace App\Controller\SosMaqueiro;

use \App\Controller\Layout\Layout as LayoutPage;
use App\Http\Request;
use App\Model\SosMaqueiro\Local;
use App\Model\SosMaqueiro\Setor;
use App\Model\Entity\User;
use App\Model\SosMaqueiro\Paciente;
use App\Model\SosMaqueiro\Recurso;
use App\Model\SosMaqueiro\Solicitation;
use App\Model\SosMaqueiro\Transporte;
use App\Utils\View;
use DateTime;
use DateTimeZone;
use \App\Model\Utils\Spy;

class Home extends LayoutPage
{

  /**
   * Constante responsável por definir a cor do status do chamado
   * @var array
   */
  const statusColor = [
    'aberto' => 'bg-danger',
    'atendimento' => 'bg-warning',
    'pausado' => 'bg-light',
    'finalizado' => 'bg-success',
    'cancelado' => 'bg-secondary',
    // 'aberto' => 'bg-primary',
    // 'atendimento' => 'bg-warning',
    // 'pausado' => 'bg-secondary',
    // 'finalizado' => 'bg-success',
    // 'cancelado' => 'bg-danger',
  ];


  /**
   * Método responsável por retornar o html das últimas atualizações do chamado
   * @param string $status Status atual do chamado
   * @param string $schedule Horario da ultima atualização
   * @param array|object $attendant Usuário atendente que atualizou o chamado
   * @return string Retorna o html da última atualização do chamado
   */
  private static function getLastUpdate($status, $schedule, $attendant)
  {
    $solicitationClass = 'badge bg-danger';
    $currentUpdate = '<i class="fas fa-exclamation-circle"></i> Não informado';
    if ($attendant) {
      $currentUpdate = $schedule . ' - ' . $attendant->nome . ' ' . $attendant->sobrenome;
      $solicitationClass = 'badge ' . self::statusColor[$status];
    }

    return View::render('sosmaqueiro/chamado/chamado_atualizacao', [
      'class' => $solicitationClass,
      'atualizacao' => $currentUpdate,
    ]);
  }

  /**
   * Método responsável por remover todos os acentos de uma string
   * @param string $string String a ser removido os acentos
   * @param return string String sem acentos
   */
  private static function removeAccent(string $string)
  {
    return str_replace('?', '', preg_replace(array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/", "/(í|ì|î|ï)/", "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/", "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/", "/(ñ)/", "/(Ñ)/"), explode(" ", "a A e E i I o O u U n N"), $string));
  }
  /**
   * Método responsável por retornar as linhas da tabela de solicitações 
   * @param array $solicitations Solicitações que serão exibidas
   * @return string Todas as linhas da tabela
   */
  private static function getSolicitation(array $solicitations, bool $isSolicitant): string
  {
    $content = '';
    $currentTimeZone = new DateTimeZone(CURRENT_TIMEZONE);
    
    foreach ($solicitations as $solicitation) {
      $resources = Recurso::getRecursosByChamado($solicitation->id);
      $resourceContent = implode(', ', array_map(function ($resource) {
        return $resource->nome;
      }, $resources));
      
      $solicitantUser = User::getUserById($solicitation->id_usuario_solicitante);
      
      $attendantUser = false;
      if ($solicitation->id_usuario_atendente !== NULL)
      $attendantUser = User::getUserById($solicitation->id_usuario_atendente);
      
      $destiny = Local::getLocalByCodigo($solicitation->destino);
      if (!$destiny)
      $destiny = Setor::getSetorByCodigo($solicitation->destino);
      
      $patient = Paciente::getPacienteByRegistro($solicitation->paciente);
      
      $lastUpdateDate = new DateTime($solicitation->dthr_atualizacao, $currentTimeZone);
      $solicitationDate = new DateTime($solicitation->dthr_solicitacao, $currentTimeZone);
          
      $content = View::render('sosmaqueiro/chamado/tabela_linha', [
        'id' => $solicitation->id,
        'data-atualizacao' => View::render('sosmaqueiro/chamado/data_atualizacao', [
          'acao' => self::getLastUpdate($solicitation->status, $lastUpdateDate->format('d/m/Y H:i:s'), $attendantUser),
          'horario-solicitacao' => $solicitationDate->format('d/m/Y H:i:s'),
          'solicitante' => $solicitantUser->nome . ' ' . $solicitantUser->sobrenome
        ]),
        'paciente' => self::removeAccent($patient['pac_nome']) ?? 'Não identificado',
        'setor' => self::removeAccent($patient['pac_unidade_nome']) ?? 'Não identificado',
        'local' => self::removeAccent($patient['pac_local_nome']) ?? 'Não identificado',
        'setor-solicitante' => $solicitation->setor_solicitante,
        'destino' => $destiny->nome,
        'usando' => $solicitation->transporte,
        'recurso' => $resourceContent,
        'status-color' => self::statusColor[$solicitation->status],
        'status' => ucfirst($solicitation->status),
        'observacoes' => $solicitation->observacao,
        'buttons' => $isSolicitant ? '' : View::render('sosmaqueiro/buttons/' . $solicitation->status, [
          'margin' => 'mt-1',
        ])
      ]);
    }
    return $content;
  }

  /**
   * Método responsável por retornar as linhas da tabela de solicitações 
   * @param array $solicitations Solicitações que serão exibidas
   * @return array Todas as linhas da tabela separadamente
   */
  private static function getSolicitationColumns(array $solicitations, bool $isSolicitant): array
  {
    $content = [];
    $currentTimeZone = new DateTimeZone(CURRENT_TIMEZONE);
    
    $cont = 0;
    
    foreach ($solicitations as $solicitation) {
      $resources = Recurso::getRecursosByChamado($solicitation->id);
      $resourceContent = implode(', ', array_map(function ($resource) {
        return $resource->nome;
      }, $resources));
      
      $solicitantUser = User::getUserById($solicitation->id_usuario_solicitante);
      
      $attendantUser = false;
      if ($solicitation->id_usuario_atendente !== NULL)
      $attendantUser = User::getUserById($solicitation->id_usuario_atendente);
      
      $destiny = Local::getLocalByCodigo($solicitation->destino);
      if (!$destiny)
      $destiny = Setor::getSetorByCodigo($solicitation->destino);
      
      $patient = Paciente::getPacienteByRegistro($solicitation->paciente);
      
      $lastUpdateDate = new DateTime($solicitation->dthr_atualizacao, $currentTimeZone);
      $solicitationDate = new DateTime($solicitation->dthr_solicitacao, $currentTimeZone);
        
      $content[$cont]['id'] = "<p class='id' data-id='".$solicitation->id."'>".$solicitation->id."</p>";
      $content[$cont]['data-atualizacao'] = $lastUpdateDate->format('Y-m-d H:i:s');
      $content[$cont]['maqueiro-selecionado'] = $solicitation->usuario_atendente;
      $content[$cont]['paciente-local'] = View::render('sosmaqueiro/chamado/paciente', [
        'paciente' => self::removeAccent($patient['pac_nome']) ?? 'Não identificado',
        'setor' => self::removeAccent($patient['pac_unidade_nome']) ?? 'Não identificado',
        'local' => self::removeAccent($patient['pac_local_nome']) ?? 'Não identificado'
      ]);
      $content[$cont]['setor-solicitante'] = $solicitation->setor_solicitante;
      $content[$cont]['uso-destino'] = View::render('sosmaqueiro/chamado/uso_destino', [
        'usando' => $solicitation->transporte,
        'destino' => $destiny->nome
      ]);
      $content[$cont]['recurso-obs'] = View::render('sosmaqueiro/chamado/recurso_obs', [
        'recurso' => $resourceContent,
        'observacoes' => $solicitation->observacao
      ]);
      $content[$cont]['status'] = View::render('sosmaqueiro/chamado/status_span', [
        'status-color' => self::statusColor[$solicitation->status],
        'status' => ucfirst($solicitation->status)
      ]);
      $content[$cont++]['buttons'] = View::render('sosmaqueiro/buttons/' . $solicitation->status, [
          'margin' => 'mt-1',
          'id' => $solicitation->status == 'cancelado' || $solicitation->status == 'pausado' ? $solicitation->id : ''
        ]);
    }
    return $content;
  }

  /**
   * Método responsável por retornar o modal de maqueiros
   * @param object $user Usuário da requisição
   * @return string HTML do modal que possui o select de maqueiros
   */
  private static function getStretcherBearersModal(object $user)
  {
    $maqueiros = User::getUsersByPermissao('sosmaqueiro-atender');
    $options = '';
    foreach ($maqueiros as $maqueiro) {
      if ($maqueiro->id === $user->id)
        continue;
      $options .= View::render('utils/option', [
        'id' => $maqueiro->id,
        'nome' => $maqueiro->nome . ' ' . $maqueiro->sobrenome,
      ]);
    }
    return View::render('sosmaqueiro/chamado/modal_transferencia', [
      'options' => $options,
    ]);
  }

  /**
   * Método responsável por retornar a página de solicitações
   * @param Request $request Requisição do usuário
   * @return string Página de solicitações
   */
  public static function getSolicitations(Request $request)
  {
    $user = $request->user;
    $isSolicitant = parent::checkPermissao($user, 'sosmaqueiro-solicitar', "");

    $content = View::render('sosmaqueiro/chamados', [
      'cancel-modal' =>  View::render('sosmaqueiro/chamado/modal_cancelar'),
      'transfer-modal' => self::getStretcherBearersModal($user)
    ]);

    // Atualiza o acesso do usuario nesse sistema
    Spy::updateAcess($request->user, 5, 'sos_maqueiro');

    return parent::getPage('SOS Maqueiro', 'sosmaqueiro', $content, $request);
  }

  /**
   * Método responsável por retornar os recursos para a solicitação
   * @return string HTML dos recursos disponíves para a solicitação
   */
  private static function getResources()
  {
    $resources = Recurso::getRecursos();
    $checkboxes = '';
    foreach ($resources as $resource) {
      $checkboxes .= View::render('utils/checkbox', [
        'id' => 'recursos-' . $resource->id,
        'value' => $resource->id,
        'label' => $resource->nome,
        'name' => 'recurso[]',
        'required' => 'required',
      ]);
    }
    return $checkboxes;
  }

  /**
   * Método responsável por retornar os transportes para a solicitação
   * @return string HTML dos transportes disponíves para a solicitação
   */
  private static function getTransports()
  {
    $transportes = Transporte::getTransportes();
    $radios = '';
    foreach ($transportes as $transporte) {
      $radios .= View::render('utils/radio_button', [
        'id' => 'transporte-' . $transporte->id,
        'value' => $transporte->id,
        'label' => $transporte->nome,
        'name' => 'transporte',
        'required' => 'required'
      ]);
    }
    return $radios;
  }

  /**
   * Método responsável por retornar os pacientes para a solicitação
   * @return string HTML dos transportes pacientes para a solicitação
   */
  private static function getPatients()
  {
    $patients = Paciente::getPacientes();
    $options = '';
    foreach ($patients as $patient) {
      $options .= View::render('utils/option', [
        'id' => $patient['pac_registro'],
        'nome' => $patient['pac_nome'] . ' - ' . $patient['pac_unidade_nome'] . ' - ' . $patient['pac_local_nome'],
        'selected' => '',
        'disabled' => '',
      ]);
    }
    return $options;
  }

  /**
   * Método responsável por retornar os locais para a solicitação
   * @return string HTML dos locais disponíves para a solicitação
   */
  private static function getLocals()
  {
    $locais = Local::getLocaisAndSetores();
    $options = '';
    foreach ($locais as $local) {
      $options .= View::render('utils/option', [
        'id' => $local->codigo !== NULL ? $local->codigo : $local->setor_codigo,
        'nome' => $local->codigo !== NULL ? ($local->setor_codigo === NULL ? $local->local : $local->setor . ' - ' . $local->local) : $local->setor,
        'selected' => '',
        'disabled' => '',
      ]);
    }
    return $options;
  }

  /**
   * Método responsável por retornar os setores para a solicitação
   * @return string HTML dos setores disponíves para a solicitação
   */
  private static function getSolicitationUnit()
  {
    $setores = Setor::getSetores();
    $options = '';
    foreach ($setores as $setor) {
      $options .= View::render('utils/option', [
        'id' => $setor->codigo,
        'nome' => $setor->nome,
        'selected' => '',
        'disabled' => '',
      ]);
    }
    return $options;
  }

  /**
   * Método responsável por retornar o modal para solicitar maqueir
   * @param Request $request Requisição do usuário
   * @return string Modal para solicitar maqueiro
   */
  public static function getSolicitationModal(Request $request)
  {
    return View::render('sosmaqueiro/solicitar_maqueiro', [
      'resources' => self::getResources(),
      'transport' => self::getTransports(),
      'options-pacient' => self::getPatients(),
      'options-solicitant' => self::getSolicitationUnit(),
      'options-destiny' => self::getLocals(),
    ]);
  }

  /**
   * Método responsável por inserir uma solicitação para os maqueiros
   * @param Request $request Requisição do usuário
   * @return array Retorna um array com o status da solicitação e a mensagem de erro ou sucesso
   */
  public static function setSolicitation(Request $request)
  {
    $postVars = $request->getPostVars();
    $user = $request->user;

    $requireds = array(
      'paciente',
      'setor_solicitante',
      'recurso',
      'transporte',
      'destino'
    );

    foreach ($requireds as $required) {
      if (empty($postVars[$required]))
        return [
          'success' => false,
          'message' => 'Preencha todos os campos obrigatórios.',
        ];
    }

    $observation = $postVars['observacao'] === '' ? NULL : $postVars['observacao'];
    $solicitation = Solicitation::insertSolicitation($postVars['paciente'], $postVars['setor_solicitante'], $postVars['destino'], $postVars['transporte'], $user->id, $observation);

    foreach ($postVars->recurso as $resource) {
      Solicitation::insertResource($solicitation, $resource);
    }

    return array(
      'success' => true,
      'message' => 'Solicitação realizada com sucesso!',
    );
  }

  /**
   * Método responsável por retornar as informações das solicitações (canceladas e pausadas)
   * @param Request $request Requisição do usuário
   * @param string|int $solicitation Id da solicitação
   * @param string $situation Status da solicitação atual (pausada ou cancelada)
   * @return string HTML com o modal de detalhes da solicitação 
   */
  public static function getSolicitationInfo(Request $request, string|int $solicitacao, string $situacao)
  {
    $solicitacao = Solicitation::getChamadoById($solicitacao);
    if (!$solicitacao || $solicitacao->status !== $situacao)
      return;
    $usuario_operacao = User::getUserById($solicitacao->id_usuario_atendente);
    $tipo_operacao = 'do cancelamento';
    $motivo = $solicitacao->motivo_cancelamento;

    if ($situacao === 'pausado') {
      $tipo_operacao = 'da pausa';
      $motivo = Solicitation::getMotivoPausa($solicitacao->id);
    }

    return View::render('sosmaqueiro/chamado/modal_cancelado', [
      'tipo-modal' => $tipo_operacao,
      'usuario' => $usuario_operacao->nome . ' ' . $usuario_operacao->sobrenome,
      'motivo' => $motivo,
    ]);
  }

  /**
   * Método responsável por retornar as informações das solicitações atualizadas
   * @param object $solicitation Solicitação
   * @return array Informações atualizadas da solicitação
   */
  private static function getSolicitationDetails(object $solicitation)
  {
    // Verifica se o chamado possui um usuário atendente
    $attendantUser = false;
    if ($solicitation->id_usuario_atendente !== NULL)
      $attendantUser = User::getUserById($solicitation->id_usuario_atendente);

    $currentTimeZone = new DateTimeZone(CURRENT_TIMEZONE);
    $lastUpdateData = new DateTime($solicitation->dthr_atualizacao, $currentTimeZone);

    $action = self::getLastUpdate(
      $solicitation->status,
      $lastUpdateData->format('d/m/Y H:i:s'),
      $attendantUser
    );

    return [
      'id' => $solicitation->id,
      'action' => $action,
      'status_color' =>  self::statusColor[$solicitation->status],
      'status' => ucfirst($solicitation->status),
      'buttons' => View::render('sosmaqueiro/buttons/' . $solicitation->status, [
        'margin' => 'mt-1',
      ])
    ];
  }

  private static function acceptSolicitation(object $solicitation, array $data)
  {
    if (!$solicitation->id_usuario_atendente)
      return array(
        'success' => false,
        'code' => 'MAQ',
        'message' => 'É necesário definir um maqueiro para atender esta solicitação.'
      );

    if ($solicitation->status === 'atendimento')
      return array(
        'success' => false,
        'message' => 'Solicitação já está em atendimentos'
      );

    Solicitation::updateChamado($solicitation->id, $solicitation->id_usuario_atendente, 'atendimento');
    $solicitation->status = 'atendimento';
    return [
      'success' => true,
      'message' => 'Solicitação iniciada!',
      'content' => self::getSolicitationDetails($solicitation),
    ];
  }

  private static function transferSolicitation(object $solicitation, array $data)
  {
    if (!$data['user'])
      return [
        'success' => false,
        'message' => 'É necessário selecionar um usuário para transferir uma solicitação.'
      ];

    Solicitation::insertTransferencia($solicitation->id, $data['operator'], $data['user'], 'transferencia');
    Solicitation::transferirChamado($solicitation->id, $data['user']);
    $solicitation->id_usuario_atendente = $data['user'];

    $operation = 'transferida';

    if ($data['action'] === 'atribuir')
      $operation = 'atribuída';

    return [
      'success' => true,
      'message' => 'Solicitação ' . $operation . ' com sucesso!',
      'content' => self::getSolicitationDetails($solicitation),
    ];
  }

  private static function cancelSolicitation(object $solicitation, array $data)
  {
    if ($solicitation->status !== 'aberto')
      return [
        'success' => false,
        'message' => 'Você não pode mais cancelar está solicitação.'
      ];
    if (!$data['justification'])
      return [
        'success' => false,
        'message' => 'É necessário inserir o motivo do cancelamento.'
      ];
    Solicitation::cancelarChamado($solicitation->id, $data['operator'], $data['justification']);
    $solicitation->status = 'cancelado';
    return [
      'success' => true,
      'message' => 'Solicitação cancelada com sucesso!',
      'content' => self::getSolicitationDetails($solicitation),
    ];
  }

  private static function pauseSolicitation(object $solicitation, array $data)
  {
    if ($solicitation->status !== 'atendimento')
      return [
        'success' => false,
        'message' => 'Você não pode mais pausar está solicitação.'
      ];

    if (!$data['justification'])
      return [
        'success' => false,
        'message' => 'É necessário inserir o motivo da pausa.'
      ];

    Solicitation::insertPausaChamado($solicitation->id, $data['justification']);
    Solicitation::updateChamado($solicitation->id, $data['operator'], 'pausado');
    $solicitation->status = 'pausado';

    return [
      'success' => true,
      'message' => 'Solicitação pausada com sucesso!',
      'content' => self::getSolicitationDetails($solicitation),
    ];
  }

  private static function continueSolicitation(object $solicitation, array $data)
  {
    if ($solicitation->status !== 'pausado')
      return array(
        'success' => false,
        'message' => 'Você não pode mais continuar está solicitação.'
      );

    Solicitation::continuarChamado($solicitation->id);
    Solicitation::updateChamado($solicitation->id, $data['operator'], 'atendimento');
    $solicitation->status = 'atendimento';
    return [
      'success' => true,
      'message' => 'Solicitação continuada com sucesso!',
      'content' => self::getSolicitationDetails($solicitation),
    ];
  }

  // Método responsável por atualizar o chamado 
  public static function updateChamado(Request $request, string|int $solicitation)
  {
    $solicitation = Solicitation::getChamadoById($solicitation);

    if (!$solicitation)
      return [
        'success' => false,
        'message' => 'Chamado não encontrado.'
      ];

    $data = $request->getPostVars();
    $data['operator'] = $request->user->id;

    switch (true) {
      case $data['action'] === 'aceitar':
        return self::acceptSolicitation($solicitation, $data);
        break;
      case $data['action'] === 'transferir' || $data['action'] === 'atribuir':
        return self::transferSolicitation($solicitation, $data);
        break;
      case $data['action'] === 'cancelar':
        return self::cancelSolicitation($solicitation, $data);
        break;
      case $data['action'] === 'pausar':
        return self::pauseSolicitation($solicitation, $data);
        break;
      case $data['action'] === 'continuar':
        return self::continueSolicitation($solicitation, $data);
        break;
      case 'finalizar':
        Solicitation::updateChamado($solicitation->id, $data['operator'], 'finalizado', true);
        $solicitation->status = 'finalizado';
        return [
          'success' => true,
          'message' => 'Solicitação finalizada com sucesso!',
          'content' => self::getSolicitationDetails($solicitation),
        ];
        break;
    }
  }

  public static function getTableSolicitations(Request $request)
  {
    $post = $request->getPostVars();

    if (isset($post['start']) && $post['length'] != -1) {
      $limit = intval($post['start']) . ", " . intval($post['length']);
    }

    // $order = "";
    // if (!empty($post['order'])) {
    //   $endItem = end($post['order']);
    //   $firstItem = reset($post['order']);

    //   foreach ($post['order'] as $item) {
    //     if ($item['column'] == 9 || $item['column'] == 10)
    //       continue;

    //     if ($item == $firstItem)
    //       $order = $colums[$item['column']] . " " . $item['dir'] . (count($post['order']) > 1 ? ", " : "");

    //     elseif ($item == $endItem)
    //       $order .= $colums[$item['column']] . " " . $item['dir'];

    //     else
    //       $order .= $colums[$item['column']] . " " . $item['dir'] . ", ";
    //   }
    // }

    $inputTextSearch = !empty($post['name']) ? strtoupper($post['name']) : null;

    $user = $request->user;
    $isSolicitant = parent::checkPermissao($user, 'sosmaqueiro-solicitar', "");
    $solicitations = Solicitation::getAllSolicitations($limit);
    // , $filterString

    return array(
        "draw" => isset($post['draw']) ? intval($post['draw']) : 0,
        "recordsTotal" => count($solicitations),
        "recordsFiltered" => count(Solicitation::getAllSolicitations()),
        "data" => self::getSolicitationColumns($solicitations, !empty($isSolicitant))
    );
  }


 /**
 * Método responsável por retornar o botão de abrir chamado
 * @param Request $request Requisição do usuário
 * @return string botão de abrir chamado
 */
  public static function getAbrirChamadoButton(Request $request)
  {
    $user = $request->user;
    $isSolicitant = !empty(parent::checkPermissao($user, 'sosmaqueiro-solicitar', ""));
    $content = "";
    if($isSolicitant)
      $content = View::render('sosmaqueiro/buttons/abrir_chamado');
    return $content;
  }





  /**
   * Método responsável para retornar a página do dashboard do SOS Maqueiros
   */
  public static function dashBoard(Request $request)
  {
    $content = View::render('sosmaqueiro/dashboard');
    return parent::getPage('SOS Maqueiro', 'sosmaqueiro', $content, $request);
  }

  public static function dashBoardInfos(Request $request)
  {
    $chamados = Solicitation::getChamadosHoje();

    $_chamados_hoje_naoatendido = 0;
    $_chamados_hoje_finalizados = 0;
    $_chamados_hoje_finalizados = 0;
    $_chamados_hoje_cancelados = 0;
    $_chamados_hoje_ematendimento = 0;
    $_chamados_hoje_tempo = 0; // soma do tempo de todos os chamados não cancelados
    $_chamados_hoje_total = 0;


    for ($i = 0 ; $i < sizeof($chamados) ; $i++) {
        
        $linha = $chamados[$i];
        if ($linha['status'] == 'C'){
            $_chamados_hoje_cancelados = $linha['qtd'];
            
        }   
        
        if ($linha['status'] == 'f'){
            $_chamados_hoje_finalizados = $linha['qtd'];
            $_chamados_hoje_tempo = $_chamados_hoje_tempo + $linha['tempo_b'];
            $_chamados_hoje_total = $_chamados_hoje_total + $linha['qtd'];
        }   
        
        if ($linha['status'] == 'A'){
            $_chamados_hoje_naoatendido = $linha['qtd'];
            $_chamados_hoje_tempo = $_chamados_hoje_tempo + $linha['tempo_b'];
            $_chamados_hoje_total = $_chamados_hoje_total + $linha['qtd'];
        }   
        if ($linha['status'] == 'e'){
            $_chamados_hoje_ematendimento = $linha['qtd'];
            $_chamados_hoje_tempo = $_chamados_hoje_tempo + $linha['tempo_b'];
            $_chamados_hoje_total = $_chamados_hoje_total + $linha['qtd'];
        }   
    }

    $ultima_semana = Solicitation::getChamados7Dias();

    $tempoMedio = round($_chamados_hoje_tempo / $_chamados_hoje_total, 0);

    $chamadosPorHora = Solicitation::getChamadosPorHora();

    $_chamados_hora7DiasResultado = array();

    $_h00 = 0;
    $_h01 = 0;
    $_h02 = 0;
    $_h03 = 0;
    $_h04 = 0;
    $_h05 = 0;
    $_h06 = 0;
    $_h07 = 0;
    $_h08 = 0;
    $_h09 = 0;
    $_h10 = 0;
    $_h11 = 0;
    $_h12 = 0;
    $_h13 = 0;
    $_h14 = 0;
    $_h15 = 0;
    $_h16 = 0;
    $_h17 = 0;
    $_h18 = 0;
    $_h19 = 0;
    $_h20 = 0;
    $_h21 = 0;
    $_h22 = 0;
    $_h23 = 0;

    for ($i = 0 ; $i < sizeof($chamadosPorHora) ; $i++){
            
        $_horas = $chamadosPorHora[$i];
        
        if ($_horas['hora'] == 0) { $_h00 = $_horas['qtd']; }
        if ($_horas['hora'] == 1) { $_h01 = $_horas['qtd']; }
        if ($_horas['hora'] == 2) { $_h02 = $_horas['qtd']; }
        if ($_horas['hora'] == 3) { $_h03 = $_horas['qtd']; }
        if ($_horas['hora'] == 4) { $_h04 = $_horas['qtd']; }
        if ($_horas['hora'] == 5) { $_h05 = $_horas['qtd']; }
        if ($_horas['hora'] == 6) { $_h06 = $_horas['qtd']; }
        if ($_horas['hora'] == 7) { $_h07 = $_horas['qtd']; }
        if ($_horas['hora'] == 8) { $_h08 = $_horas['qtd']; }
        if ($_horas['hora'] == 9) { $_h09 = $_horas['qtd']; }
        if ($_horas['hora'] == 10) { $_h10 = $_horas['qtd']; }
        if ($_horas['hora'] == 11) { $_h11 = $_horas['qtd']; }
        if ($_horas['hora'] == 12) { $_h12 = $_horas['qtd']; }
        if ($_horas['hora'] == 13) { $_h13 = $_horas['qtd']; }
        if ($_horas['hora'] == 14) { $_h14 = $_horas['qtd']; }
        if ($_horas['hora'] == 15) { $_h15 = $_horas['qtd']; }
        if ($_horas['hora'] == 16) { $_h16 = $_horas['qtd']; }
        if ($_horas['hora'] == 17) { $_h17 = $_horas['qtd']; }
        if ($_horas['hora'] == 18) { $_h18 = $_horas['qtd']; }
        if ($_horas['hora'] == 19) { $_h19 = $_horas['qtd']; }
        if ($_horas['hora'] == 20) { $_h20 = $_horas['qtd']; }
        if ($_horas['hora'] == 21) { $_h21 = $_horas['qtd']; }
        if ($_horas['hora'] == 22) { $_h22 = $_horas['qtd']; }
        if ($_horas['hora'] == 23) { $_h23 = $_horas['qtd']; }
        
    }
    // array chamados hora 7Dias
    array_push($_chamados_hora7DiasResultado , array("y" => $_h00 ,"x" => 0)); 
    array_push($_chamados_hora7DiasResultado , array("y" => $_h01 ,"x" => 1)); 
    array_push($_chamados_hora7DiasResultado , array("y" => $_h02 ,"x" => 2)); 
    array_push($_chamados_hora7DiasResultado , array("y" => $_h03 ,"x" => 3)); 
    array_push($_chamados_hora7DiasResultado , array("y" => $_h04 ,"x" => 4)); 
    array_push($_chamados_hora7DiasResultado , array("y" => $_h05 ,"x" => 5)); 
    array_push($_chamados_hora7DiasResultado , array("y" => $_h06 ,"x" => 6)); 
    array_push($_chamados_hora7DiasResultado , array("y" => $_h07 ,"x" => 7)); 
    array_push($_chamados_hora7DiasResultado , array("y" => $_h08 ,"x" => 8)); 
    array_push($_chamados_hora7DiasResultado , array("y" => $_h09 ,"x" => 9)); 
    array_push($_chamados_hora7DiasResultado , array("y" => $_h10 ,"x" => 10)); 
    array_push($_chamados_hora7DiasResultado , array("y" => $_h11 ,"x" => 11)); 
    array_push($_chamados_hora7DiasResultado , array("y" => $_h12 ,"x" => 12)); 
    array_push($_chamados_hora7DiasResultado , array("y" => $_h13 ,"x" => 13)); 
    array_push($_chamados_hora7DiasResultado , array("y" => $_h14 ,"x" => 14));
    array_push($_chamados_hora7DiasResultado , array("y" => $_h15 ,"x" => 15)); 
    array_push($_chamados_hora7DiasResultado , array("y" => $_h16 ,"x" => 16)); 
    array_push($_chamados_hora7DiasResultado , array("y" => $_h17 ,"x" => 17)); 
    array_push($_chamados_hora7DiasResultado , array("y" => $_h18 ,"x" => 18)); 
    array_push($_chamados_hora7DiasResultado , array("y" => $_h19 ,"x" => 19)); 
    array_push($_chamados_hora7DiasResultado , array("y" => $_h20 ,"x" => 20)); 
    array_push($_chamados_hora7DiasResultado , array("y" => $_h21 ,"x" => 21)); 
    array_push($_chamados_hora7DiasResultado , array("y" => $_h22 ,"x" => 22)); 
    array_push($_chamados_hora7DiasResultado , array("y" => $_h23 ,"x" => 23));

    // -- array chamados hora 7Dias

    $_chamados_diaSemanaResultado = array();
    $_chamados_diaSemana = Solicitation::getChamadosPorDiasDaSemana();

    for ($i = 0 ; $i < sizeof($_chamados_diaSemana) ; $i++){
        $linha = $_chamados_diaSemana[$i];
        array_push($_chamados_diaSemanaResultado,array("label" => $linha['dia'], "y" => $linha['qtd']));
    }

    return [
      "total" => $_chamados_hoje_total > 1 ? $_chamados_hoje_total." Solicitações" : $_chamados_hoje_total." Solicitação",
      "tempo" => $tempoMedio > 1 ? $tempoMedio." Minutos" : $tempoMedio." Minuto",
      "naoAtendidos" => $_chamados_hoje_naoatendido > 1 ? $_chamados_hoje_naoatendido." Solicitações" : $_chamados_hoje_naoatendido." Solicitação",
      "emAtendimento" => $_chamados_hoje_ematendimento > 1 ? $_chamados_hoje_ematendimento." Solicitações" : $_chamados_hoje_ematendimento." Solicitação",
      "finalizados" => $_chamados_hoje_finalizados > 1 ? $_chamados_hoje_finalizados." Solicitações" : $_chamados_hoje_finalizados." Solicitação",
      "cancelados" => $_chamados_hoje_cancelados > 1 ? $_chamados_hoje_cancelados." Solicitações" : $_chamados_hoje_cancelados." Solicitação",
      "ultimaSemana" => $ultima_semana['qtd'] > 1 ? $ultima_semana['qtd']." Solicitações" : $ultima_semana['qtd']." Solicitação",
      "mesAtual" => $chamados[0]['tempo_a'] > 1 ? $chamados[0]['tempo_a']." Minutos" : $chamados[0]['tempo_a']." Minuto",
      "chamadosPorHora" => $_chamados_hora7DiasResultado,
      "chamadosPorDiaSemana" => $_chamados_diaSemanaResultado
    ];
  }
}