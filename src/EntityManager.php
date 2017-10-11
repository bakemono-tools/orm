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
    private $entitiesSchema;

    /**
     * EntityManager constructor.
     * @param DatabaseConnection $connection
     * @param Schema $entitiesSchema
     */
    public function __construct(DatabaseConnection $connection, Schema $entitiesSchema)
    {
        $this->queryBuilder = new QueryBuilder($connection);
        $this->entitiesSchema = $entitiesSchema;
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
    public function list(string $table, array $options = []) : QueryResult
    {
        if (!array_key_exists('order_by', $options)) {
            $options['order_by'] = [
                'id' => 'desc'
            ];
        }

        return new QueryResult($this->queryBuilder->select($table, $options), $this->entitiesSchema, $table);
    }

    /**
     * Trouve une entrée
     *
     * @param string $table
     * @param array $options
     * @return Entity
     */
    public function find(string $table, array $options = [])
    {
        /**
         * La méthode find permet de chercher qu'une seule entité, on a donc toujours "WHERE key = value" et non WHERE "key > value"
         * Donc on ajoute automatiquement le "=" pour évité de le faire soit même à chaque appelle de find()
         */
        foreach ($options as $key => $value) {
            $options[$key] = "= " . $value;
        }

        $options['where'] = $options;
        $options['limit'] = '1';

        $result = new QueryResult($this->queryBuilder->select($table, $options), $this->entitiesSchema, $table);
        $entities = $result->getResult();

        if (array_key_exists(0, $entities))
            return $entities[0];
        else
            return null;
    }

    /**
     * Retourne la dernière entrée d'une table
     *
     * @param string $table
     * @return Entity
     */
    public function getLast(string $table)
    {
        $options = [
            'order_by' => [
                'id' => 'desc'
            ],
            'limit' => '1'
        ];

        $result = new QueryResult($this->queryBuilder->select($table, $options), $this->entitiesSchema, $table);
        $entities = $result->getResult();

        if (array_key_exists(0, $entities))
            return $entities[0];
        else
            return null;
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
            return new QueryResult($PDOResult, $this->entitiesSchema, $table);
        }

        return null;
    }

    /**
     * @param string $value
     * @return string
     */
    public static function equal(string $value): string
    {
        return "= " . $value;
    }

    /**
     * @param string $value
     * @param bool $equal
     * @return string
     */
    public static function lessThan(string $value, bool $equal = false) : string
    {
        $value = " " . $value;

        if ($equal) {
            $value = "=" . $value;
        }

        return "<" . $value;
    }

    /**
     * @param string $value
     * @param bool $equal
     * @return string
     */
    public static function greaterThan(string $value, bool $equal = false) : string
    {
        $value = " " . $value;

        if ($equal) {
            $value = "=" . $value;
        }

        return ">" . $value;
    }
}
