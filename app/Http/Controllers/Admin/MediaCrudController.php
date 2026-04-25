<?php

namespace App\Http\Controllers\Admin;

use App\Cache\CacheKeys;
use App\DTOs\MediaDTO;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Neo4jService;
use Illuminate\Support\Facades\Cache;

class MediaCrudController extends Controller
{
    protected Neo4jService $neo4j;

    public function __construct(Neo4jService $neo4j)
    {
        $this->neo4j = $neo4j;
    }

    public function index()
    {
        $raw = Cache::remember(CacheKeys::ADMIN_MEDIA_GROUPED, CacheKeys::TTL_LONG, function () {
            $client = $this->neo4j->client();
            $result = $client->run(
                'MATCH (m:Media)
                 OPTIONAL MATCH (f:Franchise)-[:HAS_ENTRY]->(m)
                 RETURN m, f.name AS franchise
                 ORDER BY franchise ASC, m.format ASC, m.title ASC'
            );

            $grouped = [];
            foreach ($result as $record) {
                $props     = $record->get('m')->getProperties()->toArray();
                $franchise = $record->get('franchise') ?? 'Sin franquicia';
                $format    = $props['format'] ?? 'UNKNOWN';
                $grouped[$franchise][$format][] = $props;
            }
            return $grouped;
        });

        $grouped = [];
        foreach ($raw as $franchise => $formats) {
            foreach ($formats as $format => $items) {
                $grouped[$franchise][$format] = array_map(fn($r) => MediaDTO::from($r), $items);
            }
        }

        return view('admin.media.index', compact('grouped'));
    }

    public function create()
    {
        $franchises = app(\App\Actions\GetFranchiseNamesAction::class)->execute();
        return view('admin.media.create', compact('franchises'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'native' => 'nullable|string|max:255',
            'format' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'coverImage' => 'nullable|url|max:500',
            'start_year' => 'nullable|integer',
            'franchise_name' => 'required|string',
        ]);

        $client = $this->neo4j->client();
        
        // Generate a random ID or unique identifier for the media if none exists (AniList uses integers, we can use a timestamp/random int)
        $id = rand(1000000, 9999999);

        $params = [
            'id' => $id,
            'title' => $request->input('title'),
            'native' => $request->input('native', ''),
            'format' => $request->input('format', 'UNKNOWN'),
            'status' => $request->input('status', 'FINISHED'),
            'description' => $request->input('description', ''),
            'coverImage' => $request->input('coverImage', ''),
            'start_year' => (int) $request->input('start_year', date('Y')),
            'franchise_name' => $request->input('franchise_name')
        ];

        $query = '
            MATCH (f:Franchise {name: $franchise_name})
            CREATE (m:Media {
                id: $id,
                title: $title,
                native: $native,
                format: $format,
                status: $status,
                description: $description,
                coverImage: $coverImage,
                start_year: $start_year
            })
            CREATE (f)-[:HAS_ENTRY]->(m)
        ';

        $client->run($query, $params);
        CacheKeys::forget(CacheKeys::onMediaChange($params['franchise_name']));

        return redirect()->route('admin.media.index')->with('success', 'Media created successfully.');
    }

    public function edit($id)
    {
        $client = $this->neo4j->client();
        $result = $client->run(
            'MATCH (m:Media {id: $id})
             OPTIONAL MATCH (f:Franchise)-[:HAS_ENTRY]->(m)
             RETURN m, f.name AS franchise',
            ['id' => (int) $id]
        );

        $record = $result->first();
        if (!$record) {
            return redirect()->route('admin.media.index')->with('error', 'Media not found.');
        }

        $media      = $record->get('m')->getProperties()->toArray();
        $franchise  = $record->get('franchise');
        $franchises = app(\App\Actions\GetFranchiseNamesAction::class)->execute();

        return view('admin.media.edit', compact('media', 'franchise', 'franchises'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title'          => 'required|string|max:255',
            'native'         => 'nullable|string|max:255',
            'format'         => 'nullable|string|max:50',
            'status'         => 'nullable|string|max:50',
            'description'    => 'nullable|string',
            'coverImage'     => 'nullable|url|max:500',
            'start_year'     => 'nullable|integer',
            'franchise_name' => 'required|string',
        ]);

        $client = $this->neo4j->client();
        $client->run(
            'MATCH (m:Media {id: $id})
             SET m.title       = $title,
                 m.native      = $native,
                 m.format      = $format,
                 m.status      = $status,
                 m.description = $description,
                 m.coverImage  = $coverImage,
                 m.start_year  = $start_year
             WITH m
             OPTIONAL MATCH (old_f:Franchise)-[old_r:HAS_ENTRY]->(m)
             DELETE old_r
             WITH m
             MATCH (new_f:Franchise {name: $franchise_name})
             MERGE (new_f)-[:HAS_ENTRY]->(m)',
            [
                'id'             => (int) $id,
                'title'          => $request->input('title'),
                'native'         => $request->input('native', ''),
                'format'         => $request->input('format', 'UNKNOWN'),
                'status'         => $request->input('status', 'FINISHED'),
                'description'    => $request->input('description', ''),
                'coverImage'     => $request->input('coverImage', ''),
                'start_year'     => (int) $request->input('start_year', date('Y')),
                'franchise_name' => $request->input('franchise_name'),
            ]
        );

        CacheKeys::forget(CacheKeys::onMediaChange($request->input('franchise_name'), (int) $id));

        return redirect()->route('admin.media.index')->with('success', 'Media updated successfully.');
    }

    public function destroy($id)
    {
        $client = $this->neo4j->client();
        $client->run(
            'MATCH (m:Media {id: $id}) DETACH DELETE m',
            ['id' => (int) $id]
        );
        CacheKeys::forget(CacheKeys::onMediaChange('', (int) $id));

        return redirect()->route('admin.media.index')->with('success', 'Media deleted successfully.');
    }
}
