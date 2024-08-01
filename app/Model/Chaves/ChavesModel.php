<?php

namespace App\Model\Chaves;

use App\Db\Database;
use App\Db\Smart;
use DateTime;
use DateTimeZone;

class ChavesModel
{

    public static function updateAcess($user){

        date_default_timezone_set('America/Fortaleza');

        return (new Database("centralservicos", "usuario_acesso"))->insert([
            "usuario_nome" => $user->nome,
            "usuario_cpf" => $user->cpf,
            "sistema" => "Chaves (Produção)",
            "dthr_login" => date("Y-m-d H:i:s")
        ]);
    }

    public static function searchRegistration($register)
    {
        return (new Database("appcatraca", "FUNCIONARIOS func left join ARMARIOS arm on func.matricula = arm.id_func"))->select("func.nome, func.matricula, func.setor, arm.numero as armario", "func.matricula = '" . trim($register) . "'")->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public static function getLockers($order=null)
    {
        return (new Database("appcatraca", "ARMARIOS"))->select("id, id_func, numero, status_arm as status", null, null, null, "numero $order")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getLockersByFunId($fun_id){
        return (new Database("appcatraca", "ARMARIOS"))->select('*', "arm_idfun=$fun_id")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getFuncionarios()
    {
        return (new Database("appcatraca", "FUNCIONARIOS"))->select("id, nome, setor, status_fun as status, matricula")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getLockerInfos($idLocker)
    {
        return (new Database("appcatraca", "ARMARIOS arm left join FUNCIONARIOS on matricula = arm.id_func"))->select("arm.id, nome, setor, matricula, numero, status_arm as status", "arm.id = ".$idLocker)->fetch(\PDO::FETCH_ASSOC);
    }

    public static function getLastRent($idLocker, $idFuncionario){
        return (new Database("appcatraca", "ARMARIO_HAS_FUNCIONARIO"))->select("data_action as data", "id_arm = ".$idLocker." AND matricula_func = ".$idFuncionario, null, null, "data_action desc")->fetch(\PDO::FETCH_ASSOC);
    }

    public static function alugarLocker($matricula, $idLocker)
    {
        return (new Database("appcatraca", "ARMARIOS"))->update("id = ". $idLocker, [
            "id_func" => $matricula,
            "status_arm" => "O"
        ]);
    }

    public static function putRentHistoric($matricula, $idLocker, $type)
    {
        $datetime = new DateTime();
        $datetime->setTimezone(new DateTimeZone('America/Fortaleza'));

        return (new Database("appcatraca", "ARMARIO_HAS_FUNCIONARIO"))->insert([
            "matricula_func" => $matricula,
            "id_arm" => $idLocker,
            "data_action" => $datetime->format("Y-m-d H:i:s"),
            "type_action" => $type
        ]);
    }

    public static function alugarLockerCorrectly($matricula, $idLocker)
    {
        return (new Database("appcatraca", "ARMARIO_HAS_FUNCIONARIO"))->insert([
            "arm_arm_id" => (int) $matricula,
            "fun_fun_id" => (int) $idLocker,
            "arm_fun_data" => date("Y-m-d H:i:s"),
            "arm_fun_status" => "emprestimo"
        ]);
    }

    public static function devolverLocker($idLocker)
    {
        return (new Database("appcatraca", "ARMARIOS"))->update("id = ". $idLocker, [
            "id_func" => null,
            "status_arm" => "L"
        ]);
    }

    public static function getHistorico($limit = null, $order = null, $filters = null)
    {
        return (new Database("appcatraca", "ARMARIO_HAS_FUNCIONARIO INNER JOIN FUNCIONARIOS func ON func.id = matricula_func
            INNER JOIN ARMARIOS arm ON arm.id = id_arm"))->select("data_action as data, arm.numero as numero, type_action as status, matricula_func as matricula, func.nome as nome", $filters, $limit, null, $order)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getLastDate(){
        return (new Database("appcatraca", "ARMARIO_HAS_FUNCIONARIO INNER JOIN FUNCIONARIOS func ON func.id = matricula_func
            INNER JOIN ARMARIOS arm ON arm.id = id_arm"))->select("data_action as data", "type_action = 'O'", "1", null, "data_action DESC")->fetch(\PDO::FETCH_ASSOC);

    }

    public static function getSetores(){
        return (new Database("appcatraca", "FUNCIONARIOS"))->select('TRIM(RTRIM(setor)) as setor', null, null, 'TRIM(RTRIM(setor))', null)->fetchAll(\PDO::FETCH_ASSOC);   
    }

    public static function addFuncionario($matricula, $nome, $setor)
    {
        return (new Database("appcatraca", "FUNCIONARIOS"))->insert([
            "id" => $matricula,
            "nome" => $nome,
            "setor" => $setor,
            "matricula" => $matricula,
        ]);
    }

    /**
     * Método responsável por adquirir nome e matricula de todos os funcionarios do banco
     * @return array
    */
    public static function getFuncionariosData(){
        return (new Database("appcatraca", "FUNCIONARIOS"))->select("id, nome, matricula")->fetchAll(\PDO::FETCH_ASSOC);
    }
}