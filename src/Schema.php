<?php
/**
 * Created by PhpStorm.
 * User: melvin
 * Date: 01/10/17
 * Time: 13:15
 */

namespace Orm;

class Schema
{
    private $schema = [];

    /**
     * Schema constructor.
     */
    public function __construct()
    {
    }

    /**
     * Transforme le tableau renvoyé par 'DESCRIBE nom_de_la_table' en schema
     *
     * @param $schema
     */
    public function parseDatabaseSchema(array $schema)
    {
        foreach ($schema as $table => $columns) {
            $this->schema[$table] = [];

            foreach ($columns as $column) {
                $this->schema[$table][$column['Field']] = [
                    'type'    => $column['Type'],
                    'null'    => $column['Null'],
                    'default' => $column['Default']
                ];
            }
        }
    }

    /**
     * Transforme le tableau renvoyé par YAML
     *
     * @param array $schema
     */
    public function parseEntitiesSchema(array $schema)
    {
        $this->schema = $schema['entities'];
    }

    /**
     * @return mixed
     */
    public function getSchema() : array
    {
        return $this->schema;
    }

    /**
     * Retourne les tables contenu dans le schema
     *
     * @return array
     */
    public function getTables() : array
    {
        $tmpArray = [];

        foreach ($this->schema as $table => $columns) {
            $tmpArray[] = $table;
        }

        return $tmpArray;
    }

    /**
     * Retourne une table
     *
     * @param  $table
     * @return mixed
     */
    public function getTableDescription($table) : array
    {
        return $this->schema[$table];
    }

    /**
     * Méthode qui ne fait qu'appeler la méthode compareSchemaTables
     * Je trouve juste le nom plus clair que la méthode qu'elle appelle
     * mais pour m'y retrouver je préfère ne pas modifier le nom de la méthode qu'elle appel
     *
     * ATTENTION : Cette méthode à un sens. Elle prend l'objet en paramètre comme obsolète
     * et se base donc sur $this pour déterminer les modifications
     *
     * @param  Schema $schema
     * @return array
     */
    public function compare(Schema $schema) : array
    {
        return $this->compareSchemaTables($schema);
    }

    /**
     * Renvoi un tableau décrivant les éléments ajoutées, les éléments modifier et ceux supprimées
     * ATTENTION : Cette méthode à un sens. Elle prend l'objet en paramètre comme obsolète
     * et se base donc sur $this pour déterminer les modifications
     *
     * @param  Schema $schema
     * @return array
     */
    public function compareSchemaTables(Schema $schema) : array
    {
        $tmpArray = [];

        /**
         * Tables ajoutées ou modifiées
         */
        foreach ($this->getTables() as $table) {
            if (in_array($table, $schema->getTables())) {
                if (!empty($comparaison = $this->compareTableColumns($schema, $table))) {
                    if (!array_key_exists('updated', $tmpArray)) {
                        $tmpArray['updated'] = [];
                    }

                    $tmpArray['updated'][$table] = $comparaison;
                }
            } else {
                // Si la catégorie 'added' n'existe pas dans le tableau,
                // on la créé pour y mettre les tables nouvellement ajoutées
                if (!array_key_exists('added', $tmpArray)) {
                    $tmpArray['added'] = [];
                }

                $tmpArray['added'][$table] = $this->getTableDescription($table);
            }
        }

        /**
         * Tables supprimées
         */
        foreach ($schema->getTables() as $table) {
            if (!in_array($table, $this->getTables())) {
                if (!array_key_exists('removed', $tmpArray)) {
                    $tmpArray['removed'] = [];
                }

                $tmpArray['removed'][] = $table;
            }
        }

        return $tmpArray;
    }

    /**
     * Retourne un tableau décrivant les colonnes ajoutées, modifiées, supprimées d'une table
     *
     * @param  Schema $schema
     * @param  string $table
     * @return array
     */
    public function compareTableColumns(Schema $schema, string $table) : array
    {
        /**
         * Contient la réponse de la méthode
         *
         * Ce tableau peut être constitué des clés ['added']['updated']
         */
        $tmpArray = [];

        /**
         * On récupère le schema de la table externe $table (celle que l'ont compare avec l'objet courant)
         * (NOTE : L'objet courant est considéré comme actualisé est sert donc de base de comparaison
         *  tandis que $table est considéré comme l'objet obselète.)
         *
         * Exemple de ce que $externalSchemaTable peut contenir :
         *
         * Array (
         *     first_name => Array(
         *         type => VARCHAR(25),
         *         null => YES,
         *         default =>
         *     ),
         *     email => Array(
         *         type => VARCHAR(50),
         *         null => YES,
         *         default =>
         *     )
         * )
         */
        $externalSchemaTable = $schema->getTableDescription($table);

        /**
         * Pour chaque colonne et sa description
         * On test si la colonne existe dans la description du schema obsolète (externe)
         *
         * Si la colonne existe, on regarde si une des clé de sa description à changé
         * On fait donc une boucle sur $description
         *
         * Exemple des clés de description : ($description contient)
         *
         * $description = Array(
         *     'type' => VARCHAR(255),
         *     'null' => YES,
         *     'default' =>
         * )
         *
         * Sinon si la colonne n'existe pas dans le schema obsolète,
         * on l'ajoute dans la partie 'added' de la réponse.
         */
        foreach ($this->getTableDescription($table) as $column => $description) {
            if (array_key_exists($column, $externalSchemaTable)) {
                foreach ($description as $key => $item) {
                    if ($externalSchemaTable[$column][$key] !== $item) {

                        /**
                         * Si la clé 'updated' n'existe pas encore, on la créée
                         */
                        if (!array_key_exists('updated', $tmpArray)) {
                            $tmpArray['updated'] = [];
                        }

                        /**
                         * On ajoute la description modifié à la réponse
                         */
                        $tmpArray['updated'][$column] = $description;
                    }
                }
            } else {

                /**
                 * Si la clé 'added' n'existe pas encore, on la créée
                 */
                if (!array_key_exists('added', $tmpArray)) {
                    $tmpArray['added'] = [];
                }

                /**
                 * On ajoute la colonne ajoutée à la réponse
                 */
                $tmpArray['added'][$column] = $description;
            }
        }

        /**
         * On cherche si des colonnes ont été supprimé dans le schéma de base
         * afin de pouvoir les supprimer dans le schema obsolète
         */
        foreach ($externalSchemaTable as $column => $description) {
            if ($column !== 'id') {
                if (!array_key_exists($column, $this->getTableDescription($table))) {

                    /**
                     * Si la clé 'removed' n'existe pas encore, on la créée
                     */
                    if (!array_key_exists('removed', $tmpArray)) {
                        $tmpArray['removed'] = [];
                    }

                    /**
                     * On ajoute la colonne supprimée à la réponse
                     */
                    $tmpArray['removed'][] = $column;
                }
            }
        }

        return $tmpArray;
    }

    /**
     * @param mixed $schema
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;
    }
}
