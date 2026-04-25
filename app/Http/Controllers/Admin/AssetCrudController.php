<?php

namespace App\Http\Controllers\Admin;

use App\Cache\CacheKeys;
use App\Http\Controllers\Controller;
use App\Services\Neo4jService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class AssetCrudController extends Controller
{
    protected Neo4jService $neo4j;

    public function __construct(Neo4jService $neo4j)
    {
        $this->neo4j = $neo4j;
    }

    public function index(Request $request)
    {
        $type = $request->input('type');

        $cacheKey = 'admin.assets.index.' . md5($type ?? '');
        $rows = Cache::remember($cacheKey, CacheKeys::TTL_SHORT, function () use ($type) {
            $client = $this->neo4j->client();

            $where  = $type ? 'WHERE a.type = $type' : '';
            $params = $type ? ['type' => $type] : [];

            $result = $client->run("
                MATCH (a:Asset)
                $where
                OPTIONAL MATCH (c:Character)-[:HAS_ASSET]->(a)
                OPTIONAL MATCH (m:Media)-[:HAS_ASSET]->(a)
                WITH a, count(DISTINCT c) as charCount, count(DISTINCT m) as mediaCount
                RETURN a, charCount, mediaCount
                ORDER BY a.createdAt DESC
                LIMIT 200
            ", $params);

            $rows = [];
            foreach ($result as $rec) {
                $p = $rec->get('a')->getProperties()->toArray();
                $p['charCount']  = (int) $rec->get('charCount');
                $p['mediaCount'] = (int) $rec->get('mediaCount');
                $p['fileUrl']    = isset($p['filename'])
                    ? asset('storage/assets/' . $p['filename'])
                    : ($p['url'] ?? null);
                $p['coverUrl']   = isset($p['coverFilename'])
                    ? asset('storage/assets/covers/' . $p['coverFilename'])
                    : null;
                if (isset($p['createdAt']) && is_object($p['createdAt'])) {
                    $p['createdAt'] = method_exists($p['createdAt'], 'toDateTime')
                        ? $p['createdAt']->toDateTime()->format('c')
                        : (string) $p['createdAt'];
                }
                $rows[] = $p;
            }
            return $rows;
        });

        $types = ['ANIME','MANGA','LIGHT NOVEL','DOUJIN','WALLPAPER ENGINE','IMG','MUSIC','GIF','AMV','VIDEO'];

        return view('admin.assets.index', compact('rows', 'types', 'type'));
    }

    public function edit($id)
    {
        try {
            $client = $this->neo4j->client();
            $result = $client->run('MATCH (a:Asset {id: $id}) RETURN a', ['id' => $id]);

            if ($result->isEmpty()) {
                return redirect()->route('admin.assets.index')->with('error', 'Asset not found.');
            }

            $asset = $result->first()->get('a')->getProperties()->toArray();
            $asset['coverUrl'] = isset($asset['coverFilename'])
                ? asset('storage/assets/covers/' . $asset['coverFilename'])
                : null;
            $asset['fileUrl'] = isset($asset['filename'])
                ? asset('storage/assets/' . $asset['filename'])
                : ($asset['url'] ?? null);

            $types = ['ANIME','MANGA','LIGHT NOVEL','DOUJIN','WALLPAPER ENGINE','IMG','MUSIC','GIF','AMV','VIDEO'];

            return view('admin.assets.edit', compact('asset', 'types'));
        } catch (Exception $e) {
            return redirect()->route('admin.assets.index')->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title'       => 'nullable|string|max:255',
            'type'        => 'required|string',
            'cover_image' => 'nullable|image|max:10240',
        ]);

        try {
            $client = $this->neo4j->client();

            $result = $client->run('MATCH (a:Asset {id: $id}) RETURN a', ['id' => $id]);
            if ($result->isEmpty()) {
                return redirect()->route('admin.assets.index')->with('error', 'Asset not found.');
            }

            $existing = $result->first()->get('a')->getProperties()->toArray();

            $coverFilename = $existing['coverFilename'] ?? null;

            if ($request->hasFile('cover_image')) {
                // Delete old cover if local
                if ($coverFilename) {
                    Storage::disk('public')->delete('assets/covers/' . $coverFilename);
                }

                $img = $request->file('cover_image');
                $coverFilename = time() . '_cover_' . Str::slug(pathinfo($img->getClientOriginalName(), PATHINFO_FILENAME))
                    . '.' . $img->getClientOriginalExtension();
                $img->storeAs('assets/covers', $coverFilename, 'public');
            }

            $client->run('
                MATCH (a:Asset {id: $id})
                SET a.title         = $title,
                    a.type          = $type,
                    a.coverFilename = $coverFilename
            ', [
                'id'            => $id,
                'title'         => $request->input('title'),
                'type'          => $request->input('type'),
                'coverFilename' => $coverFilename,
            ]);

            CacheKeys::forget($this->invalidationKeys($id));

            return redirect()->route('admin.assets.index')->with('success', 'Asset updated.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $client = $this->neo4j->client();
            $result = $client->run('MATCH (a:Asset {id: $id}) RETURN a', ['id' => $id]);

            if (!$result->isEmpty()) {
                $props = $result->first()->get('a')->getProperties()->toArray();

                if (!empty($props['filename'])) {
                    Storage::disk('public')->delete('assets/' . $props['filename']);
                }
                if (!empty($props['coverFilename'])) {
                    Storage::disk('public')->delete('assets/covers/' . $props['coverFilename']);
                }

                $client->run('MATCH (a:Asset {id: $id}) DETACH DELETE a', ['id' => $id]);
            }

            CacheKeys::forget($this->invalidationKeys($id));

            return redirect()->route('admin.assets.index')->with('success', 'Asset deleted.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function invalidationKeys(string $assetId): array
    {
        return [
            CacheKeys::ASSETS_CATEGORIES,
            CacheKeys::FRANCHISES_CATALOGUE,
            CacheKeys::CHARACTERS_GROUPED,
            CacheKeys::ADMIN_CHARACTERS_GROUPED,
            // Clear all type-filtered index caches
            'admin.assets.index.' . md5(''),
        ];
    }
}
