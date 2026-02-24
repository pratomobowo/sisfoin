<?php

namespace App\Http\Controllers\Sekretariat;

use App\Http\Controllers\Controller;
use App\Models\Employee\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $typeOptions = $this->typeOptions();
        $priorityOptions = $this->priorityOptions();
        $statusOptions = $this->statusOptions();

        $announcements = Announcement::query()
            ->with('creator')
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = (string) $request->string('q');
                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', "%{$keyword}%")
                        ->orWhere('content', 'like', "%{$keyword}%");
                });
            })
            ->when($request->filled('type'), fn ($query) => $query->where('type', (string) $request->string('type')))
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', (string) $request->string('priority')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', (string) $request->string('status')))
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('sekretariat.pengumuman.index', [
            'announcements' => $announcements,
            'typeOptions' => $typeOptions,
            'priorityOptions' => $priorityOptions,
            'statusOptions' => $statusOptions,
            'filters' => $request->only(['q', 'type', 'priority', 'status']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateAnnouncement($request);

        Announcement::create([
            ...$validated,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'Pengumuman berhasil ditambahkan.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $announcement = Announcement::query()->findOrFail($id);
        $validated = $this->validateAnnouncement($request);

        $announcement->fill([
            ...$validated,
            'updated_by' => Auth::id(),
        ]);
        $announcement->save();

        return back()->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $announcement = Announcement::query()->findOrFail($id);
        $announcement->delete();

        return back()->with('success', 'Pengumuman berhasil dihapus.');
    }

    public function togglePin(int $id): RedirectResponse
    {
        $announcement = Announcement::query()->findOrFail($id);
        $announcement->update([
            'is_pinned' => ! $announcement->is_pinned,
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'Status pin pengumuman berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:draft,published,archived'],
        ]);

        $announcement = Announcement::query()->findOrFail($id);
        $nextStatus = (string) $request->string('status');

        $announcement->status = $nextStatus;
        if ($nextStatus === 'published' && ! $announcement->published_at) {
            $announcement->published_at = now();
        }
        $announcement->updated_by = Auth::id();
        $announcement->save();

        return back()->with('success', 'Status pengumuman berhasil diperbarui.');
    }

    private function validateAnnouncement(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'type' => ['required', 'in:general,policy,event,urgent,maintenance'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'status' => ['required', 'in:draft,published,archived'],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:published_at'],
            'is_pinned' => ['nullable', 'boolean'],
            'audience_staff' => ['nullable', 'boolean'],
            'audience_employee' => ['nullable', 'boolean'],
        ]);

        $audience = [];
        if ($request->boolean('audience_staff')) {
            $audience[] = 'staff';
        }
        if ($request->boolean('audience_employee')) {
            $audience[] = 'employee';
        }
        if ($audience === []) {
            $audience = ['staff', 'employee'];
        }

        return [
            'title' => $validated['title'],
            'content' => $validated['content'],
            'type' => $validated['type'],
            'priority' => $validated['priority'],
            'status' => $validated['status'],
            'published_at' => $validated['published_at'] ?? ($validated['status'] === 'published' ? now() : null),
            'expires_at' => $validated['expires_at'] ?? null,
            'target_audience' => $audience,
            'is_pinned' => $request->boolean('is_pinned'),
        ];
    }

    private function typeOptions(): array
    {
        return [
            'general' => 'Umum',
            'policy' => 'Kebijakan',
            'event' => 'Acara',
            'urgent' => 'Mendesak',
            'maintenance' => 'Pemeliharaan',
        ];
    }

    private function priorityOptions(): array
    {
        return [
            'low' => 'Rendah',
            'normal' => 'Normal',
            'high' => 'Tinggi',
            'urgent' => 'Mendesak',
        ];
    }

    private function statusOptions(): array
    {
        return [
            'draft' => 'Draft',
            'published' => 'Dipublikasi',
            'archived' => 'Diarsipkan',
        ];
    }
}
