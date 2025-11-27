<?php

namespace App\Http\Requests\Api\Tickets;

use Illuminate\Foundation\Http\FormRequest;

class TicketPurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ticket_type_id' => 'required|uuid|exists:ticket_types,id',
            'quantity' => 'required|integer|min:1|max:10',
            'customer.firstname' => 'required|string|max:255',
            'customer.lastname' => 'required|string|max:255',
            'customer.email' => 'required|email|max:255',
            'customer.phone_number' => 'required|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'ticket_type_id.required' => 'Le type de ticket est requis.',
            'ticket_type_id.exists' => 'Le type de ticket sélectionné n\'existe pas.',
            'quantity.required' => 'La quantité est requise.',
            'quantity.min' => 'Vous devez acheter au moins 1 ticket.',
            'quantity.max' => 'Vous ne pouvez pas acheter plus de 10 tickets à la fois.',
            'customer.firstname.required' => 'Le prénom est requis.',
            'customer.lastname.required' => 'Le nom est requis.',
            'customer.email.required' => 'L\'email est requis.',
            'customer.email.email' => 'L\'email doit être valide.',
            'customer.phone_number.required' => 'Le numéro de téléphone est requis.',
        ];
    }
}
