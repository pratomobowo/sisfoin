<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements.
     */
    public function index(): View
    {
        // Generate dummy announcement data
        $announcements = $this->generateDummyAnnouncementData();

        return view('staff.pengumuman.index', [
            'title' => 'Pengumuman',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('staff.dashboard')],
                ['name' => 'Pengumuman', 'url' => null],
            ],
            'announcements' => $announcements,
        ]);
    }

    /**
     * Show the form for creating a new announcement.
     */
    public function create(): View
    {
        return view('employee.announcements.create', [
            'title' => 'Buat Pengumuman',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'Karyawan', 'url' => route('employee.dashboard')],
                ['name' => 'Pengumuman', 'url' => route('employee.announcements.index')],
                ['name' => 'Buat', 'url' => null],
            ],
        ]);
    }

    /**
     * Store a newly created announcement in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // TODO: Implement announcement creation logic
        return redirect()->route('employee.announcements.index')
            ->with('success', 'Pengumuman berhasil dibuat.');
    }

    /**
     * Display the specified announcement.
     */
    public function show(string $id): View
    {
        // Get dummy announcement data by ID
        $announcement = $this->getDummyAnnouncementById($id);

        return view('staff.pengumuman.show', [
            'title' => 'Detail Pengumuman',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('staff.dashboard')],
                ['name' => 'Pengumuman', 'url' => route('staff.pengumuman.index')],
                ['name' => 'Detail', 'url' => null],
            ],
            'announcement' => $announcement,
        ]);
    }

    /**
     * Show the form for editing the specified announcement.
     */
    public function edit(string $id): View
    {
        return view('employee.announcements.edit', [
            'title' => 'Edit Pengumuman',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'Karyawan', 'url' => route('employee.dashboard')],
                ['name' => 'Pengumuman', 'url' => route('employee.announcements.index')],
                ['name' => 'Edit', 'url' => null],
            ],
        ]);
    }

    /**
     * Update the specified announcement in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        // TODO: Implement announcement update logic
        return redirect()->route('employee.announcements.index')
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    /**
     * Remove the specified announcement from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        // TODO: Implement announcement deletion logic
        return redirect()->route('employee.announcements.index')
            ->with('success', 'Pengumuman berhasil dihapus.');
    }

    /**
     * Mark announcement as read.
     */
    public function markAsRead(string $id): JsonResponse
    {
        // TODO: Implement actual database logic when real data is available
        // For now, just return success response
        return response()->json([
            'success' => true,
            'message' => 'Pengumuman ditandai sebagai sudah dibaca.'
        ]);
    }

    /**
     * Display archived announcements.
     */
    public function archived(): View
    {
        return view('employee.announcements.archived', [
            'title' => 'Pengumuman Arsip',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'Karyawan', 'url' => route('employee.dashboard')],
                ['name' => 'Pengumuman', 'url' => route('employee.announcements.index')],
                ['name' => 'Arsip', 'url' => null],
            ],
        ]);
    }

    /**
     * Archive the specified announcement.
     */
    public function archive(string $id): RedirectResponse
    {
        // TODO: Implement announcement archiving logic
        return redirect()->route('employee.announcements.index')
            ->with('success', 'Pengumuman berhasil diarsipkan.');
    }

    /**
     * Restore the specified announcement from archive.
     */
    public function restore(string $id): RedirectResponse
    {
        // TODO: Implement announcement restoration logic
        return redirect()->route('employee.announcements.index')
            ->with('success', 'Pengumuman berhasil dipulihkan dari arsip.');
    }

    /**
     * Generate dummy announcement data for staff view.
     */
    private function generateDummyAnnouncementData(): array
    {
        return [
            [
                'id' => 1,
                'title' => 'Tausiyah Mingguan - Pentingnya Sholat Berjamaah',
                'content' => 'Assalamualaikum warahmatullahi wabarakatuh. Dalam rangka meningkatkan ketakwaan kepada Allah SWT, kami mengundang seluruh karyawan untuk mengikuti tausiyah mingguan yang akan membahas tentang pentingnya sholat berjamaah dalam kehidupan sehari-hari.',
                'type' => 'tausiyah',
                'priority' => 'normal',
                'status' => 'published',
                'is_pinned' => true,
                'published_at' => now()->subDays(1),
                'expires_at' => now()->addDays(7),
                'created_by' => 'Admin HR',
                'created_at' => now()->subDays(1),
                'read_status' => false,
                'attachments' => [],
            ],
            [
                'id' => 2,
                'title' => 'Pengumuman Libur Hari Raya Idul Fitri 1445 H',
                'content' => 'Berkenaan dengan perayaan Hari Raya Idul Fitri 1445 H, kami informasikan bahwa kantor akan libur mulai tanggal 10-15 April 2024. Seluruh karyawan diharapkan dapat memanfaatkan waktu libur ini untuk berkumpul bersama keluarga.',
                'type' => 'pengumuman',
                'priority' => 'high',
                'status' => 'published',
                'is_pinned' => true,
                'published_at' => now()->subDays(3),
                'expires_at' => now()->addDays(10),
                'created_by' => 'Direktur',
                'created_at' => now()->subDays(3),
                'read_status' => true,
                'attachments' => ['surat_libur_idul_fitri.pdf'],
            ],
            [
                'id' => 3,
                'title' => 'Kajian Rutin: Akhlak dalam Bekerja',
                'content' => 'Mengundang seluruh karyawan untuk mengikuti kajian rutin bulanan dengan tema "Akhlak dalam Bekerja" yang akan dilaksanakan pada hari Jumat, 15 Maret 2024 pukul 13.00-14.00 WIB di Aula kantor.',
                'type' => 'kajian',
                'priority' => 'normal',
                'status' => 'published',
                'is_pinned' => false,
                'published_at' => now()->subDays(5),
                'expires_at' => now()->addDays(3),
                'created_by' => 'Tim Rohani',
                'created_at' => now()->subDays(5),
                'read_status' => false,
                'attachments' => [],
            ],
            [
                'id' => 4,
                'title' => 'Himbauan Protokol Kesehatan di Lingkungan Kerja',
                'content' => 'Dalam upaya menjaga kesehatan bersama, kami menghimbau seluruh karyawan untuk tetap menerapkan protokol kesehatan seperti mencuci tangan, menjaga jarak, dan menggunakan masker saat diperlukan.',
                'type' => 'himbauan',
                'priority' => 'normal',
                'status' => 'published',
                'is_pinned' => false,
                'published_at' => now()->subWeek(),
                'expires_at' => null,
                'created_by' => 'Tim Kesehatan',
                'created_at' => now()->subWeek(),
                'read_status' => true,
                'attachments' => ['protokol_kesehatan.pdf'],
            ],
            [
                'id' => 5,
                'title' => 'Undangan Buka Puasa Bersama 1445 H',
                'content' => 'Dalam rangka mempererat silaturahmi antar karyawan, kami mengundang seluruh keluarga besar perusahaan untuk mengikuti acara buka puasa bersama yang akan dilaksanakan pada hari Sabtu, 30 Maret 2024 pukul 17.30 WIB.',
                'type' => 'undangan',
                'priority' => 'high',
                'status' => 'published',
                'is_pinned' => false,
                'published_at' => now()->subDays(2),
                'expires_at' => now()->addDays(5),
                'created_by' => 'Panitia Ramadhan',
                'created_at' => now()->subDays(2),
                'read_status' => false,
                'attachments' => ['undangan_bukber.jpg'],
            ],
        ];
    }

    /**
     * Get dummy announcement data by ID.
     */
    private function getDummyAnnouncementById(string $id): array
    {
        $announcements = $this->generateDummyAnnouncementData();

        foreach ($announcements as $announcement) {
            if ($announcement['id'] == $id) {
                return $announcement;
            }
        }

        // Return default if not found
        return [
            'id' => $id,
            'title' => 'Pengumuman Tidak Ditemukan',
            'content' => 'Maaf, pengumuman yang Anda cari tidak ditemukan atau sudah tidak tersedia.',
            'type' => 'pengumuman',
            'priority' => 'normal',
            'status' => 'published',
            'is_pinned' => false,
            'published_at' => now(),
            'expires_at' => null,
            'created_by' => 'System',
            'created_at' => now(),
            'read_status' => false,
            'attachments' => [],
        ];
    }

    /**
     * Get priority badge class.
     */
    private function getPriorityBadge(string $priority): string
    {
        return match ($priority) {
            'low' => 'bg-secondary',
            'normal' => 'bg-primary',
            'high' => 'bg-warning',
            'urgent' => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    /**
     * Get type badge class.
     */
    private function getTypeBadge(string $type): string
    {
        return match ($type) {
            'tausiyah' => 'bg-success',
            'kajian' => 'bg-info',
            'pengumuman' => 'bg-primary',
            'himbauan' => 'bg-warning',
            'undangan' => 'bg-purple',
            default => 'bg-secondary'
        };
    }
}
