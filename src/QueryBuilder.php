<?php

namespace Orm;

class QueryBuilder
{
    /**
     * Contient le requête à effectuer
     *
     * @var string
     */
    private $query;

    /**
     * Contient la connection
     *
     * @var DatabaseConnection
     */
    private $connection;

    /**
     * QueryBuilder constructor.
     * @param DatabaseConnection $connection
     */
    public function __construct(DatabaseConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Créé et execute une requête de type select
     *
     * @param string $table
     * @param array $options
     * @return \PDOStatement
     */
    public function select(string $table, array $options = [])
    {
        $this->query = 'SELECT * FROM ' . $table;

        /**
         * WHERE
         */
        if (array_key_exists('where', $options)) {
            if (is_array($options['where'])) {
                
                $cpt = 0;

                foreach ($options['where'] as $key => $value) {
                    if ($cpt === 0) {
                        $this->query .= " WHERE";
                    } else {
                        $this->query .= " AND";
                    }

                    $this->query .= " " . $key . " " . $value;

                    $cpt++;
                }
            } else {
                die('Le clé "where" du tableau d\'option n\'est pas un tableau.');
            }
        }

        /**
         * SEARCH
         */
        if (array_key_exists('search', $options)) {
            if (is_array($options['search'])) {
                $cpt = 0;

                foreach ($options['search'] as $key => $value) {
                    // Si la clause where n'a pas déjà été utilisée
                    if ($cpt === 0 && !array_key_exists('where', $options)) {
                        $this->query .= " WHERE";
                    } else {
                        $this->query .= " OR";
                    }

                    $this->query .= " " . $key . " LIKE \"%" . $value . "%\"";

                    $cpt++;
                }
            } else {
                die('Le clé "search" du tableau d\'option n\'est pas un tableau.');
            }
        }

        /**
         * ORDER BY
         */
        if (array_key_exists('order_by', $options)) {
            if (is_array($options['order_by'])) {
                $cpt = 0;

                foreach ($options['order_by'] as $key => $value) {
                    if ($cpt === 0) {
                        $this->query .= " ORDER BY";
                    } else {
                        $this->query .= ", ";
                    }

                    $this->query .= " " . $key . " " . strtoupper($value);

                    $cpt++;
                }
            }
        }

        /**
         * LIMIT
         */
        if (array_key_exists('limit', $options)) {
            $this->query .= " LIMIT " . $options['limit'];
        }

        return $this->connection->getConnection()->query($this->query);
    }

    public function insert(string $table, array $values)
    {

        $this->query = "INSERT INTO " . $table . "(";

        foreach ($values as $key => $value) {
            if ($key != 'id') {
                $this->query .= $key . ', ';
            }
        }

        // On supprime la dernière itération ", " réalisée dans la boucle
        $this->query = substr($this->query, 0, -2);

        $this->query .= ") VALUES(";

        foreach ($values as $key => $value) {
            if ($key != 'id') {
                $this->query .= '"' . $value . '", ';
            }
        }

        // On supprime la dernière itération ", " réalisée dans la boucle
        $this->query = substr($this->query, 0, -2);

        $this->query .= ")";

        $this->connection->getConnection()->query($this->query);
    }

    /**
     * @param string $value
     * @return string
     */
    public static function equal(string $value): string
    {
        return "= \"" . $value . "\"";
    }

    /**
     * @param string $value
     * @param bool $equal
     * @return string
     */
    public static function lessThan(string $value, bool $equal = false) : string
    {
        $value = " \"" . $value . "\"";

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
        $value = " \"" . $value . "\"";

        if ($equal) {
            $value = "=" . $value;
        }

        return ">" . $value;
    }
}
