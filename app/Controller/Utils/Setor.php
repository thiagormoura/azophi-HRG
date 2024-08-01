<?php

namespace App\Controller\Utils;

use \App\Utils\View;
use \App\Model\Utils\Setor as SetorModel;

class Setor
{
  public static function getSetorOptions()
  {
    $options = '';
    $setores = SetorModel::getStores();
    $options .= View::render('utils/option', [
      'id' => '',
      'nome' => 'Selecione uma opção',
      'selected' => 'selected',
      'disabled' => 'disabled',
    ]);
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

  public static function getSetorOptionsSelected($selected)
  {
    $options = '';
    $setores = SetorModel::getStores();
    foreach ($setores as $setor) {
      $options .= View::render('utils/option', [
        'id' => $setor->codigo,
        'nome' => $setor->nome,
        'selected' => $setor->codigo === $selected ? 'selected' : '',
        'disabled' => '',
      ]);
    }
    return $options;
  }

  public static function getSetorByCode($code){
    return (SetorModel::getSetorByCode($code))->nome;
  }
}
