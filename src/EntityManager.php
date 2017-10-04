<?php

namespace Orm;

class EntityManager
{
    /**
     * Contient le query builder
     *
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var string
     */
    private $entityDefinitionFilePath;

    /**
     * EntityManager constructor.
     * @param DatabaseConnection $connection
     * @param string $entityDefinitionFilePath
     */
    public function __construct(DatabaseConnection $connection, string $entityDefinitionFilePath)
    {
        $this->queryBuilder = new QueryBuilder($connection);
        $this->entityDefinitionFilePath = $entityDefinitionFilePath;
    }

    public function save(Entity $entity)
    {
        $this->queryBuilder->insert($entity->getLabel(), $entity->getProperties());
    }

    /**
     * Retourne la liste d'une table sous forme d'objet
     *
     * @param string $table
     * @param array $options
     * @return QueryResult
     */
    public function list(string $table, array $options = [])
    {
        return new QueryResult($this->queryBuilder->select($table, $options), $this->entityDefinitionFilePath, $table);
    }

    /**
     * Trouve une entrée
     *
     * @param string $table
     * @param array $options
     * @return QueryResult
     */
    public function find(string $table, array $options = [])
    {

        $options['limit'] = '1';

        return new QueryResult($this->queryBuilder->select($table, $options), $this->entityDefinitionFilePath, $table);
    }

    /**
     * Retourne la dernière entrée d'une table
     *
     * @param string $table
     * @return QueryResult
     */
    public function getLast(string $table)
    {
        $options = [
            'order_by' => [
                'id' => 'desc'
            ],
            'limit' => '1'
        ];

        return new QueryResult($this->queryBuilder->select($table, $options), $this->entityDefinitionFilePath, $table);
    }

    /**
     * Recherche dans la base de données et retourne les résultats
     *
     * @param string $table
     * @param array $options
     * @return QueryResult | null
     */
    public function search(string $table, $options = [])
    {

        if (empty($options)) {
            die('Vous ne pouvez pas faire une recherche vide. [' . __FILE__ . "][" . __LINE__ . "]");
        } else {
            /**
             * Si les options envoyées sont un tableau, on l'ajoute à la clé 'search'
             */
            if (is_array($options)) {
                $options = [
                    'search' => $options
                ];
            }
        }

        $PDOResult = $this->queryBuilder->select($table, $options);

        if ($PDOResult != false) {
            return new QueryResult($PDOResult, $this->entityDefinitionFilePath, $table);
        }

        return null;
    }
}
