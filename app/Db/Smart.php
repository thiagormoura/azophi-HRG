<?php

namespace App\Db;

use \PDO;
use \PDOException;

class Smart
{
  private static $host;
  private static $name;
  private static $user;
  private static $pass;
  private static $port;

  private $table;
  private $connection;

  public static function config($host, $name, $user, $pass, $port = 3306)
  {
    self::$host = $host;
    self::$name = $name;
    self::$user = $user;
    self::$pass = $pass;
    self::$port = $port;
  }

  public function __construct($table = null)
  {
    $this->table = $table;
    $this->setConnection();
  }

  private function setConnection()
  {
    try {
      $this->connection = new PDO("sqlsrv:server=" . self::$host . (!empty(self::$port) ? "," . self::$port : "") . ";Database=" . self::$name . ";Encrypt = true;
      TrustServerCertificate = true", self::$user, self::$pass);
      $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $this->connection->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_UTF8);
    } catch (PDOException $e) {
      die("ERROR :" . $e->getMessage());
    }
  }

  public function execute($query, $params = [])
  {
    try {
      $statament = $this->connection->prepare($query);
      $statament->execute($params);
      return $statament;
    } catch (PDOException $e) {
      die("ERROR :" . $e->getMessage());
    }
  }

  public function insert($values)
  {
    $fields = array_keys($values);
    $binds = array_pad([], count($fields), '?');

    $query = 'INSERT INTO ' . $this->table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $binds) . ')';

    $this->execute($query, array_values($values));

    return $this->connection->lastInsertId();
  }

  public function insertRaw($query)
  {
    $query = str_replace("{{table}}", $this->table, $query);
    $query = str_replace("''", "null", $query);

    $this->execute($query);

    return $this->connection->lastInsertId();
  }

  public function select($fields = '*', $where = null, $limit = null, $group = null,  $order = null)
  {
    $where = strlen($where) ? 'WHERE ' . $where : '';
    $limit = strlen($limit) ? 'LIMIT ' . $limit : '';
    $group = strlen($group) ? 'GROUP BY ' . $group : '';
    $order = strlen($order) ? 'ORDER BY ' . $order : '';

    $query = 'SELECT ' . $fields . ' FROM ' . $this->table . ' ' . $where . ' ' . $limit . ' ' . $group . ' ' . $order;

    // echo '<pre>';
    // echo $query;
    // echo '</pre>';

    // var_dump($query);

    return $this->execute($query);
  }

  public function with($fields = '*', $where = null, $limit = null, $group = null,  $order = null, $with)
  {
    $where = strlen($where) ? 'WHERE ' . $where : '';
    $limit = strlen($limit) ? 'LIMIT ' . $limit : '';
    $group = strlen($group) ? 'GROUP BY ' . $group : '';
    $order = strlen($order) ? 'ORDER BY ' . $order : '';

    $query = $with.' SELECT ' . $fields . ' FROM ' . $this->table . ' ' . $where . ' ' . $limit . ' ' . $group . ' ' . $order;

    // echo '<pre>';
    // echo $query;
    // echo '</pre>';
    // die();
    // var_dump($query);

    return $this->execute($query);
  }

  public function union($fields = '*', $where = null, $limit = null, $group = null,  $order = null, $union)
  {
    $where = strlen($where) ? 'WHERE ' . $where : '';
    $limit = strlen($limit) ? 'LIMIT ' . $limit : '';
    $group = strlen($group) ? 'GROUP BY ' . $group : '';
    $order = strlen($order) ? 'ORDER BY ' . $order : '';

    $query = 'SELECT ' . $fields . ' FROM ' . $this->table . ' ' . $where . ' ' . $limit . ' ' . $group . ' ' . $order . ' UNION ' . $union;

    // echo '<pre>';
    // echo $query;
    // echo '</pre>';

    return $this->execute($query);
  }

  public function unionAll($fields = '*', $where = null, $limit = null, $group = null,  $order = null, $union)
  {
    $where = strlen($where) ? 'WHERE ' . $where : '';
    $limit = strlen($limit) ? 'LIMIT ' . $limit : '';
    $group = strlen($group) ? 'GROUP BY ' . $group : '';
    $order = strlen($order) ? 'ORDER BY ' . $order : '';

    $query = 'SELECT ' . $fields . ' FROM ' . $this->table . ' ' . $where . ' ' . $limit . ' ' . $group . ' ' . $order . ' UNION ALL ' . $union;

    // echo '<pre>';
    // echo $query;
    // echo '</pre>';

    return $this->execute($query);
  }

  public function update($where, $values)
  {
    $fields = array_keys($values);

    $query = 'UPDATE ' . $this->table . ' SET ' . implode('=?, ', $fields) . '=? WHERE ' . $where;

    $this->execute($query, array_values($values));
    return true;
  }

  public function updateRaw($query)
  {
    $this->execute($query);
    return true;
  }

  public function delete($where)
  {
    $query = 'DELETE FROM ' . $this->table . ' WHERE ' . $where;
    $this->execute($query);

    return true;
  }
}
