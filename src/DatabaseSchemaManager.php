<?php

namespace Orm;

class DatabaseSchemaManager
{
    /**
     * Contient la connection à la base de données
     *
     * @var \PDO
     */
    private $connection;

    /**
     * DatabaseSchemaManager constructor.
     * @param \PDO $connection
     */
    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function updateDatabase(Schema $entitiesDefinitionSchema, Schema $obsoleteDatabaseSchema): array
    {

        /**
         * Exemple de tableau que peut contenir $changed
         *
         * Array
         *   (
         *       [updated] => Array
         *       (
         *           [fake] => Array
         *           (
         *               [updated] => Array
         *               (
         *                   [first_name] => Array
         *                   (
         *                       [0] => Array
         *                       (
         *                           [type] => bool
         *                       )
         *
         *                       [1] => Array
         *                       (
         *                           [null] => NO
         *                       )
         *
         *                       [2] => Array
         *                       (
         *                           [default] => 1
         *                       )
         *
         *                   )
         *
         *               )
         *
         *               [added] => Array
         *               (
         *                   [0] => username
         *               )
         *
         *           )
         *
         *           [message] => Array
         *           (
         *               [updated] => Array
         *               (
         *                   [subject] => Array
         *                   (
         *                       [0] => Array
         *                       (
         *                           [type] => varchar(255)
         *                       )
         *
         *                    )
         *
         *               )
         *
         *           )
         *
         *       )
         *
         *       [added] => Array
         *       (
         *           [0] => test
         *       )
         *
         *       [removed] => Array
         *       (
         *           [0] => poke
         *       )
         *
         *   )
         */
        $changed = $entitiesDefinitionSchema->compare($obsoleteDatabaseSchema);

        /**
         * On ajoute les tables manquantes
         */
        if (array_key_exists('added', $changed)) {
            foreach ($changed['added'] as $table => $description) {
                $this->createTable($table, $description);
            }
        }

        /**
         * On supprime les tables obsolètes
         */
        if (array_key_exists('removed', $changed)) {
            foreach ($changed['removed'] as $table) {
                $this->dropTable($table);
            }
        }

        /**
         * On met à jour les tables qui ont été modifié
         */
        if (array_key_exists('updated', $changed)) {
            foreach ($changed['updated'] as $table => $columns) {

                /**
                 * On ajoute les nouvelles colonnes
                 */
                if (array_key_exists('added', $columns)) {
                    foreach ($columns['added'] as $column => $description) {
                        $this->createColumn($table, $column, $description);
                    }
                }

                /**
                 * On supprime les colonnes obsolètes
                 */
                if (array_key_exists('removed', $columns)) {
                    foreach ($columns['removed'] as $column) {
                        $this->dropColumn($table, $column);
                    }
                }

                /**
                 * On met à jour les autres colonnes
                 */
                if (array_key_exists('updated', $columns)) {
                    foreach ($columns['updated'] as $column => $description) {
                        $this->updateColumn($table, $column, $description);
                    }
                }
            }
        }

        return $changed;
    }

    /**
     * Créée une table
     *
     * @param string $tableName
     * @param array $TableDescription
     */
    public function createTable(string $tableName, array $TableDescription)
    {
        print "Génération de la table [" . $tableName . "]\n\n";

        $query = 'CREATE TABLE IF NOT EXISTS ' . $tableName . ' (';

        $query .= 'id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, ';

        /**
         * Pour chaque colonne on ajoute sa description pour la création de la table
         */
        foreach ($TableDescription as $column => $description) {
            if ($description['null'] == 'YES') {
                $null = 'NULL';
            } else {
                $null = 'NOT NULL';
            }

            $query .= $column . ' ' . $description['type'] . ' ' . $null . ', ';
        }

        /**
         * On supprime la virgule et l'espace à la fin de la chaine de caractère
         */
        $query = substr($query, 0, -2);

        $query .= ')';

        $this->connection->query($query);
    }

    /**
     * Supprime une table
     *
     * @param string $tableName
     */
    public function dropTable(string $tableName)
    {
        print "Suppression de la table [" . $tableName . "].\n\n";
        $this->connection->query('DROP TABLE . ' . $tableName);
    }

    /**
     * Ajoute une colonne à une table
     *
     * @param string $table
     * @param string $column
     * @param array  $description
     */
    public function createColumn(string $table, string $column, array $description)
    {
        print "Création du champ [" . $table . "][" . $column . "].\n\n";

        if ($description['null'] == 'YES') {
            $null = 'NULL';
        } else {
            $null = 'NOT NULL';
        }

        $this->connection->query('ALTER TABLE '
            . $table
            . ' ADD '
            . $column
            . ' '
            . $description['type']
            . ' '
            . $null);
    }

    /**
     * Supprime une colonne d'une table
     *
     * @param string $table
     * @param string $column
     */
    public function dropColumn(string $table, string $column)
    {
        print "Suppression de la colonne [" . $table . "][" . $column . "].\n\n";
        $this->connection->query('ALTER TABLE ' . $table . ' DROP ' . $column);
    }

    /**
     * Met à jour les caractéristiques d'une colonne
     *
     * @param string $table
     * @param string $column
     * @param array $description
     */
    public function updateColumn(string $table, string $column, array $description)
    {
        print "Mise à jour de la colonne [" . $table . "][" . $column . "].\n\n";

        if ($description['null'] == 'YES') {
            $null = 'NULL';
        } else {
            $null = 'NOT NULL';
        }

        $this->connection->query('ALTER TABLE '
            . $table
            . ' MODIFY '
            . $column
            . ' '
            . $description['type']
            . ' '
            . $null);
    }
}
