<?php

namespace Database\Seeders;

use App\Models\Employee\Announcement;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::role('super-admin')->first() ?? User::first();
        
        if (!$admin) return;

        $announcements = [
            [
                'title' => 'Tausiyah Mingguan - Pentingnya Sholat Berjamaah',
                'content' => 'Assalamualaikum warahmatullahi wabarakatuh. Dalam rangka meningkatkan ketakwaan kepada Allah SWT, kami mengundang seluruh karyawan untuk mengikuti tausiyah mingguan yang akan membahas tentang pentingnya sholat berjamaah dalam kehidupan sehari-hari.',
                'type' => 'general',
                'priority' => 'normal',
                'status' => 'published',
                'is_pinned' => true,
                'published_at' => now()->subDays(1),
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Pengumuman Libur Hari Raya Idul Fitri 1445 H',
                'content' => 'Berkenaan dengan perayaan Hari Raya Idul Fitri 1445 H, kami informasikan bahwa kantor akan libur mulai tanggal 10-15 April 2024. Seluruh karyawan diharapkan dapat memanfaatkan waktu libur ini untuk berkumpul bersama keluarga.',
                'type' => 'policy',
                'priority' => 'high',
                'status' => 'published',
                'is_pinned' => true,
                'published_at' => now()->subDays(3),
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Kajian Rutin: Akhlak dalam Bekerja',
                'content' => 'Mengundang seluruh karyawan untuk mengikuti kajian rutin bulanan dengan tema "Akhlak dalam Bekerja" yang akan dilaksanakan pada hari Jumat, 15 Maret 2024 pukul 13.00-14.00 WIB di Aula kantor.',
                'type' => 'event',
                'priority' => 'normal',
                'status' => 'published',
                'is_pinned' => false,
                'published_at' => now()->subDays(5),
                'created_by' => $admin->id,
            ],
        ];

        foreach ($announcements as $data) {
            Announcement::create($data);
        }
    }
}
