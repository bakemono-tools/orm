<?php
/**
 * Created by PhpStorm.
 * User: melvin
 * Date: 06/10/17
 * Time: 11:13
 */

namespace Orm;


class Form
{
    /**
     * Contient les champs du formulaire avec leur identifiant
     *
     * @var array
     */
    private $fields = [];

    /**
     * Contient le nom de l'entité dont le formulaire est dépendant
     *
     * @var string
     */
    private $entityName;

    /**
     * Contient le schema global
     *
     * @var Schema
     */
    private $globalSchema;

    public function __construct(Schema $schema, string $entityName)
    {
        $this->globalSchema = $schema;
        $this->entityName = $entityName;
    }

    /**
     * Ajoute un champ au formulaire
     *
     * @param string $field
     * @param string $id
     */
    public function add(string $field, string $id)
    {
        $this->fields[$field] = $id;
    }

    /**
     * Récupère l'identifiant d'un champ
     *
     * @param string $id
     * @return string
     */
    public function getFieldId(string $id) : string
    {
        return $this->fields[$id];
    }

    /**
     * Retourne l'entité correspondante remplit avec les valeurs saisi par les utilisateurs
     *
     * @param array $values
     * @return null|Entity
     */
    public function getFilledEntity(array $values)
    {
        /**
         * Si des valeurs ont été saisies on retourne l'entité
         */
        if (!empty($values)) {
            return new Entity($this->entityName, $this->globalSchema, $values);
        } else {
            return null;
        }
    }
}