<?php

namespace App\Model\Inutri;

use App\Db\Database;

class Configuration
{

    // Método responsável por retornar o host da impressora
    public function getHostPrinter()
    {
        return (new Database('inutricao', 'configuracao'))->select('valor', "codigo = 'host_impressora'")->fetchColumn();
    }

    // Método responsável por retornar a impressora compartilhada definida no banco de dados
    public function getSharedPrinter()
    {
        return (new Database('inutricao', 'configuracao'))->select('valor', "codigo = 'nome_impressora'")->fetchColumn();
    }

    // Método responsável por definir o host da impressora
    public function setHostPrinter(string $host)
    {
        return (new Database('inutricao', 'configuracao'))->update("codigo = 'host_impressora'",  [
            'valor' => $host
        ]);
    }

    // Método responsável por definir a impressora compartilhada
    public function setSharedPrinter(string $printterName)
    {
        return (new Database('inutricao', 'configuracao'))->update("codigo = 'nome_impressora'",  [
            'valor' => $printterName
        ]);
    }
}
