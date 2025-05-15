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
use Pimcore\Model\DataObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Pimcore\Model\DataObject\ClassDefinition;

class ImportService
{
    private ApiAuthorizationService $apiAuthorizationService;

    public function __construct(ApiAuthorizationService $apiAuthorizationService)
    {
        $this->apiAuthorizationService = $apiAuthorizationService;
    }
    public function createImportRequest(Request $request): JsonResponse
    {   
        if (($this->apiAuthorizationService->authorize($request))->getStatusCode() !== 204) {
            return new JsonResponse(
                ['message' => 'Invalid Key'], 400
            );
        }

        $data = json_decode($request->getContent(), true);

        $targetLanguage = $data['target'];
        $sourceLanguage = $data['source'];
        $object = DataObject::getById($data['element']);
        if (!$object) {
            return new JsonResponse([
                'message' => 'Object not found'
            ], 410);
        }
        if (!method_exists($object, 'getLocalizedfields')) {
            return new JsonResponse([
                'message' => 'No Localized Fields',
            ], 500);
        }
        $localizedFields = $object->getLocalizedfields();
        foreach ($data['document'] as $entry) {
            foreach ($entry['fields'] as $field) {
                if (count($field['translatableValue']) === 1){
                    /* markFieldAsDirty -> true = overwrite, false = write only when empty */
                    $localizedFields->setLocalizedValue($field['field'], $field['translatableValue'][0], $targetLanguage, true);
                }
            }
        } 
        $this->transferNonTranslatables($object, $sourceLanguage, $targetLanguage);
        $object->setLocalizedfields($localizedFields);
        $object->save();
        return new JsonResponse(
            null, 204
        );
    }

    private function transferNonTranslatables($object, $sourceLanguage, $targetLanguage)
    {
        $classDefinition = ClassDefinition::getById($object->getClassId());
        $localizedFields = $object->getLocalizedfields();

        foreach ($classDefinition->getFieldDefinition("localizedfields")->getChildren() as $field) {
            $fieldName = $field->getName();
            if ($field instanceof \Pimcore\Model\DataObject\ClassDefinition\Data && $field->getFieldtype() !== "urlSlug") {
                $sourceValue = $localizedFields->getLocalizedValue($fieldName, $sourceLanguage);
                $targetValue = $localizedFields->getLocalizedValue($fieldName, $targetLanguage);
                if (empty($targetValue) && !empty($sourceValue)) {
                    $localizedFields->setLocalizedValue($fieldName, $sourceValue, $targetLanguage, true);
                }
            }
        }
    }
}