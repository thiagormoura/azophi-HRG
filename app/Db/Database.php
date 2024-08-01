<?php

namespace App\Db;

use \PDO;
use \PDOException;
use PDOStatement;

class Database
{
  private static $host;
  private static $name;
  private static $user;
  private static $pass;
  private static $port;

  private $table;
  private $connection;

  public static function config($host, $user, $pass, $port = 3306)
  {
    self::$host = $host;
    self::$user = $user;
    self::$pass = $pass;
    self::$port = $port;
  }

  public function __construct($name, $table)
  {
    self::$name = $name;
    $this->table = $table;
    $this->setConnection();
  }

  private function setConnection()
  {
    try {
      $this->connection = new PDO('mysql:host=' . self::$host . ';dbname=' . self::$name . ';port=' . self::$port, self::$user, self::$pass);
      $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    } catch (PDOException $e) {
      die("ERROR :" . $e->getMessage());
    }
  }

  private function getType($value)
  {
    $type = PDO::PARAM_STR;
    switch (true) {
      case is_null($value) || empty($value) && $value != 0:
        $type = PDO::PARAM_NULL;
        break;
      case is_numeric($value) && ctype_digit($value):
        $type = PDO::PARAM_INT;
        break;
      default:
        break;
    }
    return $type;
  }

  private function executeWithDataTypes(PDOStatement $statement, array $values)
  {
    $count = 1;
    foreach ($values as $value) {
      $type = $this->getType($value);
      $statement->bindValue($count, $value, $type);
      $count++;
    }
    $statement->execute();
    return $statement;
  }

  public function execute($query, $params = [])
  {
    try {
      // Validação dos campos para inserção de dados
      $params = array_map(function ($value) {
        return trim(strip_tags($value));
      }, $params);
      $statement = $this->connection->prepare($query);
      return $this->executeWithDataTypes($statement, $params);
    } catch (PDOException $e) {
      die("ERROR :" . $e->getMessage());
    }
  }
  public function insert($values)
  {
    $fields = array_keys($values);
    $binds = array_pad([], count($fields), '?');
    $query = 'INSERT INTO ' . $this->table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $binds) . ')';
    $query = preg_replace('/<[^>]*>/', '', $query);
    $this->execute($query, array_values($values));

    return $this->connection->lastInsertId();
  }

  public function select($fields = '*', $where = null, $limit = null, $group = null,  $order = null)
  {
    $where = strlen($where) ? 'WHERE ' . $where : '';
    $limit = strlen($limit) ? 'LIMIT ' . $limit : '';
    $group = strlen($group) ? 'GROUP BY ' . $group : '';
    $order = strlen($order) ? 'ORDER BY ' . $order : '';

    $query = 'SELECT ' . $fields . ' FROM ' . $this->table . ' ' . $where . ' ' . $group . ' ' . $order . ' ' . $limit;
    // echo '<pre>';
    // echo $query;
    // echo '</pre>';
    return $this->execute($query);
  }

  public function union($fields = '*', $where = null, $limit = null, $group = null,  $order = null, $union)
  {
    $where = strlen($where) ? 'WHERE ' . $where : '';
    $limit = strlen($limit) ? 'LIMIT ' . $limit : '';
    $group = strlen($group) ? 'GROUP BY ' . $group : '';
    $order = strlen($order) ? 'ORDER BY ' . $order : '';

    $query = 'SELECT ' . $fields . ' FROM ' . $this->table . ' ' . $where . ' ' . $group . ' ' . $order . ' ' . $limit . ' UNION ' . $union;
    return $this->execute($query);
  }

  public function unionAll($fields = '*', $where = null, $limit = null, $group = null,  $order = null, $union)
  {
    $where = strlen($where) ? 'WHERE ' . $where : '';
    $limit = strlen($limit) ? 'LIMIT ' . $limit : '';
    $group = strlen($group) ? 'GROUP BY ' . $group : '';
    $order = strlen($order) ? 'ORDER BY ' . $order : '';

    $query = 'SELECT ' . $fields . ' FROM ' . $this->table . ' ' . $where . ' ' . $group . ' ' . $order . ' ' . $limit . ' UNION ALL ' . $union;
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

  public function delete($where)
  {
    $query = 'DELETE FROM ' . $this->table . ' WHERE ' . $where;
    $this->execute($query);

    return true;
  }
}
