<?php

namespace Orm;

class Entity
{
    private $label;
    private $properties;

    public function __construct(string $entity, Schema $schema, array $values = [])
    {
        $this->label = strtolower($entity);

        $schema = $schema->getTableDescription($entity);

        // Si un tableau de valeur a été ajouté, on hydrate l'objet
        if (!empty($values)) {
            foreach ($values as $property => $value) {
                $this->properties[$property] = $value;
            }
        } else {
            // On créé toutes les propriétés de l'objet en suivant son schema
            foreach ($schema as $field => $description) {
                $this->properties[$field] = null;
            }
        }
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function set(string $property, string $value)
    {

        if (!array_key_exists($property, $this->properties)) {
            die('Vous ne pouvez pas modifier le champ "'
                . $property
                . '" sur l\'entité "'
                . $this->getLabel()
                . '" car ce champ n\'existe pas.');
        }

        $this->properties[$property] = $value;
    }

    public function get(string $property)
    {
        return $this->properties[$property];
    }

    public function getLabel()
    {
        return strtolower($this->label);
    }
}
