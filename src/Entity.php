<?php

namespace Orm;

class Entity
{
    const SILENT_MODE = true;

    private $label;
    private $properties;
    private $schema;

    public function __construct(string $entity, Schema $schema, array $values = [])
    {
        $this->label = strtolower($entity);
        $this->schema = $schema;

        $schema = $schema->getTableDescription($entity);

        /**
         * On créé la propriété id car elle n'apparaît pas dans schema.yml
         */
        $this->properties['id'] = null;

        // On créé toutes les propriétés de l'objet en suivant son schema
        foreach ($schema as $field => $description) {
            $this->properties[$field] = null;
        }

        // Si un tableau de valeur a été ajouté, on hydrate l'objet
        if (!empty($values)) {
            foreach ($values as $property => $value) {
                $this->set($property, $value);
            }
        }
    }

    /**
     * @return mixed
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param string $property
     * @param string $value
     * @param bool $silentMode Si ce paramètre vaut true, aucune erreur ne sera retourné par cette méthode | Non utilisé
     */
    public function set(string $property, string $value, bool $silentMode = false)
    {
        if (!$silentMode && !array_key_exists($property, $this->properties)) {
            die('Vous ne pouvez pas modifier le champ "'
                . $property
                . '" sur l\'entité "'
                . $this->getLabel()
                . '" car ce champ n\'existe pas.');
        } else {
            if (array_key_exists($property, $this->properties)) {
                $this->properties[$property] = $value;
            }
        }
    }

    /**
     * @param string $property
     * @param EntityManager $em
     * @return Entity
     */
    public function get(string $property, EntityManager $em)
    {
        $table = $this->schema->getTableDescription($this->label);


        if (array_key_exists(Singularize::singularize($property) . "_id", $table)) {
            $description = $table[Singularize::singularize($property) . "_id"];

            if (array_key_exists('relation', $description)) {
                switch ($description['relation']) {
                    case 'oneToOne':
                        return $em->find($description['entity'], [
                            $this->label . '_id' => $this->get('id', $em)
                        ]);
                        break;
                    case 'oneToMany':
                        break;
                    case 'manyToOne':
                        break;
                    case 'manyToMany':
                        break;
                    default:
                        return $this->properties[Singularize::singularize($property) . "_id"];
                }
            } else {
                return $this->properties[Singularize::singularize($property) . "_id"];
            }
        } else {
            return $this->properties[$property];
        }
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return strtolower($this->label);
    }
}
