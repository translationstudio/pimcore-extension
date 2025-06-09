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
use \Pimcore\Controller\FrontendController;
use Pimcore\Model\Tool\SettingsStore;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;
use Pimcore\Model\DataObject;

class RequestTranslationService extends FrontendController
{
    private Security $security;
    private RequestStack $requestStack;

    public function __construct(Security $security, RequestStack $requestStack)
    {
        $this->security = $security;
        $this->requestStack = $requestStack;
    }

    private function getUrl()
    {
        return 'https://pimcore.translationstudio.tech/translate';
    }

    private function createPayload($request)
    {
        $objectId = $request->request->get('id');
        $object = DataObject::getById($objectId);
        
        if (!$objectId) {
            return null;
        }

        $machine = $request->request->get('machine') == 'true';
        $isUrgent = $machine || $request->request->get('isUrgent') == 'true';
        $language = json_decode($request->request->get('language'), true);
        $notification = $request->request->get('notification') === 'true';
        $name =  $object->getKey();
        $className = $object->getClassName();
        $email = $machine || !$notification ? '':  $this->requestStack->getSession()->get('userEmail');
        $payload = [
            'email' => $email, 
            'project-name' => $className,
            'duedate' => time(),
            'urgent' => $isUrgent,
            'translations' => $language,
            'entry' => [
                'uid' => $objectId,
                'name' => $name
            ]
        ];

        return $payload;
    }

    public function requestTranslation($request)
    {
        $payload = $this->createPayload($request);
        if ($payload == null) {
            error_log("Cannot create payload");
            return new JsonResponse(
                ['message' => 'Invalid payload'], 400
            );
        }

        $license = SettingsStore::get('tslicense') ? SettingsStore::get('tslicense')->getData() : null;
        if (!$license) {
            return new JsonResponse(
                ['message' => 'License missing'], 500
            );
        }

        $ch = curl_init($this->getUrl());

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $license,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);

        if ($response === false) {
            $errorMessage = curl_error($ch);
            $errorCode = curl_errno($ch);
            curl_close($ch);
            error_log("cURL Fehler: Code $errorCode - $errorMessage");
            return new JsonResponse(
                ['message' => "Could not perform request: Code $errorCode - $errorMessage"], 500
            );
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return new JsonResponse(null, 204);
    }
}