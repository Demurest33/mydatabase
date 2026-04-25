<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ImportWallpaperJob;
use App\Models\WallpaperImportLog;
use App\Services\Neo4jService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class WallpaperImportController extends Controller
{
    public function __construct(private Neo4jService $neo4j) {}

    public function index(Request $request)
    {
        // Prefer an explicitly requested batch (e.g. just finished, user wants to review)
        // then fall back to any batch that is still running
        $batchId = $request->query('batch') ?? $this->findActiveBatchId();
        return view('admin.wallpaper-import', compact('batchId'));
    }

    public function store(Request $request)
    {
        // Block new imports while one is already running
        if ($activeBatchId = $this->findActiveBatchId()) {
            return redirect()->route('admin.wallpaper-import', ['batch' => $activeBatchId]);
        }

        $request->validate([
            'mode'          => 'required|in:path,ids',
            'path'          => 'required_if:mode,path|nullable|string|max:500',
            'ids_list'      => 'required_if:mode,ids|nullable|string',
            'delay_seconds' => 'nullable|integer|min:1|max:30',
        ]);

        $result = $this->neo4j->client()->run(
            'MATCH (m:Media) WHERE toLower(m.title) = "wallpaper engine" RETURN m.id as id LIMIT 1'
        );
        if ($result->isEmpty()) {
            return back()->with('error', 'No se encontró un Media llamado "Wallpaper Engine". Créalo primero en el backoffice.');
        }
        $mediaId = (int) $result->first()->get('id');

        if ($request->input('mode') === 'path') {
            $path = rtrim($request->input('path'), '/\\');

            if (!is_dir($path)) {
                return back()->with('error', "Directorio no encontrado: {$path}");
            }

            $entries = @scandir($path);
            if ($entries === false) {
                return back()->with('error', 'No se pudo leer el directorio. Verifica los permisos.');
            }

            $ids = array_values(array_filter(
                $entries,
                fn($name) => is_numeric($name) && is_dir($path . DIRECTORY_SEPARATOR . $name)
            ));

            if (empty($ids)) {
                return back()->with('error', 'No se encontraron subcarpetas con IDs numéricos en esa ruta.');
            }
        } else {
            $raw = preg_split('/[\s,;]+/', $request->input('ids_list', ''));
            $ids = array_values(array_filter($raw, fn($v) => is_numeric(trim($v))));
            $ids = array_unique($ids);

            if (empty($ids)) {
                return back()->with('error', 'No se encontraron IDs numéricos válidos.');
            }
        }

        $delay = (int) ($request->input('delay_seconds') ?? 3);
        $jobs  = [];
        foreach (array_values($ids) as $index => $id) {
            $jobs[] = (new ImportWallpaperJob(trim($id), $mediaId))
                ->delay(now()->addSeconds($index * $delay));
        }

        $batch = Bus::batch($jobs)
            ->name('Wallpaper Engine Import')
            ->allowFailures()
            ->dispatch();

        return redirect()->route('admin.wallpaper-import', ['batch' => $batch->id]);
    }

    private function findActiveBatchId(): ?string
    {
        return DB::table('job_batches')
            ->where('name', 'Wallpaper Engine Import')
            ->whereNull('finished_at')
            ->whereNull('cancelled_at')
            ->orderByDesc('created_at')
            ->value('id');
    }

    public function progress(string $batchId)
    {
        $batch = Bus::findBatch($batchId);

        if (!$batch) {
            return response()->json(['error' => 'Batch not found'], 404);
        }

        $logs = WallpaperImportLog::where('batch_id', $batchId)
            ->orderBy('id')
            ->get(['workshop_id', 'status', 'reason'])
            ->toArray();

        return response()->json([
            'total'     => $batch->totalJobs,
            'processed' => $batch->processedJobs(),
            'failed'    => $batch->failedJobs,
            'pending'   => $batch->pendingJobs,
            'finished'  => $batch->finished(),
            'cancelled' => $batch->cancelled(),
            'progress'  => $batch->progress(),
            'logs'      => $logs,
        ]);
    }
}
