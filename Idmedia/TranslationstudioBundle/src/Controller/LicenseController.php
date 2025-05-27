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
    #[Route('/translationstudio/pimcore/license', methods: ['POST'])]
    public function saveLicense(Request $request): JsonResponse
    {
        $license = $request->request->get('license')?:$request->request->get('license');
        SettingsStore::set('tslicense', $license);
        return new JsonResponse(204);
    }
    
    #[Route('/translationstudio/pimcore/license', methods: ['GET'])]
    public function getLicense(): JsonResponse
    {
        $licenseSetting = SettingsStore::get('tslicense');
        $license = $licenseSetting ? $licenseSetting->getData() : null;
        if (!$license) {
            return new JsonResponse(['license' => ''], 200);
        }
        return new JsonResponse(['license'=> $license], 200);
    }

    #[Route('/translationstudio/pimcore/apikey', methods: ['POST'])]
    public function createApi(): JsonResponse
    {
        $api = bin2hex(random_bytes(32));
        SettingsStore::set('tsapi', $api);
        return new JsonResponse(['success' => $api], 200);
    }

    #[Route('/translationstudio/pimcore/apikey', methods: ['GET'])]
    public function getApi(): JsonResponse
    {
        $apiSetting = SettingsStore::get('tsapi');
        $api = $apiSetting ? $apiSetting->getData() : null;
        if (!$api) {
            $api = bin2hex(random_bytes(32));
            SettingsStore::set('tsapi', $api);
        }
        return new JsonResponse(['api'=> $api], 200);
    }

    #[Route("/translationstudio/pimcore/get-user-info", methods: ["GET"])]
    public function getUserInfo(RequestStack $requestStack): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            if (!$user) {
                return new JsonResponse(["message" => "Keine email gefunden"], 500);
            }

            $pimcoreUser = User::getById($user->getId());
            if ($pimcoreUser)
            {
                $session = $requestStack->getSession();
                $session->set('userEmail', $pimcoreUser->getEmail());
            }

            return new JsonResponse([], 204);
        }
        catch(Exception $exIgnore) 
        {
            return new JsonResponse([], 500);
        }
    }
}
