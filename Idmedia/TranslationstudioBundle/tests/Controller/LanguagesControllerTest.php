<?php

namespace Tests\AppBundle\Controller;

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
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Pimcore\Tool;
use PHPUnit\Framework\MockObject\MockObject;
use Idmedia\TranslationstudioBundle\Controller\LanguagesController;
use Idmedia\TranslationstudioBundle\Services\LanguagesService;
use Idmedia\TranslationstudioBundle\Services\ApiAuthorizationService;
class LanguagesControllerTest extends WebTestCase
{
    private ApiAuthorizationService|MockObject $apiAuthorizationService;
    private LanguagesService|MockObject $languagesService;
    private LanguagesController $controller;

    protected function setUp(): void
    {
        $this->apiAuthorizationService = $this->createMock(ApiAuthorizationService::class);
        $this->languagesService = $this->createMock(LanguagesService::class);
        $this->controller = new LanguagesController($this->apiAuthorizationService);
    }

    public function testGetCmsLanguagesUnauthorized()
    {
        $request = new Request();
        $this->apiAuthorizationService->method('authorize')->willReturn(new JsonResponse([], 403));
        
        $response = $this->controller->getCmsLanguages($request);
        
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testGetCmsLanguagesSuccess()
    {
        $request = new Request();
        $this->apiAuthorizationService->method('authorize')->willReturn(new JsonResponse([], 204));
        
        $mockTool = $this->createMock(Tool::class);
        
        $mockTool->method('getValidLanguages')
                 ->willReturn(['en', 'de']);

        $response = $this->controller->getCmsLanguages($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    public function testGetLanguages()
    {
        $this->languagesService->method('sendLanguageRequest')->willReturn(['en' => 'English', 'de' => 'Deutsch']);
        
        $response = $this->controller->getLanguages($this->languagesService);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertStringContainsString('English', $response->getContent());
    }
}
