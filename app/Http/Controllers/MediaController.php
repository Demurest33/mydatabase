<?php

namespace App\Http\Controllers;

use App\Services\Neo4jService;
use Illuminate\Http\Request;
use Exception;

class MediaController extends Controller
{
    protected Neo4jService $neo4j;

    public function __construct(Neo4jService $neo4j)
    {
        $this->neo4j = $neo4j;
    }

    public function show($id)
    {
        try {
            $client = $this->neo4j->client();

            // Match media and its elements
            $query = '
                MATCH (m:Media {id: $id})
                OPTIONAL MATCH (m)-[:HAS_ASSET]->(a:Asset)
                OPTIONAL MATCH (m)-[:HAS_GENRE]->(g:Genre)
                RETURN m, 
                       collect(DISTINCT a) as assets,
                       collect(DISTINCT g.name) as genres
            ';

            $result = $client->run($query, ['id' => (int)$id]);

            if ($result->isEmpty()) {
                abort(404, "Media item not found in graph database.");
            }

            $record = $result->first();
            
            // Validate that we actually got a Media node
            $mNode = $record->get('m');
            if ($mNode === null) {
                abort(404, "Media item not found.");
            }
            
            $media = $mNode->getProperties()->toArray();
            
            // Genres handling
            $genres = array_filter($record->get('genres')->toArray());
            
            // Assets parsing and sorting
            $assetsItems = $record->get('assets')->toArray();
            $assets = [];
            foreach ($assetsItems as $aNode) {
                if ($aNode !== null) {
                    $a = $aNode->getProperties()->toArray();
                    
                    // Format dates safely
                    if (isset($a['createdAt']) && is_object($a['createdAt'])) {
                        $a['createdAtStr'] = method_exists($a['createdAt'], 'toDateTime') 
                            ? $a['createdAt']->toDateTime()->format('c') 
                            : (string)$a['createdAt'];
                    } else {
                        $a['createdAtStr'] = $a['createdAt'] ?? now()->format('c');
                    }

                    // Extract actual URL dynamically 
                    $a['fileUrl'] = $a['url'] ?? null;
                    if (!$a['fileUrl'] && isset($a['filename'])) {
                        $a['fileUrl'] = asset('storage/assets/' . $a['filename']);
                    }
                    if (isset($a['coverFilename'])) {
                        $a['coverUrl'] = asset('storage/assets/covers/' . $a['coverFilename']);
                    }
                    
                    $assets[] = $a;
                }
            }
            
            // Sort assets ascending by Title so Episode 1, 2, 3 align. Fallback to ID/Creation if no title.
            usort($assets, function($a, $b) {
                $titleA = $a['title'] ?? $a['id'];
                $titleB = $b['title'] ?? $b['id'];
                // Use natural sort so 'Episode 10' comes after 'Episode 2'
                return strnatcmp($titleA, $titleB);
            });

            return view('media.show', compact('media', 'genres', 'assets'));

        } catch (Exception $e) {
            abort(500, $e->getMessage());
        }
    }
}
