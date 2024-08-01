<?php

namespace App\Controller\Admin;

use App\Communication\Email;
use App\Controller\Auth\Alert;
use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use \App\Model\CentralServicos\Permissao as PermissaoModel;
use \App\Model\CentralServicos\Sistema as SistemaModel;
use \App\Model\Entity\User as UsuarioModel;
use \App\Utils\ValidCPF;
use App\Model\CentralServicos\Perfil as PerfilModel;
use App\Model\CentralServicos\Token;
use DateTime;
use DateTimeZone;

class Usuario extends LayoutPage
{
  // Método responsável por retornar as linhas da tabela de usuários
  private static function getUsuariosTabela($user)
  {
    $linhasTabela = '';
    $usuarios = UsuarioModel::getUsers();
    $amIAdmin = parent::checkPermissao($user, 'admin');
    $number_linha = 0;
    foreach ($usuarios as $key => $usuario) {
      $usuario->permissoes = PermissaoModel::getPermissaoCodigoByUsuario($usuario->id);
      $isAdmin = parent::checkPermissao($usuario, 'admin');
      // Verifica se é um usuário administrador e se o usuário logado possui permissão
      // para visualiza-lo e verifica se o usuário listado é logado, caso verdadeiro, não exibe essa linha
      
      $number_linha++;

      $fortalezaTimeZone = new DateTimeZone(CURRENT_TIMEZONE);
      $dataCriacao = new DateTime($usuario->data_criacao, $fortalezaTimeZone);

      $linhasTabela .= View::render('admin/usuario/tabela_linha', [
        'numero-linha' => $number_linha,
        'id' => $usuario->id,
        'cpf' => $usuario->cpf,
        'nome' => $usuario->nome . ' ' .  $usuario->sobrenome,
        'email' => $usuario->email,
        'administrador' => $isAdmin ? '<span class="badge bg-danger ms-1">Administrador</span>' : '',
        'data-criado' => $dataCriacao->format('d/m/Y H:i'),
        'icon' => $usuario->status == 'A' ? 'times' : 'check',
        'icon-color' => $usuario->status == 'A' ? 'text-danger' : 'text-success',
        'status-color' => $usuario->status == 'A' ? 'text-success' : 'text-danger',
        'status' => $usuario->status == 'A' ? 'Ativo' : 'Inativo',
        'actions' => $amIAdmin ? View::render('admin/usuario/tabela_linha_admin', [
          "id" => $usuario->id,
          'icon' => $usuario->status == 'A' ? 'times' : 'check',
          'icon-color' => $usuario->status == 'A' ? 'text-danger' : 'text-success'
        ]) : View::render('admin/usuario/tabela_linha_view', ["id" => $usuario->id])
      ]);
    }
    return $linhasTabela;
  }
  // Método responsável por retornar as opções de perfis para o select de perfis
  private static function getPerfis($superAdmin)
  {
    $perfis = PerfilModel::getActivedPerfis();
    $options = '';
    foreach ($perfis as $perfil) {
      if ($perfil->codigo === 'admin' && !$superAdmin) continue;
      $options .= View::render('utils/option', [
        'id' => $perfil->id,
        'nome' => $perfil->nome,
        'disabled' => '',
        'selected' => '',
      ]);
    }
    return $options;
  }
  // Método responsável por retornar a página principal dos usuários
  public static function getUsuarios($request, $errorMessage = null, $successMessage = null)
  {
    $user = $request->user;
    $superAdmin = parent::checkPermissao($user, 'admin');
    $status = !is_null($errorMessage) ? Alert::getError($errorMessage) : (!is_null($successMessage) ? Alert::getSuccess($successMessage) : '');
    $content = View::render('admin/usuario', [
      'status' => $status,
      'usuarios' => self::getUsuariosTabela($user),
      'options-perfil' => self::getPerfis($superAdmin),
    ]);
    return parent::getPage('Painel administrativo', 'admin-usuario', $content, $request);
  }
  // Método responsável por retornar os sistemas e suas respectivas permissões, as quais determinado usuário possui
  private static function getPermissaoUsuario($usuario, $isSuperAdmin)
  {
   
    $sistemas = SistemaModel::getAllSistemas();
    $permissoesUsuario = '';
    foreach ($sistemas as $sistema) {
      if ($sistema->id === 1 && !$isSuperAdmin) continue;
      $permissaoSistema = PermissaoModel::getPermissaoByUsuarioAndSistema($usuario->id, $sistema->id);
      $permissaoCheck = '';

      $vinculado = false;
      foreach ($permissaoSistema as $permissao) {
        if (!$vinculado && $permissao->vinculado) {
          $vinculado = true;
        }
        $permissaoCheck .= View::render('admin/permissao/permission_check', [
          'id' => $permissao->id,
          'descricao' => $permissao->descricao,
          'checked' => $permissao->vinculado ? 'checked' : '',
        ]);
      }
      $permissoesUsuario .= View::render('admin/permissao/accordion_sistema', [
        'text-vinculado' => !$vinculado ? 'text-dark' : '',
        'id' => $sistema->id,
        'sistema' => $sistema->nome,
        'descricao' => $sistema->descricao,
        'permissoes-check' => $permissaoCheck,
      ]);
    }
    return $permissoesUsuario;
  }
  // Método responsável por retornar a página de edição do usuário
  public static function getEditUser($request, $id, $errorMessage = null, $successMessage = null)
  {
    $userRequest = $request->user;

    $status = !is_null($errorMessage) ? Alert::getError($errorMessage) : (!is_null($successMessage) ? Alert::getSuccess($successMessage) : '');
    $usuario = UsuarioModel::getUserById($id);
    if($usuario->nome.' '.$usuario->sobrenome == "Setor Teste"){
      $brincadeira = getenv('URL')."/resources/img/sim.png";
    } else {
      $brincadeira = getenv('URL')."/resources/img/man-icon-png.png";
    }
    $isSetor = $usuario->cpf == '---' ? 1 : 0;
    $usuario->permissoes = PermissaoModel::getPermissaoCodigoByUsuario($id);

    $isAdmin = parent::checkPermissao($usuario, 'admin');
    $amIAdmin = parent::checkPermissao($userRequest, 'admin');

    // Caso o usuário não possua permissão para editar determinado usuário ele vai ser redirecionad
    // para página inicial dos usuários
    if (!$amIAdmin) return self::getUsuarios($request, 'Desculpe, mas você não possui permissão para editar este usuário');

    $fortalezaTimeZone = new DateTimeZone(CURRENT_TIMEZONE);
    $dataCriacao = new DateTime($usuario->data_criacao, $fortalezaTimeZone);
    $quantidade_token = Token::verifyTokenByEmail($usuario->email)['quantidade'];

    $content = View::render('admin/usuario/editar_usuario', [
      'status' => $status,
      'id' => $usuario->id,
      'nome' => $usuario->nome ,
      'sobrenome' => $usuario->sobrenome,
      'email' => $usuario->email,
      'cpf' => $isSetor ? "---" : $usuario->cpf,
      'matricula' => $usuario->matricula,
      'data-criacao' => $dataCriacao->format('d/m/Y H:i'),
      'options-perfil' => self::getPerfis($amIAdmin),
      'permissoes' => self::getPermissaoUsuario($usuario, $amIAdmin),
      "reset-senha" => $quantidade_token > 0 ? "" : View::render('utils/button', [
        "class" => "btn btn-orange w-100 admin-reset-usuario-pass",
        "text" => "<i class='fas fa-undo'></i> Resetar Senha"
      ]),
      "isset-reset-senha" => $quantidade_token > 0 ? "12" : "6",
      "status-user" => $usuario->status,
      "status-icon" => $usuario->status == "A" ? "-slash" : "",
      "status-name-button" => $usuario->status == "A" ? " Inativar usuário" : " Ativar usuário",
      "status-color" => $usuario->status == "A" ? "danger" : "info",
      "brincadeira" => $brincadeira,
    ]);
    return parent::getPage('Painel administrativo - Usuário', 'admin-usuario', $content,  $request);
  }
  // Método responsável por alterar as informações de determinado usuário com validações
  public static function setEditUser($request, $id)
  {
    $postVars = $request->getPostVars();
    $usuario = UsuarioModel::getUserById($id);
    $isSetor = isset($postVars['isSetor']);
    
    // Verifica se o email inserido diverge do atual email do usuário e
    // se possui algum usuário com aquele email já cadastrado.
    $user = UsuarioModel::getUserByEmail($postVars['email']);
    if ($user instanceof UsuarioModel && $user->email !== $usuario->email) return array(
      'success' => false,
      'message' => 'Email já cadastrado! Por favor, tente novamente.'
    );

    // Verifica se o cpf inserido é valido
    $cpf = str_replace('-', '', str_replace('.', '', $postVars['cpf']));
    if (!ValidCPF::validCPF($cpf) && !$isSetor) return array(
      'success' => false,
      'message' => 'Por favor, insira um CPF válido.'
    );

    if(!$isSetor){
      // Verifica se o cpf inserido diverge do atual cpf do usuário e
      // se possui algum usuário com aquele cpf já cadastrado.
      $user = UsuarioModel::getUserByCpf($cpf);
      if ($user instanceof UsuarioModel && $user->cpf !== $usuario->cpf) return array(
        'success' => false,
        'message' => 'CPF já cadastrado! Por favor, tente novamente.'
      );
    }

    // Verifica se o cpf inserido diverge do atual cpf do usuário e
    // se possui algum usuário com aquele cpf já cadastrado.
    $user = UsuarioModel::getUserByMatricula($postVars['matricula']);
    if (!empty($postVars['matricula']) && $user instanceof UsuarioModel && $user->matricula !== $usuario->matricula) return array(
      'success' => false,
      'message' => 'Matricula já cadastrada! Por favor, tente novamente.'
    );
    // Válida se há algum valor vázio, caso sim, não altera
    foreach ($postVars as $propriedade => $valor) {
      $usuario->$propriedade = empty($valor) ? $usuario->$propriedade : $valor;
    }
    $usuario->cpf = $isSetor ? "---" : $cpf;
    $usuario->atualizar();
    return array(
      'success' => true,
      'message' => 'Alterações realizadas com sucesso!',
    );
  }
  // Método responsável por alterar as permissões de determinado usuário
  public static function setEditUserPermission($request, $id)
  {
    $user = $request->user;
    $isSuperAdmin = parent::checkPermissao($user, 'admin');
    $postVars = $request->getPostVars();
    // Remove todas as permissões do usuário
    PermissaoModel::deletePermissaoByUsuario($id);
    $permissoes = $postVars['permissao'];
    // Insere as novas permissões
    foreach ($permissoes as $permissao) {
      $permissaoUsuario = PermissaoModel::getPermissaoById($permissao);
      if (!$isSuperAdmin && $permissaoUsuario->codigo === 'admin') continue;
      if ($permissaoUsuario instanceof PermissaoModel) PermissaoModel::insertPermissaoUsuario($permissaoUsuario->id, $id);
    }

    return array(
      'success' => true,
      'message' => 'Alterações realizadas com sucesso!',
    );
  }
  // Método responsável por inserir um novo usuário no sistema com alguams validações
  public static function setNewUser($request)
  {
    $postVars = $request->getPostVars();
    $user = $request->user;

    $isSuperAdmin = parent::checkPermissao($user, 'admin');
    // Verifica se todos os campos obrigátorios estão preenchidos
    foreach ($postVars as $key => $value) {
      // Matricula é um campo opcional
      if ($key === 'matricula') continue;
      if (empty($value)) return array(
        'success' => false,
        'message' => 'Preencha todos os campos obrigátorios'
      );
    }
    // Verifica se já possui um usuário com o email especificado
    $user = UsuarioModel::getUserByEmail($postVars['email']);
    if ($user instanceof UsuarioModel) return array(
      'success' => false,
      'message' => 'Email já cadastrado! Por favor, tente novamente.'
    );
    // Verifica se o cpf inserido é válido
    $cpf = str_replace('-', '', str_replace('.', '', $postVars['cpf']));
    $accountType = $postVars['AccountTypeRadio'];
    if($accountType == 'NormalAccount'){
      if (!ValidCPF::validCPF($cpf)) return array(
        'success' => false,
        'message' => 'Por favor, insira um CPF válido.'
      );
      
      // Verifica se possui um usuário com o cpf especificado
      $user = UsuarioModel::getUserByCpf($cpf);
      if ($user instanceof UsuarioModel) return array(
        'success' => false,
        'message' => 'CPF já cadastrado! Por favor, tente novamente.'
      );
    } else {
      $cpf = '---';
    }
    

    // Verifica se possui um usuário com a matricula inserida
    $user = UsuarioModel::getUserByMatricula($postVars['matricula']);
    if (!empty($postVars['matricula']) && $user instanceof UsuarioModel) return array(
      'success' => false,
      'message' => 'Matricula já cadastrada! Por favor, tente novamente.'
    );

    $user = new UsuarioModel;
    foreach ($postVars as $key => $value) {
      if ($key === 'matricula' and empty($value)) continue;
      $user->$key = $value;
    }

    // Muda o cpf do usuário para o padrão sem formatação
    $user->cpf = $cpf;
    // Gera a senha provisória do usuário com 8 digitos
    $codigoLogin = substr(strtoupper(md5(uniqid(rand(0, 10), true))), 0, 8);
    $user->senha = password_hash($codigoLogin, PASSWORD_DEFAULT);
    $user->cadastrar();

    // Verifica se foi definido um perfil para determinado usuario
    if (!empty($postVars['perfil'])) {

      foreach ($postVars['perfil'] as $perfilId) {
        $perfil = PerfilModel::getPerfilById($perfilId);
        if (!$perfil) return array(
          'success' => false,
          'message' => 'Por favor, insira um perfil válido.'
        );

        if ($perfil->codigo === 'admin' && !$isSuperAdmin) return array(
          'success' => false,
          'message' => 'Desculpe, você não possui permissão para inserir um usuário com este perfil.'
        );
        $permissoes = PermissaoModel::getPermissaoByPerfil($perfil->id);
        foreach ($permissoes as $permissao) {
          if (!$isSuperAdmin && $permissao->codigo === 'admin') continue;
          PermissaoModel::insertPermissaoUsuario($permissao->id, $user->id);
        }
      }
    }

    // Instância o objeto de email
    $email = new Email;
    // Define a hora para hora local no horário de Recife
    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    date_default_timezone_set('America/Recife');

    // Envia o email para o usuário com sua senha provisória
    if($email->sendEmail($postVars['email'], 'Central de Serviços - Bem vindo', View::render('mail/mail_body', [
      'content' => View::render('mail/mail_bemvindo', [
        'date' => date('d/m/Y H:i:s'),
        'usuario' => $postVars['nome'],
        'senha' => $codigoLogin,
      ]),
    ])))
      return array('success' => true, 'message' => 'Usuário cadastrado com sucesso!');

    return array('success' => false, 'message' => $email->getError());
  }
  // Método responsável por atualizar o status do usuário
  public static function updateStatus($request, $id)
  {
    $usuario = UsuarioModel::getUserById($id);
    
    if ($usuario->status === 'A') 
      $usuario->status = 'D';
    else 
      $usuario->status = 'A';
    
      $usuario->atualizar();
    $statusUsuario = $usuario->status == 'A' ? 'Ativo' : 'Inativo';
    
    return array(
      'success' => true,
      'message' => 'O usuário ' . $usuario->nome . ' está ' . $statusUsuario . '.',
      'icon' => $usuario->status == 'A' ? 'fas fa-times' : 'fas fa-check',
      'status' => $statusUsuario,
    );
  }
  // Método responsável por retornar as permissões que determinado perfil possui para
  // preenchimento das permissões na página de edição de usuário
  public static function getPermissaoPerfil($request)
  {
    $postVars = $request->getPostVars();
    if (empty($postVars['perfil'])) return false;
    $perfis = explode(',', $postVars['perfil']);
    $permissoes = array();

    foreach ($perfis as $perfil) {
      $perfilModel = PerfilModel::getPerfilById($perfil);
      if (!$perfilModel) continue;
      $permissoes = array_merge($permissoes, PermissaoModel::getPermissaoIdByPerfil($perfil));
    }

    return $permissoes;
  }
}
