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
class LanguagesService
{
    public function sendLanguageRequest(string $license)
    {
        $url = 'https://pimcore.translationstudio.tech/mappings';

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $license
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $errorMessage = curl_error($ch);
            $errorCode = curl_errno($ch);
            error_log("cURL Fehler: Code $errorCode - $errorMessage");
            return ['error' => $errorMessage];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($httpCode !== 200) {
            return ['error' => "Fehler: HTTP Statuscode $httpCode"];
        }

        return json_decode($response, true);
    }
}
