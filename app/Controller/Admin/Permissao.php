<?php

namespace App\Controller\Admin;

use App\Controller\Auth\Alert;
use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use \App\Model\CentralServicos\Permissao as PermissaoModel;
use \App\Model\CentralServicos\Sistema as SistemaModel;

class Permissao extends LayoutPage
{
	// Método responsável por retornar as linhas da tabela de permissões
	private static function getPermissoesTabela($isSuperAdmin)
	{
		$linhasTabela = '';
		$permissoes = PermissaoModel::getPermissoes();
		$number_linha = 0;
		foreach ($permissoes as $permissao) {
			if ($permissao->id_sistema === 1 && !$isSuperAdmin) continue;
			$number_linha++;
			$linhasTabela .= View::render('admin/permissao/tabela_linha', [
				'numero-linha' => $number_linha,
				'id' => $permissao->id,
				'codigo' => $permissao->codigo,
				'sistema' => $permissao->sistema,
				'descricao' => $permissao->descricao,
			]);
		}
		return $linhasTabela;
	}
	// Método responsável por retornar as opções dos sistemas para o select 
	private static function getSistemas($isSuperAdmin, $selected = null)
	{
		$sistemas = SistemaModel::getAllSistemas();
		$options = '';
		foreach ($sistemas as $sistema) {
			if ($sistema->id === 1 && !$isSuperAdmin) continue;
			$options .= View::render('utils/option', [
				'id' => $sistema->id,
				'nome' => $sistema->nome,
				'disabled' => '',
				'selected' => $selected === null ? '' : ($selected === $sistema->id ? 'selected' : ''),
			]);
		}
		return $options;
	}
	// Método responsável por retornar a página principal de permissões 
	public static function getPermissoes($request, $errorMessage = null, $successMessage = null)
	{
		$user = $request->user;
		$superAdmin = parent::checkPermissao($user, 'admin');
		$status = !is_null($errorMessage) ? Alert::getError($errorMessage) : (!is_null($successMessage) ? Alert::getSuccess($successMessage) : '');
		$content = View::render('admin/permissao', [
			'status' => $status,
			'permissoes' => self::getPermissoesTabela($superAdmin),
			'options-sistemas' => self::getSistemas($superAdmin),
		]);
		return parent::getPage('Painel administrativo', 'admin-permissao', $content,  $request);
	}
	// Método responsável por retornar a permissão para o modal de alteração da permissão
	public static function getEditPermissao($request, $id)
	{
		$permissao = PermissaoModel::getPermissaoById($id);
		if (!$permissao) return array(
			'success' => false, 'message' =>
			'Desculpe, mas essa permissão não existe ou não está ativa.'
		);
		return array('success' => true, 'permissao' => $permissao);
	}
	// Método responsável por atualizar as informações da permissão passa por parâmetro
	public static function setEditPermissao($request, $id)
	{
		$user = $request->user;
		$superAdmin = parent::checkPermissao($user, 'admin');
		$postVars = $request->getPostVars();
		// Verifica se o sistema e a descrição está vázia
		if (empty($postVars['sistema']) || empty($postVars['descricao']))  return array(
			'success' => false,
			'message' => 'Preencha todos os campos obrigátorios'
		);
		// Verifica se o código não está vázio
		if (!empty($postVars['codigo']))  return array(
			'success' => false,
			'message' => 'Desculpe, você não pode alterar o código após criar uma permissão.'
		);
		// Verifica se determinada permissão existe 
		$permissao = PermissaoModel::getPermissaoById($id);
		if (!$permissao instanceof PermissaoModel) return array(
			'success' => false,
			'message' => 'Desculpe, esta permissão não existe.'
		);
		// Verifica se a permissão é uma permissão do sistema de administrador
		// Caso seja verifica se o usuário possui permissão para altera-la
		if ($permissao->id_sistema === 1 && !$superAdmin) return array(
			'success' => false,
			'message' => 'Desculpe, você não tem permissão para alterar esta permissão.'
		);
		$permissao->id_sistema = $postVars['sistema'];
		$permissao->descricao = $postVars['descricao'];
		PermissaoModel::updatePermissao($permissao);
		return array(
			'success' => true,
			'message' => 'Permissão alterada com sucesso.'
		);
	}
	// Método responsável por cadastrar uma nova permissão realizando algumas validações
	public static function setNewPermissao($request)
	{
		$postVars = $request->getPostVars();
		if (empty($postVars['codigo']) || empty($postVars['sistema']) || empty($postVars['descricao']))  return array(
			'success' => false,
			'message' => 'Preencha todos os campos obrigátorios'
		);
		$permissao = PermissaoModel::getPermissaoByCodigo($postVars['codigo']);
		if ($permissao instanceof PermissaoModel) return array(
			'success' => false,
			'message' => 'Código já cadastrado! Por favor, insira uma permissão com código diferente.'
		);
		$permissao['codigo'] = $postVars['codigo'];
		$permissao['id_sistema'] = $postVars['sistema'];
		$permissao['descricao'] = $postVars['descricao'];
		PermissaoModel::insertPermissao($permissao);
		return array(
			'success' => true,
			'message' => 'Permissão cadastrada com sucesso.'
		);
	}
}
