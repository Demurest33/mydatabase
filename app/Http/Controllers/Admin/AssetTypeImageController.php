<?php

namespace App\Http\Controllers\Admin;

use App\Cache\CacheKeys;
use App\Http\Controllers\Controller;
use App\Models\AssetTypeImage;
use App\Services\Neo4jService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AssetTypeImageController extends Controller
{
    public function index(Neo4jService $neo4j)
    {
        $types = Cache::remember(CacheKeys::ASSETS_CATEGORIES, CacheKeys::TTL_LONG, function () use ($neo4j) {
            $cats = [];
            foreach ($neo4j->client()->run('MATCH (a:Asset) WHERE a.type IS NOT NULL RETURN a.type as type, count(a) as count ORDER BY count DESC') as $record) {
                $cats[] = ['name' => $record->get('type'), 'count' => $record->get('count')];
            }
            return $cats;
        });

        $images = AssetTypeImage::all()->keyBy('type');

        return view('admin.asset-type-images.index', compact('types', 'images'));
    }

    public function upsert(Request $request, string $type)
    {
        $request->validate([
            'image' => 'required|image|max:4096',
        ]);

        $record = AssetTypeImage::firstOrNew(['type' => $type]);

        if ($record->image) {
            Storage::disk('public')->delete('type-images/' . $record->image);
        }

        $filename = $request->file('image')->store('type-images', 'public');
        $record->image = basename($filename);
        $record->save();

        Cache::forget(CacheKeys::ASSET_TYPE_IMAGES);

        return back()->with('success', "Imagen de «{$type}» actualizada.");
    }

    public function destroy(string $type)
    {
        $record = AssetTypeImage::find($type);
        if ($record) {
            if ($record->image) {
                Storage::disk('public')->delete('type-images/' . $record->image);
            }
            $record->delete();
            Cache::forget(CacheKeys::ASSET_TYPE_IMAGES);
        }

        return back()->with('success', "Imagen de «{$type}» eliminada.");
    }
}
