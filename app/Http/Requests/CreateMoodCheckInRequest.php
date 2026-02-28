<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateMoodCheckInRequest extends FormRequest
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
            'mood' => 'required|in:happy,sad,angry,loved,tired,anxious,excited',
            'note' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom error messages for validator.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mood.required' => 'Mood harus dipilih',
            'mood.in' => 'Mood yang dipilih tidak valid',
            'note.max' => 'Catatan maksimal 500 karakter',
        ];
    }
}
