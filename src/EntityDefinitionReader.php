<?php

namespace Orm;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class EntityDefinitionReader
{
    /**
     * @param string $filePath
     * @param string|null $entityName
     * @return array
     */
    public static function getEntitiesDefinition(string $filePath, string $entityName = null) : array
    {
        $entities = [];

        try {
            $entities = Yaml::parse(file_get_contents($filePath));
        } catch (ParseException $e) {
            printf("Impossible de lire le fichier de configuration [%s] : %s", $filePath, $e->getMessage());
        }

        if ($entityName === null) {
            return $entities;
        } else {
            return $entities[$entityName];
        }
    }
}
