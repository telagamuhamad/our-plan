<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SavingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'exists:saving_categories,id'],
            'name' => ['required', 'string'],
            'target_amount' => ['required_if:is_shared,0', 'numeric', 'nullable'],
            'is_shared' => ['boolean'],
            'target_date' => ['nullable', 'date', 'after:today'],
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
            'target_date.after' => 'Target date harus lebih dari hari ini.',
            'target_amount.required_if' => 'Target amount wajib diisi untuk tabungan dengan tujuan.',
        ];
    }
}
