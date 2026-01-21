<?php

namespace App\Livewire;

use Livewire\Component;

class RoleSwitcher extends Component
{
    public $currentRole;

    public $availableRoles;

    public function mount()
    {
        $this->currentRole = getActiveRole();
        $this->availableRoles = getUserRoles();
    }

    public function switchRole($role)
    {
        if (! canSwitchToRole($role)) {
            session()->flash('error', 'Anda tidak memiliki izin untuk beralih ke peran ini.');

            return;
        }

        setActiveRole($role);
        $this->currentRole = $role;

        session()->flash('success', "Berhasil beralih ke peran {$role}.");

        // Redirect to refresh the page and apply new permissions
        return redirect()->to(request()->header('Referer') ?: '/');
    }

    public function render()
    {
        return view('livewire.role-switcher');
    }
}
