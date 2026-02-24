<?php

namespace App\Services;

use App\Imports\SlipGajiImport;
use App\Models\SlipGajiDetail;
use App\Models\SlipGajiHeader;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Browsershot\Browsershot;

class SlipGajiService
{
    public function uploadAndValidate(UploadedFile $file, string $periode, string $mode = 'standard'): array
    {
        // Check if periode and mode combination already exists
        if (SlipGajiHeader::where('periode', $periode)->where('mode', $mode)->exists()) {
            $modeLabel = $mode === 'gaji_13' ? 'Gaji 13' : 'Standard';

            return [
                'success' => false,
                'errors' => ['Slip gaji '.$modeLabel.' untuk periode '.$periode.' sudah ada'],
                'warnings' => [],
            ];
        }

        return [
            'success' => true,
            'errors' => [],
            'warnings' => [],
        ];
    }

    /**
     * Process and store import directly without preview
     */
    public function processAndStoreImport(UploadedFile $file, string $periode, int $userId, string $mode = 'standard'): array
    {
        try {
            DB::beginTransaction();

            // Check if periode and mode combination already exists
            if (SlipGajiHeader::where('periode', $periode)->where('mode', $mode)->exists()) {
                $modeLabel = $mode === 'gaji_13' ? 'Gaji 13' : 'Standard';

                return [
                    'success' => false,
                    'errors' => ['Slip gaji '.$modeLabel.' untuk periode '.$periode.' sudah ada'],
                ];
            }

            // Create header
            $header = SlipGajiHeader::create([
                'periode' => $periode,
                'mode' => $mode,
                'file_original' => $file->getClientOriginalName(),
                'uploaded_by' => $userId,
                'uploaded_at' => now(),
            ]);

            // Import data using Laravel Excel
            $import = new SlipGajiImport($header->id);
            Excel::import($import, $file);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Data slip gaji berhasil diimpor',
                'header_id' => $header->id,
            ];

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error processing and storing import: '.$e->getMessage());

            return [
                'success' => false,
                'errors' => ['Terjadi kesalahan saat memproses file: '.$e->getMessage()],
            ];
        }
    }

    public function processImport(UploadedFile $file, string $periode, int $uploadedBy, string $mode = 'standard'): array
    {
        DB::beginTransaction();

        try {
            // Store file
            $fileName = 'slip_gaji_'.$periode.'_'.time().'.'.$file->getClientOriginalExtension();
            $filePath = $file->storeAs('slip-gaji', $fileName, 'local');

            // Create header record
            $header = SlipGajiHeader::create([
                'periode' => $periode,
                'mode' => $mode,
                'file_original' => $file->getClientOriginalName(),
                'uploaded_by' => $uploadedBy,
                'uploaded_at' => now(),
            ]);

            // Import data
            $import = new SlipGajiImport($header->id);
            Excel::import($import, $file);

            // Get imported data for preview
            $details = SlipGajiDetail::where('header_id', $header->id)
                ->orderBy('nama')
                ->get();

            DB::commit();

            return [
                'success' => true,
                'header' => $header,
                'details' => $details,
                'total_records' => $details->count(),
                'errors' => [],
                'warnings' => [],
            ];

        } catch (Exception $e) {
            DB::rollBack();

            // Clean up uploaded file if exists
            if (isset($filePath) && Storage::exists($filePath)) {
                Storage::delete($filePath);
            }

            return [
                'success' => false,
                'errors' => ['Gagal memproses file: '.$e->getMessage()],
                'warnings' => [],
            ];
        }
    }

    public function confirmImport(int $headerId): array
    {
        DB::beginTransaction();

        try {
            $header = SlipGajiHeader::findOrFail($headerId);

            // Update all details status to 'confirmed'
            SlipGajiDetail::where('header_id', $headerId)
                ->update(['status' => 'confirmed']);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Data slip gaji berhasil dikonfirmasi',
            ];

        } catch (Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'errors' => ['Gagal mengkonfirmasi data: '.$e->getMessage()],
            ];
        }
    }

    public function cancelImport(int $headerId): array
    {
        DB::beginTransaction();

        try {
            $header = SlipGajiHeader::findOrFail($headerId);

            // Delete all details
            SlipGajiDetail::where('header_id', $headerId)->delete();

            // Delete header
            $header->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Import dibatalkan dan data dihapus',
            ];

        } catch (Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'errors' => ['Gagal membatalkan import: '.$e->getMessage()],
            ];
        }
    }

    public function generatePdfSlip(int $detailId): string
    {
        $detail = SlipGajiDetail::with(['header', 'header.uploader', 'employee', 'dosen'])
            ->findOrFail($detailId);

        // Get mode from header
        $mode = $detail->header->mode ?? 'standard';

        // Determine the template based on employee status and mode
        $status = strtolower(str_replace('_', '-', $detail->status));

        // For gaji_13 mode, use special templates
        if ($mode === 'gaji_13') {
            // Karyawan Magang uses Karyawan Kontrak template for gaji 13
            if ($detail->status === 'KARYAWAN_MAGANG') {
                $template = 'sdm.slip-gaji.pdf-templates.karyawan-kontrak-gaji13';
            } else {
                $template = 'sdm.slip-gaji.pdf-templates.'.$status.'-gaji13';
            }
        } else {
            // Standard mode
            $template = 'sdm.slip-gaji.pdf-templates.'.$status;
        }

        // Fallback to default template if specific template doesn't exist
        if (! \View::exists($template)) {
            $template = 'sdm.slip-gaji.pdf-template';
        }

        // Prepare image data for PDF
        $kopSuratPath = public_path('images/kopsurat.jpg');
        $ttdAuditaPath = public_path('images/ttd-audita.png');
        $ttdYantiPath = public_path('images/ttd-yanti.png');
        $ttdHrdPath = public_path('images/ttd-hrd.png');
        $ttdKaryawanPath = public_path('images/ttd-karyawan.png');

        $kopSuratDataUri = file_exists($kopSuratPath) ? 'data:image/jpeg;base64,'.base64_encode(file_get_contents($kopSuratPath)) : null;
        $ttdAuditaDataUri = file_exists($ttdAuditaPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($ttdAuditaPath)) : null;
        $ttdYantiDataUri = file_exists($ttdYantiPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($ttdYantiPath)) : null;
        $ttdHrdDataUri = file_exists($ttdHrdPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($ttdHrdPath)) : null;
        $ttdKaryawanDataUri = file_exists($ttdKaryawanPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($ttdKaryawanPath)) : null;

        $data = [
            'detail' => $detail,
            'header' => $detail->header,
            'periode_formatted' => $this->formatPeriode($detail->header->periode),
            'generated_at' => now()->format('d/m/Y H:i:s'),
            'kop_surat_data_uri' => $kopSuratDataUri,
            'ttd_audita_data_uri' => $ttdAuditaDataUri,
            'ttd_yanti_data_uri' => $ttdYantiDataUri,
            'ttd_hrd_data_uri' => $ttdHrdDataUri,
            'ttd_karyawan_data_uri' => $ttdKaryawanDataUri,
        ];

        // Render HTML view
        $html = view($template, $data)->render();

        // Generate PDF using Browsershot
        $browsershot = Browsershot::html($html)
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->noSandbox()
            ->showBackground()
            ->emulateMedia('print');

        if (env('PUPPETEER_NODE_BINARY')) {
            $browsershot->setNodeBinary(env('PUPPETEER_NODE_BINARY'));
        }

        if (env('PUPPETEER_CHROME_PATH')) {
            $browsershot->setChromePath(env('PUPPETEER_CHROME_PATH'));
        }

        $pdf = $browsershot->pdf();

        return $pdf;
    }

    public function generateBulkPdf(int $headerId): string
    {
        $header = SlipGajiHeader::with(['details.employee', 'details.dosen', 'uploader'])
            ->findOrFail($headerId);

        $data = [
            'header' => $header,
            'details' => $header->details()
                ->leftJoin('employees', 'slip_gaji_detail.nip', '=', 'employees.nip')
                ->leftJoin('dosens', 'slip_gaji_detail.nip', '=', 'dosens.nip')
                ->orderByRaw('COALESCE(employees.nama, dosens.nama, "")')
                ->select('slip_gaji_detail.*')
                ->get(),
            'periode_formatted' => $this->formatPeriode($header->periode),
            'generated_at' => now()->format('d/m/Y H:i:s'),
        ];

        // Render HTML view
        $html = view('sdm.slip-gaji.pdf-bulk', $data)->render();

        // Generate PDF using Browsershot
        $browsershot = Browsershot::html($html)
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->noSandbox()
            ->showBackground()
            ->emulateMedia('print');

        if (env('PUPPETEER_NODE_BINARY')) {
            $browsershot->setNodeBinary(env('PUPPETEER_NODE_BINARY'));
        }

        if (env('PUPPETEER_CHROME_PATH')) {
            $browsershot->setChromePath(env('PUPPETEER_CHROME_PATH'));
        }

        $pdf = $browsershot->pdf();

        return $pdf;
    }

    public function generatePdfFilename(SlipGajiDetail $detail): string
    {
        // Get employee name
        $nama = $detail->employee ? $detail->employee->nama : ($detail->dosen ? $detail->dosen->nama : 'Unknown');

        // Clean name for filename (remove special characters)
        $namaClean = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $nama));

        // Get month from periode (format: YYYY-MM-DD or similar)
        $periode = $detail->header->periode;
        $bulan = date('m', strtotime($periode));

        // Format: bulan-nip-namapegawai.pdf
        return sprintf('%s-%s-%s.pdf', $bulan, $detail->nip, $namaClean);
    }

    public function generateBulkPdfFilename(SlipGajiHeader $header): string
    {
        // Get month from periode
        $periode = $header->periode;
        $bulan = date('m', strtotime($periode));
        $tahun = date('Y', strtotime($periode));

        // Format: slip-gaji-bulan-tahun.pdf
        return sprintf('slip-gaji-%s-%s.pdf', $bulan, $tahun);
    }

    public function getSlipGajiList(array $filters = []): array
    {
        $query = SlipGajiHeader::with('uploader')
            ->withCount('details');

        // Filter by periode
        if (! empty($filters['periode'])) {
            $query->where('periode', 'like', '%'.$filters['periode'].'%');
        }

        // Filter by year
        if (! empty($filters['tahun'])) {
            $query->whereYear('periode', $filters['tahun']);
        }

        $headers = $query->orderBy('periode', 'desc')
            ->paginate(10);

        return [
            'headers' => $headers,
            'filters' => $filters,
        ];
    }

    public function getSlipGajiDetails(int $headerId, array $filters = []): array
    {
        $header = SlipGajiHeader::with('uploader')->findOrFail($headerId);

        $query = SlipGajiDetail::with(['employee', 'dosen'])->where('header_id', $headerId);

        // Filter by search (NIP or nama from relations)
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('slip_gaji_detail.nip', 'like', '%'.$filters['search'].'%')
                    ->orWhereHas('employee', function ($subQ) use ($filters) {
                        $subQ->where('nama', 'like', '%'.$filters['search'].'%')
                            ->orWhere('gelar_depan', 'like', '%'.$filters['search'].'%')
                            ->orWhere('gelar_belakang', 'like', '%'.$filters['search'].'%');
                    })
                    ->orWhereHas('dosen', function ($subQ) use ($filters) {
                        $subQ->where('nama', 'like', '%'.$filters['search'].'%')
                            ->orWhere('gelar_depan', 'like', '%'.$filters['search'].'%')
                            ->orWhere('gelar_belakang', 'like', '%'.$filters['search'].'%');
                    });
            });
        }

        // Filter by NIP
        if (! empty($filters['nip'])) {
            $query->where('nip', 'like', '%'.$filters['nip'].'%');
        }

        // Filter by nama (search in relations)
        if (! empty($filters['nama'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereHas('employee', function ($subQ) use ($filters) {
                    $subQ->where('nama', 'like', '%'.$filters['nama'].'%')
                        ->orWhere('gelar_depan', 'like', '%'.$filters['nama'].'%')
                        ->orWhere('gelar_belakang', 'like', '%'.$filters['nama'].'%');
                })
                    ->orWhereHas('dosen', function ($subQ) use ($filters) {
                        $subQ->where('nama', 'like', '%'.$filters['nama'].'%')
                            ->orWhere('gelar_depan', 'like', '%'.$filters['nama'].'%')
                            ->orWhere('gelar_belakang', 'like', '%'.$filters['nama'].'%');
                    });
            });
        }

        // Filter by status (aktif/tidak_aktif)
        if (! empty($filters['status'])) {
            if ($filters['status'] === 'aktif') {
                $query->where(function ($q) {
                    $q->whereHas('employee', function ($subQ) {
                        $subQ->where('status_aktif', true);
                    })
                        ->orWhereHas('dosen', function ($subQ) {
                            $subQ->where('status_aktif', true);
                        });
                });
            } elseif ($filters['status'] === 'tidak_aktif') {
                $query->where(function ($q) {
                    $q->whereHas('employee', function ($subQ) {
                        $subQ->where('status_aktif', false);
                    })
                        ->orWhereHas('dosen', function ($subQ) {
                            $subQ->where('status_aktif', false);
                        });
                });
            }
        }

        // Filter by no email (employees without email)
        if (! empty($filters['filterNoEmail']) && $filters['filterNoEmail']) {
            $query->where(function ($q) {
                $q->whereHas('employee', function ($subQ) {
                    $subQ->where(function ($subSubQ) {
                        $subSubQ->whereNull('email_kampus')
                            ->whereNull('email');
                    });
                })
                    ->orWhereHas('dosen', function ($subQ) {
                        $subQ->where(function ($subSubQ) {
                            $subSubQ->whereNull('email_kampus')
                                ->whereNull('email');
                        });
                    })
                    ->orWhere(function ($q) {
                        $q->whereDoesntHave('employee')
                            ->whereDoesntHave('dosen');
                    });
            });
        }

        // Apply sorting
        $sortBy = $filters['sort'] ?? 'nama';
        switch ($sortBy) {
            case 'nip':
                $query->orderBy('nip');
                break;
            case 'penerimaan_bersih_desc':
                $query->orderBy('penerimaan_bersih', 'desc');
                break;
            case 'penerimaan_bersih_asc':
                $query->orderBy('penerimaan_bersih', 'asc');
                break;
            case 'potongan_desc':
                $query->orderByRaw('(COALESCE(potongan_arisan, 0) + COALESCE(potongan_koperasi, 0) + COALESCE(potongan_lazmaal, 0) + COALESCE(potongan_bpjs_kesehatan, 0) + COALESCE(potongan_bpjs_ketenagakerjaan, 0) + COALESCE(potongan_bkd, 0) + COALESCE(pph21_terhutang, 0) + COALESCE(pph21_sudah_dipotong, 0) + COALESCE(pph21_kurang_dipotong, 0) + COALESCE(pajak, 0)) DESC');
                break;
            case 'potongan_asc':
                $query->orderByRaw('(COALESCE(potongan_arisan, 0) + COALESCE(potongan_koperasi, 0) + COALESCE(potongan_lazmaal, 0) + COALESCE(potongan_bpjs_kesehatan, 0) + COALESCE(potongan_bpjs_ketenagakerjaan, 0) + COALESCE(potongan_bkd, 0) + COALESCE(pph21_terhutang, 0) + COALESCE(pph21_sudah_dipotong, 0) + COALESCE(pph21_kurang_dipotong, 0) + COALESCE(pajak, 0)) ASC');
                break;
            default:
                // Use lightweight joins for name sorting to avoid expensive nested subqueries.
                $query
                    ->leftJoin('employees as sg_emp', 'sg_emp.nip', '=', 'slip_gaji_detail.nip')
                    ->leftJoin('dosens as sg_dsn', 'sg_dsn.nip', '=', 'slip_gaji_detail.nip')
                    ->orderByRaw('COALESCE(
                        NULLIF(TRIM(CONCAT(COALESCE(sg_emp.gelar_depan, ""), " ", COALESCE(sg_emp.nama, ""), CASE WHEN sg_emp.gelar_belakang IS NOT NULL AND sg_emp.gelar_belakang != "" THEN CONCAT(", ", sg_emp.gelar_belakang) ELSE "" END)), ""),
                        NULLIF(TRIM(CONCAT(COALESCE(sg_dsn.gelar_depan, ""), " ", COALESCE(sg_dsn.nama, ""), CASE WHEN sg_dsn.gelar_belakang IS NOT NULL AND sg_dsn.gelar_belakang != "" THEN CONCAT(", ", sg_dsn.gelar_belakang) ELSE "" END)), ""),
                        ""
                    ) ASC')
                    ->select('slip_gaji_detail.*');
                break;
        }

        // Get perPage from filters, default to 20
        $perPage = $filters['perPage'] ?? 20;
        $details = $query->paginate($perPage);

        return [
            'header' => $header,
            'details' => $details,
            'filters' => $filters,
        ];
    }

    public function getSlipGajiDetailById(int $detailId): ?SlipGajiDetail
    {
        return SlipGajiDetail::with(['employee', 'dosen'])->find($detailId);
    }

    private function formatPeriode(string $periode): string
    {
        $months = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];

        [$year, $month] = explode('-', $periode);

        return $months[$month].' '.$year;
    }
}
