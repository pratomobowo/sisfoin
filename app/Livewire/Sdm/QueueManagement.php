<?php

namespace App\Livewire\Sdm;

use Livewire\Component;
use Illuminate\Support\Facades\Log;

class QueueManagement extends Component
{
    public $isRunning = false;
    public $pids = [];
    public $lastUpdate = '';

    public function mount()
    {
        $this->checkStatus();
    }

    public function checkStatus()
    {
        $command = "ps aux | grep 'artisan queue:work' | grep -v grep";
        $output = [];
        \exec($command, $output);

        $this->isRunning = !empty($output);
        $this->pids = [];

        if ($this->isRunning) {
            foreach ($output as $line) {
                // Parse PID from ps aux output (usually second column)
                $parts = preg_split('/\s+/', trim($line));
                if (isset($parts[1])) {
                    $this->pids[] = $parts[1];
                }
            }
        }

        $this->lastUpdate = now()->format('H:i:s');
    }

    public function startQueue()
    {
        try {
            // Path to artisan
            $artisan = base_path('artisan');
            $php = PHP_BINARY;

            // Command to run in background
            // We use the same parameters as the user used before
            $command = "nohup {$php} {$artisan} queue:work --queue=emails,default --tries=3 --timeout=300 > /dev/null 2>&1 & echo $!";

            Log::info("Starting queue worker: " . $command);

            $pid = \shell_exec($command);

            sleep(1); // Give it a second to start
            $this->checkStatus();

            if ($this->isRunning) {
                session()->flash('success', 'Email queue worker berhasil dijalankan (PID: ' . trim($pid) . ')');
            } else {
                session()->flash('error', 'Gagal menjalankan queue worker. Cek log untuk detail.');
            }
        } catch (\Exception $e) {
            Log::error("Failed to start queue: " . $e->getMessage());
            session()->flash('error', 'Gagal menjalankan queue: ' . $e->getMessage());
        }
    }

    public function stopQueue()
    {
        try {
            $this->checkStatus();

            if (!$this->isRunning) {
                session()->flash('warning', 'Queue worker tidak sedang berjalan.');
                return;
            }

            foreach ($this->pids as $pid) {
                $command = "kill -9 {$pid}";
                \exec($command);
                Log::info("Stopped queue worker PID: " . $pid);
            }

            sleep(1);
            $this->checkStatus();

            session()->flash('success', 'Email queue worker berhasil dihentikan.');
        } catch (\Exception $e) {
            Log::error("Failed to stop queue: " . $e->getMessage());
            session()->flash('error', 'Gagal menghentikan queue: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.sdm.queue-management');
    }
}
