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

    public function requestTranslation($request)
    {
        $objectId = $request->request->get('id');
        $object = DataObject::getById($objectId);
        
        if (!$objectId) {
            return $this->json(["success" => false, "message" => "Object ID is missing"], 400);
        }
        $machine = $request->request->get('machine') === 'true';
        $isUrgent = $machine || $request->request->get('isUrgent') === 'true';
        $language = json_decode($request->request->get('language'), true);
        $license = SettingsStore::get('license') ? SettingsStore::get('license')->getData() : null;
        $notification = $request->request->get('notification') === 'true';
        $name =  $object->getKey();
        $className = $object->getClassName();
        if (!$license) {
            return $this->json(["success" => false, "message" => "Token is missing"], 400);
        }
        $email = $machine || !$notification ? '':  $this->requestStack->getSession()->get('userEmail');
        $url = 'https://pimcore.translationstudio.tech/translate';
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
        $ch = curl_init($url);

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
            return ['message' => $errorMessage];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($httpCode !== 204) {
            return ['error' => "Fehler: HTTP Statuscode $httpCode"];
        }

        return $httpCode;
    }
}