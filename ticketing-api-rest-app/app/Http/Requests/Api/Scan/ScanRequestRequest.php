<?php

namespace App\Http\Requests\Api\Scan;

use Illuminate\Foundation\Http\FormRequest;

class ScanRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ticket_id' => 'required|uuid',
            'sig' => 'required|string',
        ];
    }
}
