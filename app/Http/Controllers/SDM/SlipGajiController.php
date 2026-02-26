<?php

namespace App\Http\Controllers\SDM;

use App\Exports\SlipGajiTemplateExport;
use App\Http\Controllers\Controller;
use App\Models\SlipGajiDetail;
use App\Models\SlipGajiHeader;
use App\Services\SlipGajiEmailService;
use App\Services\SlipGajiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Activitylog\Models\Activity;

class SlipGajiController extends Controller
{
    private $slipGajiService;

    private $slipGajiEmailService;

    public function __construct(SlipGajiService $slipGajiService, SlipGajiEmailService $slipGajiEmailService)
    {
        $this->slipGajiService = $slipGajiService;
        $this->slipGajiEmailService = $slipGajiEmailService;
        $this->middleware('auth');
        $this->middleware('role:super-admin|admin-sdm|sekretariat')->except(['staffIndex', 'staffShow', 'staffDownloadPdf']);
        $this->middleware('role:staff')->only(['staffIndex', 'staffShow', 'staffDownloadPdf']);
    }

    public function index(Request $request)
    {
        // Data sekarang dihandle oleh komponen Livewire
        return view('sdm.slip-gaji.index');
    }

    public function create()
    {
        return view('sdm.slip-gaji.create');
    }

    public function upload()
    {
        return view('sdm.slip-gaji.upload');
    }

    public function downloadTemplate()
    {
        try {
            $fileName = 'template_slip_gaji_'.date('Y-m-d').'.xlsx';

            activity()
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'download_template'])
                ->log('Download template slip gaji');

            return Excel::download(new SlipGajiTemplateExport, $fileName);
        } catch (\Exception $e) {
            Log::error('Error downloading template: '.$e->getMessage());

            return back()->with('error', 'Gagal mendownload template');
        }
    }

    public function processUpload(Request $request)
    {
        \Log::info('=== START processUpload ===');
        \Log::info('Upload request received', [
            'has_file' => $request->hasFile('file'),
            'file' => $request->hasFile('file') ? $request->file('file')->getClientOriginalName() : null,
            'periode' => $request->periode,
            'all_inputs' => $request->all(),
            'method' => $request->method(),
            'uri' => $request->getRequestUri(),
        ]);

        // Cek apakah request benar-benar POST
        if (! $request->isMethod('post')) {
            \Log::error('Request is not POST', [
                'method' => $request->method(),
            ]);

            return back()->with('error', 'Invalid request method');
        }

        // Cek apakah ada file
        if (! $request->hasFile('file')) {
            \Log::error('No file uploaded');

            return back()->with('error', 'Tidak ada file yang diupload');
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB
            'bulan' => 'required|string|in:01,02,03,04,05,06,07,08,09,10,11,12',
            'tahun' => 'required|integer|min:2020|max:'.(date('Y') + 1),
            'mode' => 'required|in:standard,gaji_13', // New: mode validation
        ], [
            'file.required' => 'File Excel wajib diupload',
            'file.mimes' => 'File harus berformat Excel (.xlsx atau .xls)',
            'file.max' => 'Ukuran file maksimal 10MB',
            'bulan.required' => 'Bulan wajib dipilih',
            'bulan.in' => 'Bulan tidak valid',
            'tahun.required' => 'Tahun wajib dipilih',
            'tahun.integer' => 'Tahun harus berupa angka',
            'tahun.min' => 'Tahun minimal 2020',
            'tahun.max' => 'Tahun tidak valid',
            'mode.required' => 'Mode slip gaji wajib dipilih', // New
            'mode.in' => 'Mode slip gaji tidak valid', // New
        ]);

        // Buat periode dengan format YYYY-MM menggunakan tahun yang dipilih
        $periode = $request->tahun.'-'.$request->bulan;

        \Log::info('Validation passed');

        try {
            // Process and store import directly
            $result = $this->slipGajiService->processAndStoreImport(
                $request->file('file'),
                $periode,
                Auth::id(),
                $request->input('mode', 'standard') // New: pass mode parameter
            );

            \Log::info('Process and store import result', ['success' => $result['success']]);

            if (! $result['success']) {
                \Log::info('Import errors', ['errors' => $result['errors']]);

                return back()
                    ->withErrors($result['errors'])
                    ->withInput();
            }

            if (! empty($result['warnings'])) {
                session()->flash('warning', implode(' | ', $result['warnings']));
            }

            // Log activity
            activity()
                ->causedBy(Auth::user())
                ->withProperties([
                    'periode' => $periode,
                    'file_name' => $request->file('file')->getClientOriginalName(),
                ])
                ->log('Upload slip gaji berhasil');

            \Log::info('Redirecting to index after successful upload', ['header_id' => $result['header_id']]);

            return redirect()->route('sdm.slip-gaji.index')
                ->with('success', 'File slip gaji berhasil diupload dan diproses untuk periode '.$periode);

        } catch (\Exception $e) {
            \Log::error('Error processing upload: '.$e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->with('error', 'Terjadi kesalahan saat memproses file: '.$e->getMessage())
                ->withInput();
        }
    }

    // Preview and confirm methods removed - direct import implemented

    public function cancel(SlipGajiHeader $header)
    {
        try {
            $periode = $header->periode;
            $result = $this->slipGajiService->cancelImport($header->id);

            if (! $result['success']) {
                return back()->withErrors($result['errors']);
            }

            // Log activity
            activity()
                ->causedBy(Auth::user())
                ->withProperties([
                    'periode' => $periode,
                    'action' => 'cancel_import',
                ])
                ->log('Batalkan import slip gaji');

            return redirect()->route('sdm.slip-gaji.index')
                ->with('success', $result['message']);

        } catch (\Exception $e) {
            Log::error('Error canceling import: '.$e->getMessage());

            return back()->with('error', 'Terjadi kesalahan saat membatalkan import');
        }
    }

    public function show(Request $request, SlipGajiHeader $header)
    {
        return view('sdm.slip-gaji.show-livewire', compact('header'));
    }

    public function edit(SlipGajiDetail $detail)
    {
        try {
            // Log activity
            activity()
                ->causedBy(Auth::user())
                ->performedOn($detail)
                ->withProperties([
                    'nip' => $detail->nip,
                    'nama' => $detail->nama,
                    'periode' => $detail->header->periode,
                ])
                ->log('Akses form edit slip gaji');

            return view('sdm.slip-gaji.edit', compact('detail'));
        } catch (\Exception $e) {
            Log::error('Error accessing edit form: '.$e->getMessage());

            return back()->with('error', 'Terjadi kesalahan saat mengakses form edit');
        }
    }

    public function update(Request $request, SlipGajiDetail $detail)
    {
        $request->validate([
            'nip' => 'required|string|max:50',
            'status' => 'required|string|max:50',
            // PENDAPATAN
            'gaji_pokok' => 'nullable|numeric|min:0',
            'honor_tetap' => 'nullable|numeric|min:0',
            'tpp' => 'nullable|numeric|min:0',
            'insentif_golongan' => 'nullable|numeric|min:0',
            'tunjangan_keluarga' => 'nullable|numeric|min:0',
            'tunjangan_kemahalan' => 'nullable|numeric|min:0',
            'tunjangan_pmb' => 'nullable|numeric|min:0',
            'tunjangan_golongan' => 'nullable|numeric|min:0',
            'tunjangan_masa_kerja' => 'nullable|numeric|min:0',
            'transport' => 'nullable|numeric|min:0',
            'tunjangan_kesehatan' => 'nullable|numeric|min:0',
            'tunjangan_rumah' => 'nullable|numeric|min:0',
            'tunjangan_pendidikan' => 'nullable|numeric|min:0',
            'tunjangan_struktural' => 'nullable|numeric|min:0',
            'tunjangan_fungsional' => 'nullable|numeric|min:0',
            'beban_manajemen' => 'nullable|numeric|min:0',
            'honor_tunai' => 'nullable|numeric|min:0',
            'penerimaan_kotor' => 'nullable|numeric|min:0',

            // POTONGAN
            'potongan_arisan' => 'nullable|numeric|min:0',
            'potongan_koperasi' => 'nullable|numeric|min:0',
            'potongan_lazmaal' => 'nullable|numeric|min:0',
            'potongan_bpjs_kesehatan' => 'nullable|numeric|min:0',
            'potongan_bpjs_ketenagakerjaan' => 'nullable|numeric|min:0',
            'potongan_bkd' => 'nullable|numeric|min:0',

            // PAJAK
            'pajak' => 'nullable|numeric|min:0',
            'pph21_terhutang' => 'nullable|numeric|min:0',
            'pph21_sudah_dipotong' => 'nullable|numeric|min:0',
            'pph21_kurang_dipotong' => 'nullable|numeric|min:0',

            // TOTAL
            'penerimaan_bersih' => 'nullable|numeric|min:0',
        ], [
            'nip.required' => 'NIP wajib diisi',
            'gaji_pokok.numeric' => 'Gaji pokok harus berupa angka',
            '*.numeric' => 'Field harus berupa angka',
            '*.min' => 'Nilai tidak boleh negatif',
        ]);

        try {
            // Store original data for logging
            $originalData = $detail->toArray();

            // Update detail data
            $detail->update([
                'nip' => $request->nip,
                'status' => $request->status,
                // PENDAPATAN
                'gaji_pokok' => $request->gaji_pokok ?? 0,
                'honor_tetap' => $request->honor_tetap ?? 0,
                'tpp' => $request->tpp ?? 0,
                'insentif_golongan' => $request->insentif_golongan ?? 0,
                'tunjangan_keluarga' => $request->tunjangan_keluarga ?? 0,
                'tunjangan_kemahalan' => $request->tunjangan_kemahalan ?? 0,
                'tunjangan_pmb' => $request->tunjangan_pmb ?? 0,
                'tunjangan_golongan' => $request->tunjangan_golongan ?? 0,
                'tunjangan_masa_kerja' => $request->tunjangan_masa_kerja ?? 0,
                'transport' => $request->transport ?? 0,
                'tunjangan_kesehatan' => $request->tunjangan_kesehatan ?? 0,
                'tunjangan_rumah' => $request->tunjangan_rumah ?? 0,
                'tunjangan_pendidikan' => $request->tunjangan_pendidikan ?? 0,
                'tunjangan_struktural' => $request->tunjangan_struktural ?? 0,
                'tunjangan_fungsional' => $request->tunjangan_fungsional ?? 0,
                'beban_manajemen' => $request->beban_manajemen ?? 0,
                'honor_tunai' => $request->honor_tunai ?? 0,
                'penerimaan_kotor' => $request->penerimaan_kotor ?? 0,

                // POTONGAN
                'potongan_arisan' => $request->potongan_arisan ?? 0,
                'potongan_koperasi' => $request->potongan_koperasi ?? 0,
                'potongan_lazmaal' => $request->potongan_lazmaal ?? 0,
                'potongan_bpjs_kesehatan' => $request->potongan_bpjs_kesehatan ?? 0,
                'potongan_bpjs_ketenagakerjaan' => $request->potongan_bpjs_ketenagakerjaan ?? 0,
                'potongan_bkd' => $request->potongan_bkd ?? 0,

                // PAJAK
                'pajak' => $request->pajak ?? 0,
                'pph21_terhutang' => $request->pph21_terhutang ?? 0,
                'pph21_sudah_dipotong' => $request->pph21_sudah_dipotong ?? 0,
                'pph21_kurang_dipotong' => $request->pph21_kurang_dipotong ?? 0,

                // TOTAL
                'penerimaan_bersih' => $request->penerimaan_bersih ?? 0,
            ]);

            // Log activity
            activity()
                ->causedBy(Auth::user())
                ->performedOn($detail)
                ->withProperties([
                    'original' => $originalData,
                    'updated' => $detail->fresh()->toArray(),
                    'periode' => $detail->header->periode,
                ])
                ->log('Update data slip gaji');

            return redirect()->route('sdm.slip-gaji.show', $detail->header)
                ->with('success', 'Data slip gaji berhasil diperbarui');

        } catch (\Exception $e) {
            Log::error('Error updating slip gaji: '.$e->getMessage());

            return back()
                ->with('error', 'Terjadi kesalahan saat memperbarui data: '.$e->getMessage())
                ->withInput();
        }
    }

    public function previewPdfSlip(SlipGajiDetail $detail)
    {
        try {
            $pdfContent = $this->slipGajiService->generatePdfSlip($detail->id);
            $filename = $this->slipGajiService->generatePdfFilename($detail);

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($detail)
                ->withProperties(['action' => 'preview_pdf'])
                ->log('Preview PDF slip gaji untuk NIP: '.$detail->nip);

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="'.$filename.'"');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menampilkan PDF: '.$e->getMessage());
        }
    }

    public function showPdfSlip(SlipGajiDetail $detail)
    {
        try {
            $pdfContent = $this->slipGajiService->generatePdfSlip($detail->id);
            $filename = $this->slipGajiService->generatePdfFilename($detail);

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($detail)
                ->withProperties(['action' => 'show_pdf'])
                ->log('Menampilkan PDF slip gaji untuk NIP: '.$detail->nip);

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="'.$filename.'"');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menampilkan PDF: '.$e->getMessage());
        }
    }

    public function downloadPdfSlip(SlipGajiDetail $detail)
    {
        try {
            $pdfContent = $this->slipGajiService->generatePdfSlip($detail->id);
            $filename = $this->slipGajiService->generatePdfFilename($detail);

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($detail)
                ->withProperties(['action' => 'download_pdf'])
                ->log('Download PDF slip gaji untuk NIP: '.$detail->nip);

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mendownload PDF: '.$e->getMessage());
        }
    }

    public function downloadBulkPdf(SlipGajiHeader $header)
    {
        try {
            $pdfContent = $this->slipGajiService->generateBulkPdf($header->id);
            $filename = $this->slipGajiService->generateBulkPdfFilename($header);

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($header)
                ->withProperties(['action' => 'download_bulk_pdf'])
                ->log('Download bulk PDF slip gaji untuk periode: '.$header->periode);

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mendownload PDF: '.$e->getMessage());
        }
    }

    /**
     * Send bulk email for slip gaji
     */
    public function sendBulkEmail(Request $request, SlipGajiHeader $header)
    {
        try {
            $selectedDetails = $request->input('selected_details', []);

            $result = $this->slipGajiEmailService->sendBulkEmail($header->id, $selectedDetails);

            if (! $result['success']) {
                return back()->with('error', $result['message']);
            }

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($header)
                ->withProperties([
                    'action' => 'send_bulk_email',
                    'valid_recipients' => $result['valid_recipients'],
                    'invalid_recipients' => $result['invalid_recipients'],
                ])
                ->log('Kirim bulk email slip gaji untuk periode: '.$header->periode);

            return back()->with('success', $result['message']);

        } catch (\Exception $e) {
            Log::error('Error sending bulk email: '.$e->getMessage());

            return back()->with('error', 'Terjadi kesalahan saat mengirim email: '.$e->getMessage());
        }
    }

    /**
     * Show email logs for slip gaji header
     */
    public function showEmailLogs(Request $request, SlipGajiHeader $header)
    {
        try {
            $filters = $request->only(['status', 'search', 'perPage']);
            $result = $this->slipGajiEmailService->getEmailLogs($header->id, $filters);
            $stats = $this->slipGajiEmailService->getEmailStats($header->id);

            return view('sdm.slip-gaji.email-logs', [
                'header' => $header,
                'email_logs' => $result['email_logs'],
                'filters' => $result['filters'],
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            Log::error('Error showing email logs: '.$e->getMessage());

            return back()->with('error', 'Terjadi kesalahan saat menampilkan log email: '.$e->getMessage());
        }
    }

    /**
     * Retry failed emails for slip gaji header
     */
    public function retryFailedEmails(SlipGajiHeader $header)
    {
        try {
            $result = $this->slipGajiEmailService->retryFailedEmails($header->id);

            if (! $result['success']) {
                return back()->with('error', $result['message']);
            }

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($header)
                ->withProperties([
                    'action' => 'retry_failed_emails',
                    'retry_count' => $result['retry_count'],
                ])
                ->log('Retry failed emails untuk periode: '.$header->periode);

            return back()->with('success', $result['message']);

        } catch (\Exception $e) {
            Log::error('Error retrying failed emails: '.$e->getMessage());

            return back()->with('error', 'Terjadi kesalahan saat mengulangi pengiriman email: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified slip gaji from storage.
     */
    public function destroy($id)
    {
        try {
            $header = SlipGajiHeader::findOrFail($id);

            // Store header info for logging before deletion
            $periode = $header->periode;

            // Delete the slip gaji using service
            $this->slipGajiService->cancelImport($id);

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->withProperties(['action' => 'delete_slip_gaji', 'periode' => $periode])
                ->log('Menghapus slip gaji periode: '.$periode);

            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Slip gaji berhasil dihapus']);
            }

            return redirect()->route('sdm.slip-gaji.index')->with('success', 'Slip gaji berhasil dihapus');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal menghapus slip gaji: '.$e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'Gagal menghapus slip gaji: '.$e->getMessage());
        }
    }

    /**
     * Download PDF for staff slip gaji
     */
    public function staffDownloadPdf($header_id)
    {
        $user = Auth::user();
        $nip = $user->nip;

        if (! $nip) {
            abort(403, 'Data NIP tidak ditemukan');
        }

        // Find slip gaji detail by header_id and nip
        $detail = SlipGajiDetail::where('header_id', $header_id)
            ->where('nip', $nip)
            ->with(['header', 'employee', 'dosen'])
            ->firstOrFail();

        try {
            $pdfContent = $this->slipGajiService->generatePdfSlip($detail->id);
            $filename = $this->slipGajiService->generatePdfFilename($detail);

            activity()
                ->causedBy($user)
                ->performedOn($detail)
                ->withProperties(['action' => 'download_pdf'])
                ->log('Staff downloaded slip gaji PDF');

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
        } catch (\Exception $e) {
            Log::error('Error generating staff PDF: '.$e->getMessage());

            return redirect()->back()->with('error', 'Gagal mengunduh slip gaji: '.$e->getMessage());
        }
    }
}
