<?php
namespace Idmedia\TranslationstudioBundle\Controller;
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
use Pimcore\Model\Tool\SettingsStore;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;

use Pimcore\Model\User;

class LicenseController extends AbstractController
{
    #[Route('/save-license', methods: ['POST'])]
    public function saveLicense(Request $request): JsonResponse
    {
        $license = $request->request->get('license')?:$request->request->get('license');
        SettingsStore::set('license', $license);
        return new JsonResponse(['success' => 'Lizenz gespeichert'], 200);
    }
    
    #[Route('/get-license', methods: ['GET'])]
    public function getLicense(): JsonResponse
    {
        $licenseSetting = SettingsStore::get('license');
        $license = $licenseSetting ? $licenseSetting->getData() : null;
        if (!$license) {
            return new JsonResponse(['error' => 'No License saved yet'], 404);
        }
        return new JsonResponse(['license'=> $license], 200);
    }

    #[Route('/create-api', methods: ['POST'])]
    public function createApi(): JsonResponse
    {
        $api = bin2hex(random_bytes(32));
        SettingsStore::set('api', $api);
        return new JsonResponse(['success' => 'Erfolgreich'], 200);
    }

    #[Route('/get-api', methods: ['GET'])]
    public function getApi(): JsonResponse
    {
        $apiSetting = SettingsStore::get('api');
        $api = $apiSetting ? $apiSetting->getData() : null;
        if (!$api) {
            return new JsonResponse(['error' => 'No API available yet'], 404);
        }
        return new JsonResponse(['api'=> $api], 200);
    }

    #[Route("/admin/get-user-info", methods: ["POST"])]
    public function getUserInfo(RequestStack $requestStack): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $pimcoreUser = User::getById($user->getId());
        if (!$user) {
            return new JsonResponse(["message" => "Keine email gefunden"], 404);
        }
        $session = $requestStack->getSession();
        $session->set('userEmail', $pimcoreUser->getEmail());
        return new JsonResponse([], 204);
    }
}
