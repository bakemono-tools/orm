<?php

namespace Orm;


class DatabaseConnection
{
    private $connection;

    public function __construct(array $connectionParams)
    {
        $this->connection = $this->connect($connectionParams);
    }

    /**
     * @param array $connectionParams
     * @return \PDO
     * @throws \Exception
     */
    protected function connect(array $connectionParams)
    {
        try
        {
            return new \PDO('mysql:host=' . $connectionParams['host'] . ':' . $connectionParams['port'] . ';dbname=' . $connectionParams['dbname'] . ';charset=utf8', $connectionParams['user'], $connectionParams['password']);
        }
        catch (\Exception $e)
        {
            throw new \Exception("Hurricane n'a pas pu se connecter à la base de données. [" . __FILE__ . "][" . __LINE__ . "] : " . $e->getMessage());
        }
    }

    /**
     * @return \PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }
}