<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements.
     */
    public function index(): View
    {
        $user = Auth::user();

        $announcements = Announcement::query()
            ->active()
            ->with(['creator', 'readByUsers' => function ($query) use ($user) {
                $query->where('user_id', $user?->id);
            }])
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->get();

        return view('staff.pengumuman.index', [
            'title' => 'Pengumuman',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('staff.dashboard')],
                ['name' => 'Pengumuman', 'url' => null],
            ],
            'announcementTypeOptions' => $this->announcementTypeOptions(),
            'announcements' => $this->toStaffPayloadCollection($announcements, $user?->id),
        ]);
    }

    /**
     * Display the specified announcement.
     */
    public function show(string $id): View
    {
        $user = Auth::user();

        $announcement = Announcement::query()
            ->active()
            ->with(['creator', 'readByUsers' => function ($query) use ($user) {
                $query->where('user_id', $user?->id);
            }])
            ->whereKey($id)
            ->firstOrFail();

        return view('staff.pengumuman.show', [
            'title' => 'Detail Pengumuman',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('staff.dashboard')],
                ['name' => 'Pengumuman', 'url' => route('staff.pengumuman.index')],
                ['name' => 'Detail', 'url' => null],
            ],
            'announcement' => $this->toStaffPayload($announcement, $user?->id),
        ]);
    }

    /**
     * Mark announcement as read.
     */
    public function markAsRead(string $id): JsonResponse
    {
        $user = Auth::user();

        $announcement = Announcement::query()
            ->whereKey($id)
            ->first();

        if (! $announcement) {
            return response()->json([
                'success' => false,
                'message' => 'Pengumuman tidak ditemukan.',
            ], 404);
        }

        $announcement->markAsReadBy($user);

        return response()->json([
            'success' => true,
            'message' => 'Pengumuman ditandai sebagai sudah dibaca.',
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function toStaffPayloadCollection(Collection $announcements, ?int $userId): Collection
    {
        return $announcements->map(function (Announcement $announcement) use ($userId) {
            return $this->toStaffPayload($announcement, $userId);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function toStaffPayload(Announcement $announcement, ?int $userId): array
    {
        return [
            'id' => $announcement->id,
            'title' => $announcement->title,
            'content' => $announcement->content,
            'type' => $announcement->type,
            'type_label' => $announcement->type_label,
            'priority' => $announcement->priority,
            'priority_label' => $announcement->priority_label,
            'status' => $announcement->status,
            'status_label' => $announcement->status_label,
            'is_pinned' => (bool) $announcement->is_pinned,
            'published_at' => $announcement->published_at,
            'expires_at' => $announcement->expires_at,
            'created_by' => $announcement->creator?->name ?? 'System',
            'created_at' => $announcement->created_at,
            'read_status' => $userId
                ? $announcement->readByUsers->contains('id', $userId)
                : false,
            'attachments' => is_array($announcement->attachments) ? $announcement->attachments : [],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function announcementTypeOptions(): array
    {
        return [
            'general' => 'Umum',
            'policy' => 'Kebijakan',
            'event' => 'Acara',
            'urgent' => 'Mendesak',
            'maintenance' => 'Pemeliharaan',
        ];
    }
}
