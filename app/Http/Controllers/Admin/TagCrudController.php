<?php

namespace App\Http\Controllers\Admin;

use App\Cache\CacheKeys;
use App\DTOs\TagDTO;
use App\Http\Controllers\Controller;
use App\Services\Neo4jService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagCrudController extends Controller
{
    public function __construct(protected Neo4jService $neo4j) {}

    public function index()
    {
        $client = $this->neo4j->client();
        $result = $client->run(
            'MATCH (t:Tag) RETURN t ORDER BY t.type ASC, t.category ASC, t.name ASC'
        );

        $tags = [];
        foreach ($result as $record) {
            $d = $record->get('t')->getProperties()->toArray();
            $tags[(string)($d['type'] ?? 'character')][(string)($d['category'] ?? '')][] = TagDTO::from($d);
        }

        return view('admin.tags.index', compact('tags'));
    }

    public function create()
    {
        return view('admin.tags.create');
    }

    public function store(Request $request)
    {
        $rows = collect($request->input('tags', []))
            ->filter(fn($t) => !empty(trim($t['name'] ?? '')));

        if ($rows->isEmpty()) {
            return back()->withInput()->with('error', 'Añade al menos un tag con nombre.');
        }

        $request->validate([
            'tags.*.name'     => 'required|string|max:100',
            'tags.*.type'     => 'required|string|in:character,media,asset',
            'tags.*.category' => 'required|string|max:100',
        ]);

        $client  = $this->neo4j->client();
        $created = 0;
        $skipped = 0;

        foreach ($rows as $tagData) {
            $name = trim($tagData['name']);
            $type = $tagData['type'] ?? 'character';
            $cat  = trim($tagData['category'] ?? '');

            // MERGE on (name, type) — silently skips exact duplicates
            $result = $client->run(
                'MERGE (t:Tag {name: $name, type: $type})
                 ON CREATE SET t.id = $id, t.slug = $slug, t.category = $category
                 RETURN t.category AS cat',
                [
                    'id'       => rand(10000000, 99999999),
                    'name'     => $name,
                    'slug'     => Str::slug($name),
                    'type'     => $type,
                    'category' => $cat,
                ]
            );

            // If the returned category equals what we just tried to set, it was created
            $result->first()->get('cat') === $cat ? $created++ : $skipped++;
        }

        CacheKeys::forget(CacheKeys::onTagChange());

        $msg = "{$created} " . ($created === 1 ? 'tag creado' : 'tags creados');
        if ($skipped > 0) $msg .= ", {$skipped} ya " . ($skipped === 1 ? 'existía' : 'existían');

        return redirect()->route('admin.tags.index')->with('success', $msg . '.');
    }

    public function edit($id)
    {
        $result = $this->neo4j->client()->run(
            'MATCH (t:Tag {id: $id}) RETURN t',
            ['id' => (int) $id]
        );

        if ($result->isEmpty()) {
            return redirect()->route('admin.tags.index')->with('error', 'Tag no encontrado.');
        }

        $tag = TagDTO::from($result->first()->get('t')->getProperties()->toArray());

        return view('admin.tags.edit', compact('tag'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'type'     => 'required|string|in:character,media,asset',
            'category' => 'required|string|max:100',
        ]);

        $this->neo4j->client()->run(
            'MATCH (t:Tag {id: $id}) SET t.name = $name, t.slug = $slug, t.type = $type, t.category = $category',
            [
                'id'       => (int) $id,
                'name'     => $request->input('name'),
                'slug'     => Str::slug($request->input('name')),
                'type'     => $request->input('type'),
                'category' => trim($request->input('category')),
            ]
        );

        CacheKeys::forget(CacheKeys::onTagChange());

        return redirect()->route('admin.tags.index')->with('success', 'Tag actualizado.');
    }

    public function destroy($id)
    {
        $this->neo4j->client()->run(
            'MATCH (t:Tag {id: $id}) DETACH DELETE t',
            ['id' => (int) $id]
        );

        CacheKeys::forget(CacheKeys::onTagChange());

        return redirect()->route('admin.tags.index')->with('success', 'Tag eliminado.');
    }
}
