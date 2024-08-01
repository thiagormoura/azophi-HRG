<?php
require __DIR__ . '/includes/app.php';

// teste
use \App\Http\Router;
$router = new Router(URL);

include __DIR__ . '/routes/pages.php';
include __DIR__ . '/routes/auth.php';
include __DIR__ . '/routes/sisnot/home.php';
include __DIR__ . '/routes/admin.php';

// Rotas dos paineis
include __DIR__ . '/routes/espera.php';
include __DIR__ . '/routes/escalamedica.php';
include __DIR__ . '/routes/painelagm.php';
include __DIR__ . '/routes/papem.php';
include __DIR__ . '/routes/allog.php';


// Rotas dos funcionários
include __DIR__ . '/routes/inutri.php';
include __DIR__ . '/routes/monexm.php';
include __DIR__ . '/routes/azophi.php';
include __DIR__ . '/routes/azophicc.php';
include __DIR__ . '/routes/monps.php';
include __DIR__ . '/routes/pafilas.php';
include __DIR__ . '/routes/sosmaqueiro.php';
include __DIR__ . '/routes/peputi.php';
include __DIR__ . '/routes/chaves.php';
include __DIR__ . '/routes/check_os.php';
include __DIR__ . '/routes/check_exame.php';
include __DIR__ . '/routes/gestaoleitos.php';
include __DIR__ . '/routes/avasis.php';
include __DIR__ . '/routes/ouvimed.php';

// Rotas dos pacientes
include __DIR__ . '/routes/paciente/agenda.php';
include __DIR__ . '/routes/paciente/ouvidoria.php';
include __DIR__ . '/routes/paciente/monps.php';
include __DIR__ . '/routes/paciente/avasisPaciente.php';

$router->run()
       ->sendResponse();
