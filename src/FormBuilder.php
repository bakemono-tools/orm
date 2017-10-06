<?php

namespace Orm;


class FormBuilder
{
    /**
     * Contient le schema global de l'application
     *
     * @var Schema
     */
    private $globalSchema;

    /**
     * FormBuilder constructor.
     *
     * @param Schema $globalSchema
     */
    public function __construct(Schema $globalSchema)
    {
        $this->globalSchema = $globalSchema;
    }

    /**
     * Retourne le formulaire correspondant à l'entité
     *
     * @param string $entityName
     * @return Form
     */
    public function getForm(string $entityName) : Form
    {
        $form = new Form($this->globalSchema, $entityName);
        $tableDescription = $this->globalSchema->getTableDescription($entityName);

        /**
         * Ne sers à rien pour l'instant mais si la sémantique des identifiants des champs venaient à changer un jour
         * il n'y aura pas besoin de changer les vues
         * On assure ainsi la compatibilité
         */
        foreach ($tableDescription as $field => $description) {
            $form->add($field, $field);
        }

        return $form;
    }
}