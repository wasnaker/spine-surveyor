<?php

declare(strict_types=1);

namespace Modules\Customer\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Customer\Models\Customer;
use Modules\Vat\Services\VatService;
use Spine\Services\ActivityLogService;

/**
 * CRUD Customer — modul Customer.
 *
 * Field business:
 *   - code          (unique kode internal)
 *   - name, email, phone
 *   - npwp          (string dari form; auto-create Vat row, simpan vat_id FK)
 *   - is_active     (boolean)
 *
 * Activity log OTOMATIS via EntityCreated/Updated/Deleted (HasLifecycleHooks)
 * -> listener LogCustomerActivity di ServiceProvider.
 */
class CustomerController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
        private readonly VatService $vats,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $query = Customer::with('vat');

        if ($request->filled('q')) {
            $term = $request->string('q');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('code', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
            });
        }
        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        return response()->json(['data' => $query->orderByDesc('id')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code'      => ['required', 'string', 'max:64', 'unique:customers,code'],
            'name'      => ['required', 'string', 'max:190'],
            'email'     => ['nullable', 'string', 'email', 'max:190'],
            'phone'     => ['nullable', 'string', 'max:32'],
            'npwp'      => ['nullable', 'string', 'max:32'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $vatId = null;
        if (! empty($validated['npwp'])) {
            $vatId = $this->vats->findOrCreateId($validated['npwp'], $validated['name']);
        }
        unset($validated['npwp']);

        $entity = Customer::create($validated + ['vat_id' => $vatId]);

        Log::info("[Customer] created", ['id' => $entity->id, 'code' => $entity->code]);

        return response()->json($entity, 201);
    }

    public function show(int $id): JsonResponse
    {
        $entity = Customer::with(['branches.vat', 'vat'])->find($id);

        if (! $entity) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        return response()->json($entity);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $entity = Customer::find($id);

        if (! $entity) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $validated = $request->validate([
            'code'      => ['sometimes', 'string', 'max:64', 'unique:customers,code,' . $id],
            'name'      => ['sometimes', 'string', 'max:190'],
            'email'     => ['nullable', 'string', 'email', 'max:190'],
            'phone'     => ['nullable', 'string', 'max:32'],
            'npwp'      => ['nullable', 'string', 'max:32'],
            'is_active' => ['sometimes', 'boolean'],
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

        Log::info("[Customer] updated", ['id' => $entity->id, 'code' => $entity->code]);

        return response()->json($entity);
    }

    public function destroy(int $id): JsonResponse
    {
        $entity = Customer::find($id);

        if (! $entity) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $entity->delete();

        return response()->json(['message' => 'Customer deleted']);
    }

    public function activityLogs(int $id): JsonResponse
    {
        if (! Customer::find($id)) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $logs = $this->activityLog
            ->query()
            ->where('subject_type', Customer::class)
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

    /**
     * Branches milik customer ini (nested resource).
     */
    public function branches(int $id, Request $request): JsonResponse
    {
        if (! Customer::find($id)) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $query = \Modules\Customer\Models\Branch::query()->where('customer_id', $id);
        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        return response()->json(['data' => $query->orderByDesc('id')->get()]);
    }
}
