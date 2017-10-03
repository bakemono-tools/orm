<?php

namespace Orm;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class Orm
{
    /**
     * Contient les informations de connection
     *
     * Ex :
     * [
     *      'host'     => 'localhost',
     *      'port'     => null,
     *      'dbname'  => 'test',
     *      'user'     => 'root',
     *      'password' => 'root'
     * ]
     *
     * @var array | string
     */
    private $connectionParams = [];

    /**
     * Contient la connection en cours à la base de données
     *
     * @var DatabaseConnection
     */
    private $databaseConnection;

    /**
     * Contient le database schema manager
     *
     * @var DatabaseSchemaManager
     */
    private $dbSchemaManager;

    /**
     * Contient l'entityManager
     *
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Contient le chemin absolu vers le fichier de définition des entités
     *
     * @var string
     */
    private $entityDefinitionFilePath;

    /**
     * Orm constructor.
     * @param string $configurationFilePath Chemin ABSOLUE vers le fichier de configuration
     * @param string $entityDefinitionFilePath Chemin ABSOLUE vers le fichier des entités
     */
    public function __construct(string $configurationFilePath, string $entityDefinitionFilePath)
    {
        $config = null;

        /**
         * On tente de lire le fichier de configuration contenant les paramètres de connexion à la base de données
         */
        try {
            $config = Yaml::parse(file_get_contents($configurationFilePath));
        } catch (ParseException $e) {
            printf("Impossible de lire le fichier de configuration [%s] : %s", $configurationFilePath, $e->getMessage());
        }

        /**
         * Si la variable config est à null c'est qu'une erreur inconnue est survenue
         */
        if ($config != null) {
            /**
             * On récupère les paramètres de connexion.
             *
             * $this->connectionParams contient un tableau comme le suivant :
             * [
             *      'host'     => 'localhost',
             *      'port'     => null,
             *      'dbname'  => 'test',
             *      'user'     => 'root',
             *      'password' => 'root'
             * ]
             */
            $this->connectionParams = $config['database'];
        } else {
            die("Une erreur est survenue lors de la lecture de la configuration. [" . __FILE__ . "][" . __LINE__ . "]");
        }

        $this->entityDefinitionFilePath = $entityDefinitionFilePath;
        $this->dbSchemaManager = new DatabaseSchemaManager($this->getDatabaseConnection()->getConnection());
        $this->entityManager = new EntityManager($this->getDatabaseConnection(), $this->entityDefinitionFilePath);
    }

    /**
     * Retourne un tableau contenant les paramètres de connexion
     *
     * @return array
     */
    public function getConnectionParams(): array
    {
        return $this->connectionParams;
    }

    /**
     * Retourne un paramètre de connexion en particulier
     *
     * @param string $param
     * @return mixed
     */
    public function getConnectionParam(string $param): string
    {
        return $this->connectionParams[$param];
    }

    /**
     * Retourne une instance de PDO
     * Si aucune instance existe, elle en créé une et la retourne
     *
     * @return DatabaseConnection
     */
    public function getDatabaseConnection()
    {
        if ($this->databaseConnection == null) {
            $this->databaseConnection = new DatabaseConnection($this->getConnectionParams());
        }

        return $this->databaseConnection;
    }

    /**
     * @return array
     */
    public function updateDatabaseSchema()
    {
        $entitiesDefinition = null;

        try {
            $entitiesDefinition = Yaml::parse(file_get_contents($this->entityDefinitionFilePath));
        } catch (ParseException $e) {
            printf("Impossible de lire le fichier de configuration [%s] : %s", $this->entityDefinitionFilePath, $e->getMessage());
        }

        $entitiesSchema = new Schema();
        $entitiesSchema->parseEntitiesSchema($entitiesDefinition);

        $databaseSchema = new Schema();
        $databaseSchema->parseDatabaseSchema($this->getDatabaseConnection()->getDatabaseDescription());

        return $this->dbSchemaManager->updateDatabase($entitiesSchema, $databaseSchema);
    }

    /**
     * Retourne l'entity manager
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }
}
