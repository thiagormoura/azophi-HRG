<?php

namespace App\Http\Middleware;

use App\Utils\Cache\File as CacheFile;

class Cache
{
  // Verifica se a request é cacheavel
  private function isCacheable($request)
  {
    // Valida tempo de cache
    if (getenv('CACHE_TIME') <= 0) return false;
    // Valida os método http
    if ($request->getHttpMethod() != 'GET') return false;
    // Valida os headers
    $headers = $request->getHeaders();
    if (isset($headers['Cache-Control']) && $headers['Cache-Control'] == 'no-cache') return false;

    return true;
  }

  // Método responsável por gerar a hash do cahce
  private function getHash($request)
  {
    $uri = $request->getRouter()->getUri();
    $queryParams = $request->getQueryParams();
    $uri .= !empty($queryParams) ? '?' . http_build_query($queryParams) : '';

    return rtrim('route-' . preg_replace('/[^0-9a-zA-Z]/', '-', ltrim($uri, '/')), '-');
  }

  // Método responsável por executar o middleware
  public function handle($request, $next)
  {
    session_start();
    // Verifica se a request é cacheavel
    if (!$this->isCacheable($request)) return $next($request);

    $hash = $this->getHash($request);
    // Retonar os dados do cache
    return CacheFile::getCache($hash, getenv('CACHE_TIME'), function () use ($request, $next) {
      return $next($request);
    });
  }
}
