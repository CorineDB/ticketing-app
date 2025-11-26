<?php

namespace App\Http\Requests\Api\Scan;

use Illuminate\Foundation\Http\FormRequest;

class ScanConfirmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'scan_session_token' => 'required|string',
            'scan_nonce' => 'required|string',
            'gate_id' => 'required|uuid|exists:gates,id',
            'agent_id' => 'required|uuid|exists:users,id',
            'action' => 'required|in:in,out,entry,exit',
        ];
    }
}
