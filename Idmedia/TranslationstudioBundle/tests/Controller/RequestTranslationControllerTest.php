<?php
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

class RequestTranslationControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testRequestTranslation(): void
    {
        $this->client->request('POST', '/request-translation', [
            'id' => 'test-id',
            'machine' => 'true',
            'language' => 'de',
            'isUrgent' => 'false',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJson(json_encode(['message' => 'Export erfolgreich gestartet']));
    }
}