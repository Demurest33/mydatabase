<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SteamHttp;
use Illuminate\Http\Request;
use DOMDocument;
use DOMXPath;

class SteamScraperController extends Controller
{
    public function preview(Request $request)
    {
        $input = trim($request->input('url', ''));

        if (is_numeric($input)) {
            $url = "https://steamcommunity.com/sharedfiles/filedetails/?id={$input}";
        } elseif (preg_match('/id=(\d+)/i', $input)) {
            $url = $input;
        } else {
            return response()->json(['error' => 'Provide a Steam Workshop URL or numeric ID.'], 422);
        }

        $response = SteamHttp::client()->get($url);

        if (!$response->ok()) {
            return response()->json(['error' => "Steam returned HTTP {$response->status()}"], 502);
        }

        $html = $response->body();

        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);

        // Detect Steam error pages via div#message
        $messageDiv = $dom->getElementById('message');
        if ($messageDiv) {
            $h3 = $xpath->query('.//h3', $messageDiv);
            $errorText = $h3->length > 0
                ? trim($h3->item(0)->textContent)
                : trim($messageDiv->textContent);
            return response()->json(['error' => $errorText ?: 'Error de Steam'], 422);
        }

        // Fallback: generic error h2
        $errorH2 = $xpath->query('//h2[contains(normalize-space(.),"Error")]');
        if ($errorH2->length > 0) {
            return response()->json(['error' => 'Requiere inicio de sesión en Steam.'], 422);
        }

        $titleNodes = $xpath->query('//*[contains(@class,"workshopItemTitle")]');
        $title      = $titleNodes->length > 0 ? trim($titleNodes->item(0)->textContent) : null;

        $imgNode = $dom->getElementById('previewImage')
            ?? $dom->getElementById('previewImageMain');

        if (!$imgNode) {
            foreach (['workshopItemPreviewImageEnlargeable', 'workshopItemPreviewImageMain'] as $cls) {
                $nodes = $xpath->query("//*[contains(@class,'{$cls}')]");
                if ($nodes->length > 0 && $nodes->item(0) instanceof \DOMElement) {
                    $imgNode = $nodes->item(0);
                    break;
                }
            }
        }

        $imageUrl = $imgNode instanceof \DOMElement ? $imgNode->getAttribute('src') : null;

        if (!$title && !$imageUrl) {
            return response()->json(['error' => 'No se pudo parsear la página de Steam.'], 422);
        }

        return response()->json(['title' => $title, 'imageUrl' => $imageUrl]);
    }
}
