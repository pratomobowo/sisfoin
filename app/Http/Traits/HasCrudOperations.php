<?php

namespace App\Http\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

trait HasCrudOperations
{
    /**
     * Store a newly created resource in storage.
     */
    protected function storeResource(Request $request, array $validationRules = [], array $customMessages = []): RedirectResponse
    {
        try {
            $validatedData = $request->validate($validationRules, $customMessages);
            
            DB::beginTransaction();
            
            $model = $this->modelClass::create($validatedData);
            
            // Hook for additional operations after creation
            $this->afterStore($model, $request);
            
            DB::commit();
            
            return $this->redirectWithSuccess("{$this->resourceName} berhasil ditambahkan.");
            
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error creating {$this->resourceName}: " . $e->getMessage());
            
            return $this->redirectWithError("Terjadi kesalahan saat menambahkan {$this->resourceName}.");
        }
    }

    /**
     * Update the specified resource in storage.
     */
    protected function updateResource(Request $request, string $id, array $validationRules = [], array $customMessages = []): RedirectResponse
    {
        try {
            $validatedData = $request->validate($validationRules, $customMessages);
            
            DB::beginTransaction();
            
            $model = $this->findModel($id);
            $model->update($validatedData);
            
            // Hook for additional operations after update
            $this->afterUpdate($model, $request);
            
            DB::commit();
            
            return $this->redirectWithSuccess("{$this->resourceName} berhasil diperbarui.");
            
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating {$this->resourceName}: " . $e->getMessage());
            
            return $this->redirectWithError("Terjadi kesalahan saat memperbarui {$this->resourceName}.");
        }
    }

    /**
     * Bulk delete resources.
     */
    protected function bulkDelete(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return $this->redirectWithError('Tidak ada item yang dipilih untuk dihapus.');
        }

        try {
            DB::beginTransaction();
            
            $models = $this->modelClass::whereIn('id', $ids)->get();
            
            foreach ($models as $model) {
                if (!$this->canDelete($model)) {
                    DB::rollBack();
                    return $this->redirectWithError($this->getDeletionErrorMessage($model));
                }
            }
            
            $this->modelClass::whereIn('id', $ids)->delete();
            
            DB::commit();
            
            $count = count($ids);
            return $this->redirectWithSuccess("{$count} {$this->resourceName} berhasil dihapus.");
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error bulk deleting {$this->resourceName}: " . $e->getMessage());
            
            return $this->redirectWithError("Terjadi kesalahan saat menghapus {$this->resourceName}.");
        }
    }

    /**
     * Toggle status of a resource.
     */
    protected function toggleStatus(string $id, string $statusField = 'status'): RedirectResponse
    {
        try {
            $model = $this->findModel($id);
            
            $currentStatus = $model->{$statusField};
            $newStatus = $currentStatus === 'active' ? 'inactive' : 'active';
            
            $model->update([$statusField => $newStatus]);
            
            $statusText = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';
            
            return $this->redirectWithSuccess("{$this->resourceName} berhasil {$statusText}.");
            
        } catch (\Exception $e) {
            Log::error("Error toggling status for {$this->resourceName}: " . $e->getMessage());
            
            return $this->redirectWithError("Terjadi kesalahan saat mengubah status {$this->resourceName}.");
        }
    }

    /**
     * Hook method called after successful store operation.
     */
    protected function afterStore(Model $model, Request $request): void
    {
        // Override in child classes if needed
    }

    /**
     * Hook method called after successful update operation.
     */
    protected function afterUpdate(Model $model, Request $request): void
    {
        // Override in child classes if needed
    }

    /**
     * Get validation rules for store operation.
     */
    protected function getStoreValidationRules(): array
    {
        return [];
    }

    /**
     * Get validation rules for update operation.
     */
    protected function getUpdateValidationRules(): array
    {
        return $this->getStoreValidationRules();
    }

    /**
     * Get custom validation messages.
     */
    protected function getValidationMessages(): array
    {
        return [];
    }
}