<?php

namespace App\Http;

use App\Http\Middleware\Queue as MiddlewareQueue;
use \Closure;
use \Exception;
use \ReflectionFunction;

class Router
{
  private $url = '';
  private $prefix = '';
  private $routes = [];
  private $request;
  private $contentType = 'text/html';

  public function __construct($url)
  {
    $this->request = new Request($this);
    $this->url = $url;
    $this->setPrefix();
  }

  public function setContentType($contentType)
  {
    $this->contentType = $contentType;
  }

  private function setPrefix()
  {
    $parseUrl = parse_url($this->url);
    $this->prefix = $parseUrl['path'] ?? '';
  }

  private function addRoute($method, $route, $params = [])
  {
    // Define o que é controlador
    foreach ($params as $key => $value) {
      if ($value instanceof Closure) {
        $params['controller'] = $value;
        unset($params[$key]);
        continue;
      }
    }

    // Verifica se existe middle se sim, percorre os middlewares existentes
    if (!empty($params['middlewares'])) {
      foreach ($params['middlewares'] as $middleware => $value) {
        // Verifica se os middlewares tem argumentos
        $middlewareName = substr($value, 0, strpos($value, '::'));

        // Caso o middleware tenha argumento separe o nome do middleware dos argumentos
        $params['middlewares'][$middleware] = $middlewareName ? $middlewareName : $value;

        // Verifica se o middleware tem argumentos novamente
        if (strpos($value, '::')) {
          // remove o :: do middleware, retorna os argumentos dividios por ,
          $args = str_replace('::', '', substr($value, strpos($value, '::'), strlen($value) - 1));

          // Separa os argumentos em arrays e retona-o ao array de argumentos do middlewares
          $params['middlewareArgs'][$middlewareName] = explode(',', $args);
        }
      }
    } else $params['middlewares'] = []; //Caso não exista middleware define como um array vazio.

    // Verifica se o middleware tem argumentos
    $params['middlewareArgs'] = $params['middlewareArgs'] ?? [];

    $params['variables'] = [];
    $patternVariable = '/{(.*?)}/';

    // Define as variáveis das rotas dinâmicas
    if (preg_match_all($patternVariable, $route, $matches)) {
      $route = preg_replace($patternVariable, '(.*?)', $route);
      $params['variables'] = $matches[1];
    }

    $route = rtrim($route, '/');
    $patternRoute = '/^' . str_replace('/', '\/', $route) . '$/';
    $this->routes[$patternRoute][$method] = $params;
  }

  public function get($route, $params = [])
  {
    return $this->addRoute('GET', $route, $params);
  }

  public function post($route, $params = [])
  {
    return $this->addRoute('POST', $route, $params);
  }

  public function put($route, $params = [])
  {
    return $this->addRoute('PUT', $route, $params);
  }

  public function delete($route, $params = [])
  {
    return $this->addRoute('DELETE', $route, $params);
  }

  public function getUri()
  {
    $uri = $this->request->getUri();
    $xUri = strlen($this->prefix) ? explode($this->prefix, $uri) : [$uri];

    return rtrim(end($xUri), '/');
  }

  private function getRoute()
  {
    $uri = $this->getUri();
    $httpMethod = $this->request->getHttpMethod();
    foreach ($this->routes as $patternRoute => $methods) {
      if (preg_match($patternRoute, $uri, $matches)) {
        if (isset($methods[$httpMethod])) {
          unset($matches[0]);

          $keys = $methods[$httpMethod]['variables'];
          $methods[$httpMethod]['variables'] = array_combine($keys, $matches);
          $methods[$httpMethod]['variables']['request'] = $this->request;

          return $methods[$httpMethod];
        }
        throw new Exception("Metodo não permitido, tente acessa-lo de outra forma.", 405);
      }
    }
    throw new Exception("URL não encontrada, nós não conseguimos encontrar a página que você está buscando", 404);
  }

  public function run()
  {
    try {
      $route = $this->getRoute();
      if (!isset($route['controller'])) {
        throw new Exception("A URL não pode ser processada", 500);
      }
      $args = [];
      $middlewareArgs = $route['middlewareArgs'] ?? [];

      $reflection = new ReflectionFunction($route['controller']);

      // Define os argumentos dos controladores
      foreach ($reflection->getParameters() as $parameter) {
        $name = $parameter->getName();
        $args[$name] = $route['variables'][$name] ?? '';
      }

      // Retorna a execução da fila de middlewares
      return (new MiddlewareQueue($route['middlewares'], $route['controller'], $args, $middlewareArgs))->next($this->request);
    } catch (Exception $e) {
      return new Response($e->getCode(), $e->getMessage(), $this->contentType);
    }
  }

  public function getCurrentUrl()
  {
    return $this->url . $this->getUri();
  }

  // Método responsável por redirecionar a url
  public function redirect($route)
  {
    $url = $this->url . $route;
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
      echo $url;
      exit;
    }

    header('location: ' . $url);
    exit;
  }
}
