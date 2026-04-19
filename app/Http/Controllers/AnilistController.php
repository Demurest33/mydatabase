<?php

namespace App\Http\Controllers;

use App\Services\AnilistService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnilistController extends Controller
{
    protected AnilistService $anilist;

    public function __construct(AnilistService $anilist)
    {
        $this->anilist = $anilist;
    }

    public function index(Request $request)
    {
        $franchiseData = null;
        $search = $request->input('search');

        if ($search) {
            try {
                $franchiseData = $this->anilist->getFullFranchise($search);
            } catch (\Exception $e) {
                // Silently fail or log error
            }
        }

        Log::info($franchiseData);

        return view('anilist.index', compact('franchiseData', 'search'));
    }
}
