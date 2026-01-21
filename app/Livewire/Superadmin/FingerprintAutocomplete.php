<?php

namespace App\Livewire\Superadmin;

use App\Models\FingerprintUserMapping;
use Livewire\Component;

class FingerprintAutocomplete extends Component
{
    public $fingerprint_pin;
    public $fingerprintSearch = '';
    public $showFingerprintAutocomplete = false;
    public $fingerprintUsers = [];
    public $isLoadingFingerprintUsers = false;

    protected $listeners = [
        'setFingerprintPin' => 'setPin',
        'clearFingerprintPin' => 'clearPin',
        'fingerprintUserSelected' => 'handleFingerprintUserSelected',
    ];

    public function mount($fingerprint_pin = null)
    {
        if ($fingerprint_pin) {
            $this->fingerprint_pin = $fingerprint_pin;
            // Cari nama user berdasarkan PIN untuk ditampilkan di search field
            $user = FingerprintUserMapping::findByPin($fingerprint_pin);
            $this->fingerprintSearch = $user ? $user->name : 'PIN: ' . $fingerprint_pin;
        }
    }

    public function render()
    {
        return view('livewire.superadmin.fingerprint-autocomplete');
    }

    public function loadFingerprintUsers()
    {
        $this->isLoadingFingerprintUsers = true;

        try {
            // Ambil data dari fingerprint_user_mappings
            $users = FingerprintUserMapping::active()
                ->when($this->fingerprintSearch, function($query) {
                    $query->where(function($q) {
                        $q->where('name', 'like', '%' . $this->fingerprintSearch . '%')
                          ->orWhere('pin', 'like', '%' . $this->fingerprintSearch . '%');
                    });
                })
                ->orderBy('name')
                ->get();

            // Konversi ke format array untuk compatibility
            $this->fingerprintUsers = $users->map(function($user) {
                return [
                    'pin' => $user->pin,
                    'name' => $user->name,
                    'is_active' => $user->is_active,
                ];
            })->toArray();
        } catch (\Exception $e) {
            $this->fingerprintUsers = [];
        }

        $this->isLoadingFingerprintUsers = false;
    }

    public function filterFingerprintUsers()
    {
        if (empty($this->fingerprintSearch)) {
            $this->showFingerprintAutocomplete = false;
            return;
        }

        $this->loadFingerprintUsers();
        $this->showFingerprintAutocomplete = true;
    }

    public function selectFingerprintUser($pin, $name)
    {
        $this->fingerprint_pin = $pin;
        $this->fingerprintSearch = $pin; // Tampilkan PIN di field search setelah dipilih
        $this->showFingerprintAutocomplete = false;
        
        // Try different emit methods
        $this->emitUp('fingerprintUserSelected', $pin);
        $this->emitUp('updateFingerprintPin', $pin);
        
        // Also try regular emit
        $this->emit('fingerprintUserSelected', $pin);
        $this->emit('updateFingerprintPin', $pin);
        
        // Try dispatch as well
        $this->dispatch('fingerprintUserSelected', $pin);
        $this->dispatch('updateFingerprintPin', $pin);
    }

    public function handleFingerprintUserSelected($pin)
    {
        $this->fingerprint_pin = $pin;
        $this->fingerprintSearch = $pin; // Tampilkan PIN di field search setelah dipilih
        $this->showFingerprintAutocomplete = false;
    }

    public function hideFingerprintAutocomplete()
    {
        $this->showFingerprintAutocomplete = false;
    }

    public function setPin($pin)
    {
        $this->fingerprint_pin = $pin;
        $this->fingerprintSearch = $pin ? 'PIN: ' . $pin : '';
    }

    public function clearPin()
    {
        $this->fingerprint_pin = '';
        $this->fingerprintSearch = '';
        $this->showFingerprintAutocomplete = false;
    }

}
