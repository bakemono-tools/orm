<?php
/**
 * Created by PhpStorm.
 * User: melvin
 * Date: 02/10/17
 * Time: 13:16
 */

namespace Orm;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class QueryResult
{
    /**
     * @var array | Entity
     */
    private $result = [];

    /**
     * Chemin absolue vers le fichier de définition des entités
     *
     * @var Schema
     */
    private $entitiesSchema;

    /**
     * QueryResult constructor.
     * @param \PDOStatement $PDOQueryResult
     * @param Schema $entitiesSchema
     * @param string $entityName
     */
    public function __construct(\PDOStatement $PDOQueryResult, Schema $entitiesSchema, string $entityName)
    {
        if ($PDOQueryResult) {
            $this->entitiesSchema = $entitiesSchema;
            $this->parsePDOStatement($PDOQueryResult, $entityName);
        }
    }

    /**
     * Transforme la réponse de PDO en objets
     *
     * @param \PDOStatement $result
     * @param string $entityName
     */
    protected function parsePDOStatement(\PDOStatement $result, string $entityName)
    {
        $tmpArray = [];

        while ($row = $result->fetch()) {

            /**
             * Le résultat de la base de données contient des doublons
             * ex, pour le title et le content, le tableau contient
             *
             * [0] => "Mon titre",
             * [title] => "Mon titre",
             * [1] => "Mon super contenu",
             * [content] => "Mon super contenu"
             *
             * On enlève donc les lignes dont les indices sont des chiffres
             */
            foreach ($row as $key => $value) {
                if (!is_integer($key)) {
                    $tmpArray[$key] = $value;
                }
            }

            $this->result[] = new Entity($entityName, $this->entitiesSchema, $tmpArray);
        }
    }

    /**
     * Retourne le résultat de la requête sous forme de tableau d'objet
     *
     * @return array
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * Retourne true si le résultat de la requête est vide
     *
     * @return bool
     */
    public function isEmpty() : bool
    {
        if (count($this->result) === 0) {
            return true;
        } else {
            return false;
        }
    }
}
