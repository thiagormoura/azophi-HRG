<?php

namespace App\Controller\Admin;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use App\Http\Request;
use App\Model\MonLogin\User;
use DateTime;
use DateTimeZone;

class MonLogin extends LayoutPage
{
    public static function getHome(Request $request): string
    {

        $content = View::render('admin/monlogin/home', []);

        return parent::getPage('MonLogin', 'monlogin', $content, $request);
    }

    public static function getUsers(array $users): string
    {
        $content = '';
        $fortalezaTimeZone = new DateTimeZone(CURRENT_TIMEZONE);

        foreach ($users as $user) {
            $dataAcessoUsuario = new DateTime($user->data_hora_login, $fortalezaTimeZone);
            $content .= View::render('admin/monlogin/linha_tabela', [
                'data-hora' => $dataAcessoUsuario->format('d/m/Y H:i'),
                'modulo' => $user->modulo,
                'login' => $user->login,
                'equipamento' => $user->equipamento,
                'nome' => $user->nome,
                'nome-completo' => $user->nome_completo,
                'sessao-color' => $user->sessao === 'Ativa' ? 'text-success' : 'text-danger',
                'sessao' => $user->sessao,
            ]);
        }
        return View::render('admin/monlogin/tabela', [
            'linhas-tabela' => $content
        ]);
    }

    public static function getUserLogin(Request $request): string
    {
        $postVars = $request->getPostVars();
        if (!empty($postVars['userLogin'])) {
            $usersLogin = User::getUsersLogin($postVars['userLogin']);
            return self::getUsers($usersLogin);
        }
    }
}
