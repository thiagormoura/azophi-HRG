<?php

namespace App\Controller\Inutri;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Model\Inutri\ItemCardapio as ItemCardapioModel;

class ItemCardapio extends LayoutPage
{

  // Método responsável por inserir um novo item
  public static function insertItemCardapio($request)
  {
    $postVars = $request->getPostVars();
    ItemCardapioModel::insertComida($postVars['nome'], $postVars['categoria']);
  }

  // Método responsável por atualizar os status por id
  public static function updateStatus($request, $id)
  {
    $item = ItemCardapioModel::getItemById($id);
    if (!$item) return array(
      'success' => false,
      'message' => 'Desculpe, mas o alimento que está tentando atualizar não existe.',
    );
    $status = 1;
    if ($item->status) $status = 0;
    ItemCardapioModel::updateStatus($id, $status);
    $statusAtual = $status === 0 ? 'Inátivo' : 'Ativo';
    return array(
      'success' => true,
      'message' => 'O alimento ' . $item->nome . ' está ' . $statusAtual,
    );
  }

  // Método responsável por atualizar os itens do cardápio
  public static function updateItemCardapio($request)
  {
    $postVars = $request->getPostVars();
    $idComidas = $postVars['comida-id'];
    $nomesComidas = $postVars['comida-nome'];
    $categoriaComidas = $postVars['comida-categoria'];

    for ($i = 0; $i < count($idComidas); $i++) {
      if (empty($idComidas[$i]) || empty($nomesComidas[$i]) || empty($categoriaComidas[$i])) {
        return array(
          'success' => false,
          'message' => 'É necessário preencher todos os campos obrigatórios.'
        );
      }
    }
    for ($i = 0; $i < count($idComidas); $i++) {
      ItemCardapioModel::updateItemCardapio($idComidas[$i], $nomesComidas[$i], $categoriaComidas[$i]);
    }
    
    return array(
      'success' => true,
      'message' => 'Alimentos atualizados com sucesso!'
    );
  }

  // Método responsável por retonar as linhas da tabela 
  private static function getCategorias($categorias, $checkCategoria = null)
  {
    $categoriasPage = '';

    foreach ($categorias as $categoria) {
      $categoriasPage .= View::render('inutri/itenscardapio/categorias', [
        'categoria-id' => $categoria->id,
        'categoria-descricao' => $categoria->descricao,
        'selected' => $categoria->descricao == $checkCategoria ? 'selected' : ''
      ]);
    }
    return $categoriasPage;
  }

  // Método responsável por retonar as linhas da tabela 
  private static function getItens()
  {
    $itensCardapio = ItemCardapioModel::getItensPedido();
    $categorias = ItemCardapioModel::getCategorias();
    $pageItensCardapio = '';

    foreach ($itensCardapio as $item) {
      $pageItensCardapio .= View::render('inutri/itenscardapio/itens', [
        'id' => $item->id,
        'nome' => $item->nome,
        'status' => $item->status,
        'checked' => $item->status ? 'checked' : '',
        'categorias' => self::getCategorias($categorias, $item->categoria_descricao),
      ]);
    }

    return $pageItensCardapio;
  }

  // Método responsável por atualizar os itens do cardápio
  public static function getCreateItemCardapio($request)
  {
    $categorias = ItemCardapioModel::getCategorias();

    return View::render('inutri/itenscardapio/modals/itemcardapio', [
      'categorias' => self::getCategorias($categorias)
    ]);
  }

  // Método responsável por retornar a página principal dos alimentos
  public static function getItemCardapio($request)
  {
    $content = View::render('inutri/central_itemcardapio', [
      'itens' => self::getItens(),
    ]);

    return parent::getPage('iNutri - Itens Cardápio', 'inutri', $content, $request);
  }
}
