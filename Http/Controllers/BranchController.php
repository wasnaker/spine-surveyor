<?php

declare(strict_types=1);

namespace Modules\Customer\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Customer\Models\Branch;
use Modules\Vat\Services\VatService;
use Spine\Services\ActivityLogService;

/**
 * CRUD Branch — kantor cabang / site / pabrik.
 *
 * Field:
 *   - customer_id  FK ke customers
 *   - code         (nullable)
 *   - name, address, phone
 *   - npwp         (string dari form; auto-create Vat row, simpan vat_id)
 *   - is_active
 */
class BranchController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
        private readonly VatService $vats,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $query = Branch::with('vat');

        if ($request->has('customer_id')) {
            $query->where('customer_id', (int) $request->query('customer_id'));
        }
        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        return response()->json(['data' => $query->orderByDesc('id')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'code'        => ['nullable', 'string', 'max:64'],
            'name'        => ['required', 'string', 'max:190'],
            'address'     => ['nullable', 'string'],
            'phone'       => ['nullable', 'string', 'max:32'],
            'npwp'        => ['nullable', 'string', 'max:32'],
            'is_active'   => ['sometimes', 'boolean'],
        ]);

        $vatId = null;
        if (! empty($validated['npwp'])) {
            $vatId = $this->vats->findOrCreateId($validated['npwp'], $validated['name']);
        }
        unset($validated['npwp']);

        $entity = Branch::create($validated + ['vat_id' => $vatId]);

        Log::info("[Branch] created", ['id' => $entity->id, 'customer_id' => $entity->customer_id]);

        return response()->json($entity, 201);
    }

    public function show(int $id): JsonResponse
    {
        $entity = Branch::with(['customer', 'vat'])->find($id);

        if (! $entity) {
            return response()->json(['message' => 'Branch not found'], 404);
        }

        return response()->json($entity);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $entity = Branch::find($id);

        if (! $entity) {
            return response()->json(['message' => 'Branch not found'], 404);
        }

        $validated = $request->validate([
            'customer_id' => ['sometimes', 'integer', 'exists:customers,id'],
            'code'        => ['nullable', 'string', 'max:64'],
            'name'        => ['sometimes', 'string', 'max:190'],
            'address'     => ['nullable', 'string'],
            'phone'       => ['nullable', 'string', 'max:32'],
            'npwp'        => ['nullable', 'string', 'max:32'],
            'is_active'   => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('npwp', $validated)) {
            if (! empty($validated['npwp'])) {
                $entity->vat_id = $this->vats->findOrCreateId($validated['npwp'], $entity->name);
            } else {
                $entity->vat_id = null;
            }
            unset($validated['npwp']);
        }

        $entity->update($validated);

        Log::info("[Branch] updated", ['id' => $entity->id, 'customer_id' => $entity->customer_id]);

        return response()->json($entity);
    }

    public function destroy(int $id): JsonResponse
    {
        $entity = Branch::find($id);

        if (! $entity) {
            return response()->json(['message' => 'Branch not found'], 404);
        }

        $entity->delete();

        return response()->json(['message' => 'Branch deleted']);
    }

    public function activityLogs(int $id): JsonResponse
    {
        if (! Branch::find($id)) {
            return response()->json(['message' => 'Branch not found'], 404);
        }

        $logs = $this->activityLog
            ->query()
            ->where('subject_type', Branch::class)
            ->where('subject_id', $id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($log) => [
                'id'          => $log->id,
                'description' => $log->description,
                'causer'      => $log->causer?->name ?? 'System',
                'properties'  => $log->properties,
                'at'          => $log->created_at?->toIso8601String(),
            ]);

        return response()->json(['data' => $logs]);
    }
}
