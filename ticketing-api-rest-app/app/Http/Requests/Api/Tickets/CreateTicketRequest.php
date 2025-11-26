<?php

namespace App\Http\Requests\Api\Tickets;

use Illuminate\Foundation\Http\FormRequest;

class CreateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_id' => 'required|uuid|exists:events,id',
            'ticket_type_id' => 'nullable|uuid|exists:ticket_types,id',
            'buyer_name' => 'nullable|string|max:255',
            'buyer_email' => 'nullable|email|max:255',
            'buyer_phone' => 'nullable|string|max:20',
            'quantity' => 'nullable|integer|min:1|max:100',
        ];
    }
}
