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
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Pimcore\Model\Tool\SettingsStore;

class LicenseControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testSaveLicense(): void
    {
        // Mock für SettingsStore
        $mockSettingsStore = $this->createMock(SettingsStore::class);
        $mockSettingsStore->expects($this->once())
                          ->method('set')
                          ->with('license', 'test-license');

        // Simuliere eine POST-Anfrage, um die Lizenz zu speichern
        $this->client->request('POST', '/save-license', ['license' => 'test-license']);

        // Überprüfen, ob die Antwort den erwarteten Erfolg enthält
        $this->assertResponseIsSuccessful();
        $this->assertJson(json_encode(['success' => 'Lizenz gespeichert']));
    }

    public function testGetLicense(): void
    {
        // Mock für SettingsStore
        $mockSettingsStore = $this->createMock(SettingsStore::class);
        $mockSettingsStore->expects($this->once())
                          ->method('get')
                          ->with('license')
                          ->willReturn('test-license');

        // Simuliere eine GET-Anfrage, um die Lizenz abzurufen
        $this->client->request('GET', '/get-license');

        // Überprüfen, ob die Antwort den erwarteten Lizenzwert enthält
        $this->assertResponseIsSuccessful();
        $this->assertJson(json_encode(['license' => 'test-license']));
    }

    public function testCreateApi(): void
    {
        // Mock für SettingsStore
        $mockSettingsStore = $this->createMock(SettingsStore::class);
        $mockSettingsStore->expects($this->once())
                          ->method('set')
                          ->with('api', $this->isType('string'));

        // Simuliere eine POST-Anfrage, um den API-Schlüssel zu erstellen
        $this->client->request('POST', '/create-api');

        // Überprüfen, ob die Antwort den erwarteten Erfolg enthält
        $this->assertResponseIsSuccessful();
        $this->assertJson(json_encode(['success' => 'API gespeichert']));

    }

    public function testGetApi(): void
    {
        // Mock für SettingsStore
        $mockSettingsStore = $this->createMock(SettingsStore::class);
        $mockSettingsStore->expects($this->once())
                          ->method('get')
                          ->with('api')
                          ->willReturn('test-api-key');

        // Simuliere eine GET-Anfrage, um den API-Schlüssel abzurufen
        $this->client->request('GET', '/get-api');

        // Überprüfen, ob die Antwort den erwarteten API-Schlüssel enthält
        $this->assertResponseIsSuccessful();
        $this->assertJson(json_encode(['api' => 'test-api-key']));

    }
}
