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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use \Pimcore\Controller\FrontendController;
use Idmedia\TranslationstudioBundle\Services\RequestTranslationService;
use Pimcore\Model\Tool\SettingsStore;
use Symfony\Component\Security\Core\Security;
class RequestTranslationController 
{
    private RequestTranslationService $requestTranslationService;

    public function __construct(RequestTranslationService $requestTranslationService)
    {
        $this->requestTranslationService = $requestTranslationService;
    }

    #[Route('/request-translation', methods: ['POST'])]
    public function sendTranslationRequest(Request $request): JsonResponse
    {
        $response = $this->requestTranslationService->requestTranslation($request);
   
        return new JsonResponse([
            'message' => $response
        ], 200);
    
    }
}
