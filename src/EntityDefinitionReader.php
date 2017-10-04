<?php

namespace Orm;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class EntityDefinitionReader
{
    public static function getEntitiesDefinition(string $entityName, string $filePath)
    {
        $entities = [];

        try {
            $entities = Yaml::parse(file_get_contents($filePath));
        } catch (ParseException $e) {
            printf("Impossible de lire le fichier de configuration [%s] : %s", $filePath, $e->getMessage());
        }

        return $entities[$entityName];
    }
}
