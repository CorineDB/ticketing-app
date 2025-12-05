<?php

namespace App\Http\Requests\Api\Gates;

use Illuminate\Foundation\Http\FormRequest;

class CreateGateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'type' => 'required|in:entrance,exit,vip,other',
            'status' => 'nullable|in:active,pause,inactive',
        ];
    }
}
