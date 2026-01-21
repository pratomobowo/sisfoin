<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

abstract class BaseController extends Controller
{
    /**
     * The model class name for this controller.
     */
    protected string $modelClass;

    /**
     * The view prefix for this controller.
     */
    protected string $viewPrefix;

    /**
     * The route prefix for this controller.
     */
    protected string $routePrefix;

    /**
     * The resource name for messages.
     */
    protected string $resourceName;

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view("{$this->viewPrefix}.index");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view("{$this->viewPrefix}.create");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $model = $this->findModel($id);
        $variableName = $this->getModelVariableName();

        return view("{$this->viewPrefix}.show", [$variableName => $model]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $model = $this->findModel($id);
        $variableName = $this->getModelVariableName();

        return view("{$this->viewPrefix}.edit", [$variableName => $model]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $model = $this->findModel($id);
        
        // Check if deletion is allowed
        if (!$this->canDelete($model)) {
            return $this->redirectWithError($this->getDeletionErrorMessage($model));
        }

        $model->delete();

        return $this->redirectWithSuccess("{$this->resourceName} berhasil dihapus.");
    }

    /**
     * Find model by ID or fail.
     */
    protected function findModel(string $id): Model
    {
        return $this->modelClass::findOrFail($id);
    }

    /**
     * Get the variable name for the model in views.
     */
    protected function getModelVariableName(): string
    {
        $className = class_basename($this->modelClass);
        return strtolower($className);
    }

    /**
     * Check if the model can be deleted.
     */
    protected function canDelete(Model $model): bool
    {
        return true;
    }

    /**
     * Get the error message when deletion is not allowed.
     */
    protected function getDeletionErrorMessage(Model $model): string
    {
        return "{$this->resourceName} tidak dapat dihapus.";
    }

    /**
     * Redirect to index with success message.
     */
    protected function redirectWithSuccess(string $message): RedirectResponse
    {
        return redirect()->route("{$this->routePrefix}.index")
            ->with('message', $message);
    }

    /**
     * Redirect to index with error message.
     */
    protected function redirectWithError(string $message): RedirectResponse
    {
        return redirect()->route("{$this->routePrefix}.index")
            ->with('error', $message);
    }
}