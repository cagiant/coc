<?php


namespace coc\dao;


class LibPDO
{
    protected $pdo;

    public function __construct($params = null)
    {
        if (empty($params)) {
            return;
        }

        $host     = $params['host'];
        $port     = $params['port'];
        $username = $params['user_name'];
        $password = $params['password'];
        $database = $params['database'];
        $charset  = $params['charset'];
        $engine   = 'mysql';
        $options  = null;

        $this->setConnection($engine, $host, $port, $username, $password, $database, $charset, $options);
    }

    private function setConnection($engine, $host, $port, $username, $password, $database, $charset, $options)
    {
        $engine = strtolower($engine);
        if ($engine === 'mysql') {
            if ($options === null) {
                $options = [
                    \PDO::ATTR_EMULATE_PREPARES => false
                ];
            }
            $this->pdo = new \PDO(
                'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $database . ';charset=' . $charset,
                $username,
                $password,
                $options
            );
            if (!empty($charset)) {
                $this->pdo->query("set names " . $charset);
            }
            return true;
        } else {
            return false;
        }
    }

    public function insert($sql, $pk = null)
    {
        $rows = $this->pdo->exec($sql);
        if ($rows) {
            return $this->pdo->lastInsertId($pk);
        }
        return false;
    }

    public function getAll($sql)
    {
        $stmt = $this->pdo->query($sql);
        if($stmt) {
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $rows;
        }

        return false;
    }

    public function getCol($sql)
    {
        $stmt = $this->pdo->query($sql);
        if($stmt) {
            $rows = $stmt->fetchAll(\PDO::FETCH_BOTH);
            $col = array();
            if ($rows) {
                foreach ($rows as $row) {
                    $col[] = $row[0];
                }
            }
            return $col;
        }

        return false;
    }

    public function getOne($sql)
    {
        //FETCH_BOTH
        $stmt = $this->pdo->query($sql);
        if($stmt) {
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if ($rows) {
                $row = $rows[0];
                if ($row) {
                    $row = array_values($row);
                    return $row[0];
                }
            }
        }

        return false;
    }

    public function exec($sql)
    {
        $rows = $this->pdo->exec($sql);
        return $rows;
    }
}