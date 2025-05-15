<?php
namespace Idmedia\TranslationstudioBundle\Services;
/*
Pimcore - translationstudio extension
Copyright (C) 2025 I-D Media GmbH, idmedia.com
 
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.
 
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with this program; if not, see https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\ClassDefinition;

class ExportService
{
    private ApiAuthorizationService $apiAuthorizationService;
    public function __construct(ApiAuthorizationService $apiAuthorizationService)
    {
        $this->apiAuthorizationService = $apiAuthorizationService;
    }

    public function createExportRequest(Request $request): JsonResponse
    {
        if (($this->apiAuthorizationService->authorize($request))->getStatusCode() !== 204) {
            return new JsonResponse(
                ['message' => 'Invalid Key'], 400
            );
        }

        $data = json_decode($request->getContent(), true);
        
        if (!isset( $data['source'], $data['target'], $data['element'])) {
            return new JsonResponse(['message' => 'Fehlende erforderliche Felder'], 500);
        }
        $sourceLanguage = $data['source'];
        $object = DataObject::getById($data['element']);
        if (!$object) {
            return new JsonResponse([
                'message' => 'Object not found'
            ], 500);
        }

        $objectfields = $this->exportLocalizedvalues($object, $sourceLanguage);
        return new JsonResponse([
            'fields' => $objectfields,
        ], 200);
    }

    private function exportLocalizedvalues($object, $sourceLanguage)
    {
        $objectFields = [];
        $classDefinition = ClassDefinition::getById($object->getClassId());
        $localizedFields = $object->getLocalizedfields();
        $localizedFieldDefinition = $classDefinition->getFieldDefinition("localizedfields");
        $textFieldNames = $this->getLocalizedTextFields($localizedFieldDefinition->getChildren());

        foreach ($textFieldNames as $fieldName) {
            $field = $localizedFields->getLocalizedValue($fieldName, $sourceLanguage);

            if ($field !== null) {
                $fieldDefinition = $classDefinition->getFieldDefinition($fieldName);
                $fieldType = $fieldDefinition->getFieldtype();

                $type = 'text';
                $translatableValue = [$field];

                // if ($fieldType === 'link') {
                //     $type = 'html';
                //     $translatableValue = [$field->getHtml()];}
                // if ($fieldType === 'urlSlug' && is_array($field)) {
                //     $translatableValue = [reset($field)->getSlug()];}
                if ($fieldType === 'wysiwyg') {
                    $type = 'html';
                }

                $objectFields[] = [
                    'field' => $fieldName,
                    'type' => $type,
                    'translatableValue' => $translatableValue
                ];
            }
        }
        return $objectFields;
    }

    protected function getLocalizedTextFields($fieldDefinitions)
    {
        $textFields = [];

        foreach ($fieldDefinitions as $field) {
            if ($field instanceof \Pimcore\Model\DataObject\ClassDefinition\Data) {
                if (in_array($field->getFieldtype(), ['input', 'textarea', 'wysiwyg', 'urlSlug', 'table'])) {
                    $textFields[] = $field->getName();
                }
            } elseif ($field instanceof \Pimcore\Model\DataObject\ClassDefinition\Layout) {
                $textFields = array_merge($textFields, $this->getLocalizedTextFields($field->getChildren()));
            }
        }
        return $textFields;
    }
}