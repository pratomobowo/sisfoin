<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('users.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'nip' => ['nullable', 'string', 'max:50'],
            'employee_type' => ['nullable', 'string', 'in:employee,dosen'],
            'employee_id' => ['nullable', 'string', 'max:50'],
            'fingerprint_pin' => ['nullable', 'string', 'max:10'],
            'fingerprint_enabled' => ['boolean'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,id'],
        ];
    }

    /**
     * Get the custom error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama pengguna wajib diisi',
            'name.max' => 'Nama pengguna maksimal 255 karakter',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'nip.max' => 'NIP maksimal 50 karakter',
            'employee_type.in' => 'Tipe karyawan harus employee atau dosen',
            'employee_id.max' => 'ID karyawan maksimal 50 karakter',
            'fingerprint_pin.max' => 'PIN fingerprint maksimal 10 karakter',
            'roles.required' => 'Role wajib dipilih minimal 1',
            'roles.min' => 'Role wajib dipilih minimal 1',
            'roles.*.exists' => 'Role yang dipilih tidak valid',
        ];
    }

    /**
     * Get the custom attributes for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nama',
            'email' => 'Email',
            'password' => 'Password',
            'password_confirmation' => 'Konfirmasi Password',
            'nip' => 'NIP',
            'employee_type' => 'Tipe Karyawan',
            'employee_id' => 'ID Karyawan',
            'fingerprint_pin' => 'PIN Fingerprint',
            'fingerprint_enabled' => 'Fingerprint Diaktifkan',
            'roles' => 'Role',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Convert boolean values
        if ($this->has('fingerprint_enabled')) {
            $this->merge([
                'fingerprint_enabled' => filter_var($this->fingerprint_enabled, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
