<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MeetingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'meeting_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'is_departure_transport_ready' => 'nullable|boolean',
            'is_return_transport_ready' => 'nullable|boolean',
            'is_rest_place_ready' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'meeting_date.required' => 'Tanggal pertemuan wajib diisi.',
            'meeting_date.date' => 'Tanggal pertemuan harus berupa format tanggal yang valid.',
            'location.string' => 'Lokasi harus berupa teks.',
            'location.max' => 'Lokasi maksimal 255 karakter.',
            'note.string' => 'Catatan harus berupa teks.',
        ];
    }
}
