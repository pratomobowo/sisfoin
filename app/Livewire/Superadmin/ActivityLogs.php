<?php

namespace App\Livewire\Superadmin;

use App\Models\ActivityLog as ActivityLogModel;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ActivityLogs extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $page = 1;

    public $search = '';

    public $perPage = 10;

    public $filterType = '';

    public $dateFrom = '';

    public $dateTo = '';

    public $moduleFilter = '';

    public $riskLevelFilter = '';

    public $resultFilter = '';

    public function render()
    {
        $query = ActivityLogModel::with('causer')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('description', 'like', '%'.$this->search.'%')
                        ->orWhere('subject_type', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterType, function ($query) {
                $query->where('log_name', $this->filterType);
            })
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('created_at', '<=', $this->dateTo);
            })
            ->when($this->moduleFilter, function ($query) {
                $query->where('metadata->module', $this->moduleFilter);
            })
            ->when($this->riskLevelFilter, function ($query) {
                $query->where('metadata->risk_level', $this->riskLevelFilter);
            })
            ->when($this->resultFilter, function ($query) {
                $query->where('metadata->result', $this->resultFilter);
            })
            ->orderBy('created_at', 'desc');

        $activityLogs = $query->paginate($this->perPage);

        // Get unique log names for filter dropdown
        $logNames = ActivityLogModel::distinct()->pluck('log_name')->filter()->toArray();
        $modules = ActivityLogModel::query()
            ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.\"module\"')) as value")
            ->whereNotNull('metadata')
            ->whereRaw("JSON_EXTRACT(metadata, '$.\"module\"') IS NOT NULL")
            ->distinct()
            ->pluck('value')
            ->filter()
            ->values()
            ->toArray();

        $riskLevels = ActivityLogModel::query()
            ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.\"risk_level\"')) as value")
            ->whereNotNull('metadata')
            ->whereRaw("JSON_EXTRACT(metadata, '$.\"risk_level\"') IS NOT NULL")
            ->distinct()
            ->pluck('value')
            ->filter()
            ->values()
            ->toArray();

        $results = ActivityLogModel::query()
            ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.\"result\"')) as value")
            ->whereNotNull('metadata')
            ->whereRaw("JSON_EXTRACT(metadata, '$.\"result\"') IS NOT NULL")
            ->distinct()
            ->pluck('value')
            ->filter()
            ->values()
            ->toArray();

        return view('livewire.superadmin.activity-log', [
            'activityLogs' => $activityLogs,
            'logNames' => $logNames,
            'modules' => $modules,
            'riskLevels' => $riskLevels,
            'results' => $results,
            'activities' => $activityLogs, // Using 'activities' to match the view variable name
        ]);
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filterType = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->moduleFilter = '';
        $this->riskLevelFilter = '';
        $this->resultFilter = '';
        $this->perPage = 10;
        $this->resetPage();
    }

    // Pagination methods
    public function gotoPage($page)
    {
        $this->setPage($page);
    }

    public function nextPage()
    {
        $this->setPage($this->page + 1);
    }

    public function previousPage()
    {
        $this->setPage(max(1, $this->page - 1));
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }
}
