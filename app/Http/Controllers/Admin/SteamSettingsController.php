<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class SteamSettingsController extends Controller
{
    public function index()
    {
        return view('admin.steam-settings', [
            'steamLoginSecure' => AppSetting::get('steam_login_secure', ''),
            'sessionId'        => AppSetting::get('steam_session_id', ''),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'steam_login_secure' => 'nullable|string|max:2000',
            'session_id'         => 'nullable|string|max:200',
        ]);

        // URL-decode in case user copied the value with %7C%7C etc.
        $newLogin   = urldecode(trim($request->input('steam_login_secure', '')));
        $newSession = urldecode(trim($request->input('session_id', '')));

        if ($newLogin)   AppSetting::set('steam_login_secure', $newLogin);
        if ($newSession) AppSetting::set('steam_session_id',   $newSession);

        return back()->with('success', 'Cookies guardadas correctamente.');
    }

    public function clear()
    {
        AppSetting::set('steam_login_secure', '');
        AppSetting::set('steam_session_id',   '');

        return back()->with('cleared', true);
    }
}
