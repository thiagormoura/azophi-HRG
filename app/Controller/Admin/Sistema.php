<?php

namespace App\Controller\Admin;

use App\Controller\Auth\Alert;
use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Model\CentralServicos\Sistema as CentralServicosModel;

class Sistema extends LayoutPage
{
    public static function getHome($request)
    {
        $errorMessage = null;
        $successMessage = null;

        $sistemas = CentralServicosModel::getAllSistemas();

        $sistemasString = "";
        foreach ($sistemas as $sistema) {
            $sistemasString .= View::render('admin/sistema/tableRowSystem', [
                'id' => $sistema->id,
                'nome' => $sistema->nome,
                'descricao' => $sistema->descricao,
                'status' => $sistema->status == "A" ? 'Ativado' : 'Desativado',
                'acoes' => "<a onclick='return false' class='text-info ms-2 admin-edit-system cursor-pointer'><i class='fas fa-cog'
                data-bs-toggle='tooltip' data-bs-placement='top' title='Editar Sistema'></i></a>"
            ]);
        }

        $content = View::render('admin/sistemas', [
            'status' => !is_null($errorMessage) ? Alert::getError($errorMessage) : (!is_null($successMessage) ? Alert::getSuccess($successMessage) : ''),
            'sistemas' => $sistemasString
        ]);

        return parent::getPage('Painel administrativo', 'admin', $content, $request);
    }

    public static function getEditSistema($request, $id)
    {
        $sistema = CentralServicosModel::getSistemaById($id);
        if (!$sistema) return array(
            'success' => false, 'message' =>
            'Desculpe, mas esse Sistema não existe ou não está ativa.'
        );
        return array('success' => true, 'sistema' => $sistema);
    }

    public static function editSistema($request, $id)
    {
        $post = $request->getPostVars();

        // Verifica se o sistema e a descrição está vázia
        if (empty($post['nome-edit']) || empty($post['descricao-edit']))  return array(
            'success' => false,
            'message' => 'Preencha todos os campos obrigátorios'
        );

        // Verifica se determinada permissão existe 
        $sistema = CentralServicosModel::getSistemaById($id);
        if (empty($sistema)) return array(
            'success' => false,
            'message' => 'Desculpe, este Sistema não existe.'
        );
        // Verifica se a permissão é uma permissão do sistema de administrador
        // Caso seja verifica se o usuário possui permissão para altera-la
        $sistema['id_sistema'] = $id;
        $sistema['nome'] = $post['nome-edit'];
        $sistema['descricao'] = $post['descricao-edit'];
        CentralServicosModel::updateSistema($sistema);
        return array(
            'success' => true,
            'message' => 'Sistema alterado com sucesso.'
        );
    }

    public static function createSystem($request)
    {
        $post = $request->getPostVars();

        if (empty($post['nome']) || empty($post['descricao'])) return array(
            'success' => false,
            'message' => 'Preencha todos os campos obrigátorios'
        );
        $sistema = CentralServicosModel::getSystemByNome($post['nome']);
        if ($sistema instanceof CentralServicosModel) return array(
            'success' => false,
            'message' => 'Sistema já cadastrado!'
        );
        $sistema['nome'] = $post['nome'];
        $sistema['descricao'] = $post['descricao'];
        CentralServicosModel::insertSistema($sistema);
        return array(
            'success' => true,
            'message' => 'Sistema cadastrado com sucesso.'
        );
    }
}
