<?php

namespace App\Http\Requests\Api\Events;

use Illuminate\Foundation\Http\FormRequest;

class CreateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'location' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:0',
            'timezone' => 'nullable|string|max:50',
            'dress_code' => 'nullable|string|max:255',
            'allow_reentry' => 'nullable|boolean',
            'ticket_types' => 'nullable|array',
            'ticket_types.*.name' => 'required_with:ticket_types|string|max:255',
            'ticket_types.*.price' => 'nullable|numeric|min:0',
            'ticket_types.*.validity_from' => 'nullable|date',
            'ticket_types.*.validity_to' => 'nullable|date',
            'ticket_types.*.usage_limit' => 'nullable|integer|min:1',
            'ticket_types.*.quota' => 'nullable|integer|min:0',
        ];
    }
}
