<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

        $response = Http::withHeaders([
            'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Accept-Language' => 'en-US,en;q=0.9',
        ])->timeout(10)->get($url);

        if (!$response->ok()) {
            return response()->json(['error' => "Steam returned HTTP {$response->status()}"], 502);
        }

        $html = $response->body();

        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);

        $titleNodes = $xpath->query('//*[contains(@class,"workshopItemTitle")]');
        $title      = $titleNodes->length > 0 ? trim($titleNodes->item(0)->textContent) : null;

        $imgNode  = $dom->getElementById('previewImage');
        $imageUrl = $imgNode ? $imgNode->getAttribute('src') : null;

        if (!$title && !$imageUrl) {
            return response()->json(['error' => 'Could not parse Steam page. It may require login.'], 422);
        }

        return response()->json(['title' => $title, 'imageUrl' => $imageUrl]);
    }
}
