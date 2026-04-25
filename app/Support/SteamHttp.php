<?php

namespace App\Support;

use App\Models\AppSetting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class SteamHttp
{
    public static function client(): PendingRequest
    {
        $client = Http::withHeaders([
            'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Accept-Language' => 'en-US,en;q=0.9',
        ])->timeout(15);

        $loginSecure = AppSetting::get('steam_login_secure');
        $sessionId   = AppSetting::get('steam_session_id');

        if ($loginSecure || $sessionId) {
            $cookies = [];
            if ($loginSecure) $cookies['steamLoginSecure'] = $loginSecure;
            if ($sessionId)   $cookies['sessionid']        = $sessionId;
            $client = $client->withCookies($cookies, 'steamcommunity.com');
        }

        return $client;
    }
}
