<?php

namespace App\Utils\Cache;

class File
{
  // Método responsável por retonar o caminho do cache
  public static function getFilePath($hash)
  {
    // Diretorio do cache
    $dir = getenv('CACHE_DIR');

    if (!file_exists($dir)) mkdir($dir, 0755, true);

    return $dir . '/' . $hash;
  }

  // Método responsável por armazenar o cache
  public static function storageCache($hash, $content)
  {
    // Serializa o conteudo
    $serialize = serialize($content);

    // pega caminho arquivo cache
    $cacheFile = self::getFilePath($hash);

    return file_put_contents($cacheFile, $serialize);
  }

  // Método responsável por retornar o conteudo no cache
  private static function getContentCache($hash, $expiration)
  {
    $cacheFile = self::getFilePath($hash);
    if (!file_exists($cacheFile)) return false;

    // Valida expiração do cache
    $createTime = filectime($cacheFile);
    $diffTime = time() - $createTime;
    if ($diffTime > $expiration) return false;

    $serialize = file_get_contents($cacheFile);
    return unserialize($serialize);
  }

  // Método responsável por obter info. do cache
  public static function getCache($hash, $expiration, $function)
  {
    // Verifica o conteúdo gravado
    if ($content = self::getContentCache($hash, $expiration)) {
      return $content;
    }

    // Execução da função
    $content = $function();

    self::storageCache($hash, $content);

    // retonar o conteúdo
    return $content;
  }
}
