<?php

namespace App\Http\Controllers;

use App\Services\Neo4jService;
use Illuminate\Http\Request;
use Exception;

class Neo4jController extends Controller
{
    protected Neo4jService $neo4j;

    public function __construct(Neo4jService $neo4j)
    {
        $this->neo4j = $neo4j;
    }

    public function index()
    {
        $franchises = [];
        $error = null;

        try {
            $client = $this->neo4j->client();
            $result = $client->run('MATCH (f:Franchise) RETURN f.name AS name');
            foreach ($result as $record) {
                $franchises[] = $record->get('name');
            }
        } catch (Exception $e) {
            $error = "Error conectando a Neo4j: " . $e->getMessage();
        }

        return view('neo4j.index', compact('franchises', 'error'));
    }

    public function graphData(Request $request)
    {
        $franchise = $request->input('franchise');

        try {
            $client = $this->neo4j->client();

            if ($franchise) {
                // Obtener todos los nodos y relaciones en torno a la franquicia seleccionada
                $query = '
                    MATCH (f:Franchise {name: $name})-[r1:HAS_ENTRY]->(m:Media)
                    OPTIONAL MATCH (m)-[r2]->(node)
                    RETURN f, r1, m, r2, node
                ';
                $params = ['name' => $franchise];
            } else {
                // Si no hay franquicia, retornamos nodos vacíos (o se podría hacer un grafo global con LIMIT)
                return response()->json(['nodes' => [], 'edges' => []]);
            }

            $result = $client->run($query, $params);

            $nodes = [];
            $edges = [];
            $addedNodes = [];
            $addedEdges = [];

            // Helper para extraer datos de los Nodos independiente de la versión de Laudis
            $extractNode = function($node) use (&$nodes, &$addedNodes) {
                if (!$node) return;

                $id = method_exists($node, 'getId') ? (string)$node->getId() : (method_exists($node, 'identity') ? (string)$node->identity() : null);
                if ($id === null || in_array($id, $addedNodes, true)) return;

                $labels = method_exists($node, 'getLabels') ? $node->getLabels()->toArray() : ['Unknown'];
                $label = $labels[0] ?? 'Unknown';
                $props = method_exists($node, 'getProperties') ? $node->getProperties()->toArray() : [];

                $name = $props['name'] ?? $props['title'] ?? $props['native'] ?? 'Unknown Node';

                $color = match($label) {
                    'Franchise' => '#e11d48', // Red
                    'Media'     => '#7451f1', // Purple
                    'Character' => '#f59e0b', // Amber
                    'Studio'    => '#10b981', // Emerald
                    'Genre'     => '#3b82f6', // Blue
                    default     => '#94a3b8'  // Slate
                };

                $size = match($label) {
                    'Franchise' => 45,
                    'Media'     => 30,
                    'Character' => 12,
                    'Studio'    => 20,
                    'Genre'     => 15,
                    default     => 10
                };

                $htmlProps = "";
                foreach($props as $k => $v) { $htmlProps .= "<b>$k:</b> " . (is_string($v) ? $v : json_encode($v)) . "<br>"; }

                $nodes[] = [
                    'id' => $id,
                    'label' => wordwrap($name, 15, "\n", true),
                    'group' => $label,
                    'color' => ['background' => $color, 'border' => 'transparent'],
                    'shape' => 'dot',
                    'size' => $size,
                    'font' => ['color' => '#ffffff'],
                    'title' => "<div style='background:#151921; color:white; padding:10px; border-radius:10px; font-family:sans-serif;'><b>{$label}</b><br><br>{$htmlProps}</div>",
                ];
                $addedNodes[] = $id;
            };

            // Helper para extraer relaciones
            $extractEdge = function($rel) use (&$edges, &$addedEdges) {
                if (!$rel) return;
                $id = method_exists($rel, 'getId') ? (string)$rel->getId() : (method_exists($rel, 'identity') ? (string)$rel->identity() : null);
                if ($id === null || in_array($id, $addedEdges, true)) return;

                $type = method_exists($rel, 'getType') ? $rel->getType() : (method_exists($rel, 'type') ? $rel->type() : 'RELATED');
                $start = method_exists($rel, 'getStartNodeId') ? (string)$rel->getStartNodeId() : null;
                $end = method_exists($rel, 'getEndNodeId') ? (string)$rel->getEndNodeId() : null;
                $props = method_exists($rel, 'getProperties') ? $rel->getProperties()->toArray() : [];

                $edgeLabel = $type;
                if (!empty($props['role'])) {
                    $edgeLabel .= "\n({$props['role']})";
                }

                $edges[] = [
                    'id' => $id,
                    'from' => $start,
                    'to' => $end,
                    'label' => $edgeLabel,
                    'font' => ['color' => '#64748b', 'size' => 10, 'align' => 'middle'],
                    'color' => ['color' => 'rgba(255,255,255,0.15)', 'highlight' => '#7451f1'],
                    'arrows' => 'to',
                    'smooth' => ['type' => 'dynamic']
                ];
                $addedEdges[] = $id;
            };

            // Recorrer los resultados de Cypher
            foreach ($result as $row) {
                if ($row->hasKey('f')) $extractNode($row->get('f'));
                if ($row->hasKey('m')) $extractNode($row->get('m'));
                if ($row->hasKey('node') && $row->get('node') !== null) $extractNode($row->get('node'));
                
                if ($row->hasKey('r1') && $row->get('r1') !== null) $extractEdge($row->get('r1'));
                if ($row->hasKey('r2') && $row->get('r2') !== null) $extractEdge($row->get('r2'));
            }

            return response()->json([
                'nodes' => $nodes,
                'edges' => $edges
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
