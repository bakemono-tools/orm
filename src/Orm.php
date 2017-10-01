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
     * @var \PDO
     */
    private $connection = null;

    /**
     * Orm constructor.
     * @param string $filePath Chemin ABSOLUE vers le fichier de configuration
     */
    public function __construct(string $filePath)
    {
        $config = null;

        /**
         * On tente de lire le fichier de configuration contenant les paramètres de connexion à la base de données
         */
        try {
            $config = Yaml::parse(file_get_contents($filePath));
        } catch (ParseException $e) {
            printf("Impossible de lire le fichier de configuration [%s] : %s", $filePath, $e->getMessage());
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
     * @return \PDO
     */
    public function getDatabaseConnection()
    {
        if ($this->connection == null) {
            $databaseConnection = new DatabaseConnection($this->getConnectionParams());
            $this->connection = $databaseConnection->getConnection();
        }

        return $this->connection;
    }
}
