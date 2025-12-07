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
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120', // 5MB
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'location' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:0',
            'timezone' => 'nullable|string|max:50',
            'dress_code' => 'nullable|string|max:255',
            'allow_reentry' => 'nullable|boolean',
            'status' => 'nullable|in:draft,published,ongoing,completed,cancelled',

            // NEW: Social links
            'social_links' => 'nullable|array',
            'social_links.facebook' => 'nullable|url',
            'social_links.instagram' => 'nullable|url',
            'social_links.twitter' => 'nullable|url',
            'social_links.linkedin' => 'nullable|url',
            'social_links.tiktok' => 'nullable|url',
            'social_links.website' => 'nullable|url',

            // NEW: Gallery images
            'gallery_images' => 'nullable|array|max:5',
            'gallery_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',

            // Ticket types
            'ticket_types' => 'nullable|array',
            'ticket_types.*.name' => 'required_with:ticket_types|string|max:255',
            'ticket_types.*.price' => 'nullable|numeric|min:0',
            'ticket_types.*.validity_from' => 'nullable|date',
            'ticket_types.*.validity_to' => 'nullable|date',
            'ticket_types.*.usage_limit' => 'nullable|integer|min:1',
            'ticket_types.*.quota' => 'nullable|integer|min:0',

            // NEW: Gates
            'gates' => 'nullable|array',
            'gates.*.gate_id' => 'required_with:gates|uuid|exists:gates,id',
            'gates.*.agent_id' => 'nullable|uuid|exists:users,id',
            'gates.*.operational_status' => 'nullable|in:active,inactive,paused',
            'gates.*.ticket_type_names' => 'nullable|array',
            'gates.*.ticket_type_names.*' => 'string',
            'gates.*.schedule' => 'nullable|array',
            'gates.*.schedule.start_time' => 'nullable|date_format:H:i',
            'gates.*.schedule.end_time' => 'nullable|date_format:H:i',
            'gates.*.max_capacity' => 'nullable|integer|min:1',
        ];
    }
}
