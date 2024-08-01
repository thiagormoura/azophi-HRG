<?php

namespace App\Model\CentralServicos;

use App\Db\Database;

class Paciente
{
  public $registro;
  public $nome;
  public $cpf;
  public $sexo;
  public $dthr_nascimento;
  public $dthr_atualizacao;

  // Método responsável por cadastrar um novo paciente
  public function cadastrar()
  {
    $this->registro = (new Database('centralservicos', 'paciente'))->insert([
      'registro' => $this->registro,
      'nome' => $this->nome,
      'cpf' => $this->cpf,
      'sexo' => $this->sexo,
      'dthr_nascimento' => $this->dthr_nascimento,
    ]);
  }

  // Método responsável por atualizar o cadastro de um paciente já existente
  public function atualizar()
  {
    return (new Database('centralservicos', 'paciente'))->update('registro = ' . $this->registro,  [
      'registro' => $this->registro,
      'nome' => $this->nome,
      'cpf' => $this->cpf,
      'sexo' => $this->sexo,
      'dthr_nascimento' => $this->dthr_nascimento,
    ]);
  }

  // Método responsável por retornar um paciente pelo registro
  public static function getPacienteByRegistro($registro){
    return (new Database('centralservicos', 'paciente'))->select('*', "registro = " . $registro)->fetchObject(self::class);
  }
}
