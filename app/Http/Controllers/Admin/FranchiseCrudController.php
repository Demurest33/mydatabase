<?php

namespace App\Http\Controllers\Admin;

use App\Cache\CacheKeys;
use App\DTOs\FranchiseDTO;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Neo4jService;
use Illuminate\Support\Facades\Cache;

class FranchiseCrudController extends Controller
{
    protected Neo4jService $neo4j;

    public function __construct(Neo4jService $neo4j)
    {
        $this->neo4j = $neo4j;
    }

    public function index()
    {
        $rows = Cache::remember(CacheKeys::ADMIN_FRANCHISES_INDEX, CacheKeys::TTL_LONG, function () {
            $client = $this->neo4j->client();
            $result = $client->run(
                'MATCH (f:Franchise)
                 OPTIONAL MATCH (f)-[:HAS_ENTRY]->(m:Media)
                 OPTIONAL MATCH (m)-[:HAS_CHARACTER]->(c:Character)
                 RETURN f.name AS name,
                        count(DISTINCT m) AS mediaCount,
                        count(DISTINCT c) AS characterCount
                 ORDER BY f.name ASC'
            );

            $rows = [];
            foreach ($result as $record) {
                $rows[] = [
                    'name'           => $record->get('name'),
                    'mediaCount'     => (int) $record->get('mediaCount'),
                    'characterCount' => (int) $record->get('characterCount'),
                ];
            }
            return $rows;
        });

        $franchises = array_map(fn($r) => FranchiseDTO::from($r), $rows);

        return view('admin.franchises.index', compact('franchises'));
    }

    public function create()
    {
        return view('admin.franchises.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $client = $this->neo4j->client();
        
        // Check if franchise already exists
        $result = $client->run('MATCH (f:Franchise {name: $name}) RETURN f', ['name' => $request->input('name')]);
        if (!$result->isEmpty()) {
            return back()->with('error', 'Franchise already exists with that name.');
        }

        $client->run('CREATE (f:Franchise {name: $name})', ['name' => $request->input('name')]);

        Cache::forget('franchises.genres');
        Cache::forgetMultiple(CacheKeys::onFranchiseChange($request->input('name')));

        return redirect()->route('admin.franchises.index')->with('success', 'Franchise created successfully.');
    }

    public function show($id)
    {
        // ...
    }

    public function edit($name)
    {
        return view('admin.franchises.edit', ['currentName' => $name]);
    }

    public function update(Request $request, $name)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $newName = $request->input('name');
        $this->neo4j->client()->run(
            'MATCH (f:Franchise {name: $old}) SET f.name = $new',
            ['old' => $name, 'new' => $newName]
        );

        Cache::forget('franchises.genres');
        Cache::forgetMultiple(array_unique(array_merge(
            CacheKeys::onFranchiseChange($name),
            CacheKeys::onFranchiseChange($newName)
        )));

        return redirect()->route('admin.franchises.index')->with('success', 'Franchise renamed successfully.');
    }

    public function destroy($name)
    {
        $this->neo4j->client()->run(
            'MATCH (f:Franchise {name: $name}) DETACH DELETE f',
            ['name' => $name]
        );

        Cache::forget('franchises.genres');
        Cache::forgetMultiple(CacheKeys::onFranchiseChange($name));

        return redirect()->route('admin.franchises.index')->with('success', 'Franchise deleted.');
    }
}
