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
use Idmedia\TranslationstudioBundle\Services\ApiAuthorizationService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Pimcore\Tool;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Locale;
use Symfony\Component\Routing\Annotation\Route;
use Idmedia\TranslationstudioBundle\Services\LanguagesService;
use Pimcore\Model\Tool\SettingsStore;
use Symfony\Component\HttpFoundation\Request;


class LanguagesController extends AbstractController
{
    private ApiAuthorizationService $apiAuthorizationService;
    public function __construct(ApiAuthorizationService $apiAuthorizationService)
    {
        $this->apiAuthorizationService = $apiAuthorizationService;
    }

    #[Route('/get-ts-languages', methods: ['GET'])]
    public function getLanguages(LanguagesService $languagesService): JsonResponse
    {
        $license = SettingsStore::get('license')?SettingsStore::get('license')->getData():'';
        $response = $languagesService->sendLanguageRequest($license);
        return new JsonResponse($response, 200);
    }

    #[Route('/translationstudio/languages', methods: ['GET'])]
    public function getCmsLanguages(Request $request): JsonResponse
    {
        if (($response = $this->apiAuthorizationService->authorize($request))->getStatusCode() !== 204) {
            return $response;
        }

        $languages = Tool::getValidLanguages();
        $languageNames = [];

        foreach ($languages as $lang) {
            $languageNames[$lang] = Locale::getDisplayName($lang, $lang); // Name in der jeweiligen Sprache
        }
        return new JsonResponse(json_encode($languageNames, JSON_UNESCAPED_UNICODE), 200, [], true);
    }
}
