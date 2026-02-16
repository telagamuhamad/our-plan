<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreatePostRequest extends FormRequest
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
        $postType = $this->input('post_type', 'text');
        $rules = [
            'post_type' => 'required|in:text,photo,voice_note',
            'content' => 'nullable|string|max:5000',
        ];

        // File validation based on post type
        if ($postType === 'photo') {
            $rules['attachment'] = 'required|file|mimes:jpeg,jpg,png,gif,webp|max:10240'; // 10MB
        } elseif ($postType === 'voice_note') {
            $rules['attachment'] = 'required|file|mimes:mp3,wav,m4a,aac|max:5120'; // 5MB
        } else {
            $rules['attachment'] = 'nullable';
        }

        // Content is required for text posts
        if ($postType === 'text') {
            $rules['content'] = 'required|string|max:5000';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'post_type.required' => 'Tipe postingan wajib diisi.',
            'post_type.in' => 'Tipe postingan tidak valid.',
            'content.required' => 'Konten tidak boleh kosong.',
            'content.max' => 'Konten terlalu panjang. Maksimal 5000 karakter.',
            'attachment.required' => 'File lampiran wajib diunggah.',
            'attachment.mimes' => 'Format file tidak didukung.',
            'attachment.max' => 'Ukuran file terlalu besar. Maksimal 10MB untuk foto dan 5MB untuk audio.',
        ];
    }
}
