<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class JoinCoupleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'invite_code' => 'required|string|size:6',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'invite_code.required' => 'Kode undangan wajib diisi.',
            'invite_code.size' => 'Kode undangan harus 6 karakter.',
            'invite_code.string' => 'Kode undangan harus berupa teks.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'invite_code' => strtoupper(trim($this->invite_code)),
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (Auth::user()?->couple_id) {
                $validator->errors()->add('user', 'Anda sudah terhubung dengan pasangan.');
            }
        });
    }
}
