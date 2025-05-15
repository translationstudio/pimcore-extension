<?php
namespace Tests\App\Bundle;

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
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Idmedia\TranslationstudioBundle\Controller\ImportExportController;
use Idmedia\TranslationstudioBundle\Services\ImportService;
use Idmedia\TranslationstudioBundle\Services\ExportService;
use Idmedia\TranslationstudioBundle\Services\ApiAuthorizationService;
class ImportExportControllerTest extends TestCase
{
    private ImportExportController $controller;
    private $importService;
    private $exportService;

    protected function setUp(): void
    {
        $this->importService = $this->createMock(ImportService::class);
        $this->exportService = $this->createMock(ExportService::class);
        $this->controller = new ImportExportController($this->importService, $this->exportService);
    }

    public function testImportRequest()
    {
        $request = new Request([], [], [], [], [], [], json_encode(['document' => []]));
        $this->importService->method('createImportRequest')->willReturn(new JsonResponse(['message' => '200'], 200));
        
        $response = $this->controller->importRequest($request);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
    
    public function testExportRequest()
    {
        $request = new Request([], [], [], [], [], [], json_encode(['project-uid' => '123', 'source' => 'en', 'target' => 'de', 'elements' => [1]]));
        $this->exportService->method('createExportRequest')->willReturn(new JsonResponse(['message' => 'Export erfolgreich gestartet'], 200));
        
        $response = $this->controller->exportRequest($request);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
