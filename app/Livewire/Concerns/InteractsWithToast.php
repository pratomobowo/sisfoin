<?php

namespace App\Livewire\Concerns;

trait InteractsWithToast
{
    protected function flashToast(string $type, string $message): void
    {
        session()->flash($type, $message);
        $this->dispatch('toast-show', message: $message, type: $type);
    }

    protected function toastSuccess(string $message): void
    {
        $this->flashToast('success', $message);
    }

    protected function toastError(string $message): void
    {
        $this->flashToast('error', $message);
    }

    protected function toastWarning(string $message): void
    {
        $this->flashToast('warning', $message);
    }

    protected function toastInfo(string $message): void
    {
        $this->flashToast('info', $message);
    }
}
