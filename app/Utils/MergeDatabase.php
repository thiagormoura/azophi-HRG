<?php

namespace App\Utils;

use App\Model\CentralServicos\Paciente;
use App\Model\CentralServicos\Setor;
use App\Model\CentralServicos\Local;

class MergeDatabase
{

  // Método responsável por sincronizar os pacientes do banco secundário com o banco primário
  private static function syncPaciente($pacientes)
  {
    foreach ($pacientes as $paciente) {
      $isNew = false;
      $obPaciente = Paciente::getPacienteByRegistro($paciente['registro']);
      if ($obPaciente instanceof Paciente) {
        $obPaciente = new Paciente;
        $isNew = true;
      }

      $obPaciente->registro = $paciente['registro'];
      $obPaciente->nome = $paciente['nome'];
      $obPaciente->cpf = $paciente['cpf'];
      $obPaciente->sexo = $paciente['sexo'];
      $obPaciente->dthr_nascimento = $paciente['dthr_nascimento'];

      if ($isNew === true) {
        $obPaciente->cadastrar();
        continue;
      }
      $obPaciente->atualizar();
    }
  }

  // Método responsável por sincronizar os pacientes do banco secundário com o banco primário
  private static function syncSetor($setores)
  {
    foreach ($setores as $setor) {
      $isNew = false;
      $obSetor = Setor::getSetorByCodigo($setor['codigo']);
      if ($obSetor instanceof Setor) {
        $obSetor = new Setor;
        $isNew = true;
      }

      $obSetor->codigo = $setor['codigo'];
      $obSetor->nome = $setor['nome'];
      $obSetor->status = $setor['status'];

      if ($isNew === true) {
        $obSetor->cadastrar();
        continue;
      }
      $obSetor->atualizar();
    }
  }

  // Método responsável por sincronizar os pacientes do banco secundário com o banco primário
  private static function syncLocal($locais)
  {
    foreach ($locais as $local) {
      $isNew = false;
      $obLocal = Local::getLocalByCodigo($local['codigo']);
      if ($obLocal instanceof Local) {
        $obLocal = new Local;
        $isNew = true;
      }

      $obLocal->codigo = $local['codigo'];
      $obLocal->nome = $local['nome'];
      $obLocal->status = $local['status'];

      if ($isNew === true) {
        $obLocal->cadastrar();
        continue;
      }
      $obLocal->atualizar();
    }
  }

  // Método responsável por sincronizar os dados passados por paramentros
  public static function sync($pacientes = NULL, $setores = NULL, $locais = NULL)
  {
    if ($pacientes !== NULL) {
      self::syncPaciente($pacientes);
    }
    if ($setores !== NULL) {
      self::syncSetor($setores);
    }
    if ($locais !== NULL) {
      self::syncLocal($locais);
    }
  }
}
