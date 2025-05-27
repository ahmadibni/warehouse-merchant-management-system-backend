<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WarehouseRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:warehouses,name,' . $this->route('warehouse'),
            'phone' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'photo' => [
                $this->isMethod('POST') ? 'required' : 'nullable',
                'image',
                'mimes:png,jpg,jpeg',
                'max:2048',
            ],
        ];
    }
}
