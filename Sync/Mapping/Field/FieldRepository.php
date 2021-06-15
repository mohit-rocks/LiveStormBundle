<?php

declare(strict_types=1);

namespace MauticPlugin\LiveStormBundle\Sync\Mapping\Field;

use Symfony\Component\Yaml\Yaml;

class FieldRepository
{
    /**
     * Used by the sync engine so that it does not have to fetch the fields live
     * with each object sync.
     *
     * @return Field[]
     */
    public function getFields(string $objectName): array
    {
        // Get the fields from the yml file based on object.
        $fields = Yaml::parse(file_get_contents(__DIR__.'/../FieldMappings/ContactFieldMapping.yaml'));

        return $this->hydrateFieldObjects($fields);
    }

    /**
     * @return MappedFieldInfo[]
     */
    public function getAllFieldsForMapping(string $objectName): array
    {
        $fieldObjects = $this->getFields($objectName);

        $allFields = [];
        foreach ($fieldObjects as $field) {
            // Fields must have the name as the key
            $allFields[$field->getName()] = new MappedFieldInfo($field);
        }

        return $allFields;
    }

    /**
     * @return Field[]
     */
    private function hydrateFieldObjects(array $fields): array
    {
        $fieldObjects = [];
        foreach ($fields as $field) {
            $fieldObjects[$field['name']] = new Field($field);
        }

        return $fieldObjects;
    }
}
