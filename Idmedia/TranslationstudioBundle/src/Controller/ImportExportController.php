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
use Idmedia\TranslationstudioBundle\Services\ImportService;
use Idmedia\TranslationstudioBundle\Services\ExportService;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
class ImportExportController
{
    private ImportService $importService;
    private ExportService $exportService;

    public function __construct(ImportService $importService, ExportService $exportService)
    {
        $this->importService = $importService;
        $this->exportService = $exportService;
    }
    #[Route('/translationstudio/import', methods: ['POST'])]
    public function importRequest(Request $request): JsonResponse{
        return $this->importService->createImportRequest($request);
    }
    #[Route('/translationstudio/export', methods: ['POST'])]
    public function exportRequest(Request $request): JsonResponse{
        return $this->exportService->createExportRequest($request);
    }
}

