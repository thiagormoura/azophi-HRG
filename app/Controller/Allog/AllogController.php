<?php

namespace App\Controller\Allog;

use \App\Utils\View;
use \App\Controller\Layout\Layout as LayoutPage;
use Error;
use \App\Http\Request;
use \App\Model\Allog\AllogModel;
use DateTime;

class AllogController extends LayoutPage
{

	// Método responsável por retornar a página inicial
	public static function getHome($request)
	{
		$systems = AllogModel::getSystems();

		$options = "";
		foreach ($systems as $system) {
			$options .= View::render('utils/option', [
				"id" => $system['id'],
				"nome" => $system['descricao'],
				"selected" => $system['id'] == 12 ? "selected" : "",
				"disabled" => ""
			]);
		}

		$content = View::render('allog/Painel', [
			'title' => 'Allog',
			'sistemas' => $options
		]);

		return parent::getPage('Auditar Login', 'allog', $content, $request);
	}

	public static function ajaxReloadTable(Request $request)
	{

		$data = $request->getPostVars();

		if (isset($data['start']) && $data['length'] != -1) {
			$limit = intval($data['start']) . ", " . intval($data['length']);
		}

		$users = AllogModel::getAllog($data, $limit, $data['search']['value']);

		return [
			"draw" => $data['draw'],
			"data" => $users,
			"recordsTotal" => count($users),
			"recordsFiltered" => count(AllogModel::getAllog($data, null))
		];
	}

	public static function getGraphics(Request $request)
	{
		$result = [];

		$result['weekAccess'] = self::getWeekAccess($request);
		$result['totalAccess'] = self::getTotalAccess($request);
		$result['usersAccess'] = self::getUsersAccessBySystem($request);

		return $result;
	}

	private static function getWeekAccess(Request $request)
	{
		$sistemas7days = AllogModel::getSistemsInWeek();
		$idSystemColumn = array_unique(array_column($sistemas7days, "id"));
		sort($idSystemColumn);

		$dataPointTemplate = [];
		$dataPosition = [];
		$_7days = (new DateTime());
		$_7days->modify("-7 days");

		for ($i = $_7days; $i <= (new DateTime()); $i->modify("+1 day")) { 
			$dataPointTemplate[] = ["x" => $i->format("Y-m-d"), "y" => 0];
			$dataPosition[] = $i->format("Y-m-d");
		}

		$systemAcesso = array_fill(0, count($idSystemColumn), false);

		$result = [];
		foreach ($idSystemColumn as $systemId){
			$filterSystem = array_filter($sistemas7days, function($element) use ($systemId){
				if($element['id'] == $systemId) return $element;
			});

			$getSystemName = $filterSystem;

			$arrayDataLine = [];
			$arrayDataLine['name'] = array_pop(array_reverse($getSystemName))['descricao'];
			$arrayDataLine['type'] = "spline";
			$arrayDataLine['showInLegend'] = true;

			foreach (array_count_values(array_column($filterSystem, 'dt')) as $data => $acessos) {
				$keyPosition = array_search($data, $dataPosition);

				if(empty($arrayDataPoint)) $arrayDataPoint = $dataPointTemplate;

				if(!$systemAcesso[$keyPosition]) $systemAcesso[$keyPosition] = !$systemAcesso[$keyPosition];

				$arrayDataPoint[$keyPosition]['y'] = $acessos;
				$arrayDataLine['dataPoints'] = $arrayDataPoint;

			}
			$arrayDataPoint = [];

			$result[] = $arrayDataLine;

		}

		return $result;
	}

	private static function getTotalAccess(Request $request)
	{
		$totalAccess = AllogModel::getTotalAccess();
		return $totalAccess;
	}

	public static function getUsersAccessBySystem(Request $request)
	{
		$users = [];
		$systemId = $request->getPostVars()['system'];
		$users['data'] = AllogModel::getUsersAccessBySystem($systemId);
		$users['nome'] = AllogModel::getSystem($systemId)['descricao'];

		return $users;
	}
}