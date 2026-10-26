<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IngestEventsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'events'                  => 'required|array|min:1|max:50',
            'events.*.event_id'       => 'required|uuid',
            'events.*.event_name'     => 'required|string|max:100',
            'events.*.event_category' => 'nullable|string|max:50',
            'events.*.properties'     => 'nullable|array',
            'events.*.anonymous_id'   => 'required|string|max:100',
            'events.*.session_id'     => 'required|string|max:100',
            'events.*.user_id'        => 'nullable|integer',
            'events.*.page_url'       => 'nullable|string|max:2048',
            'events.*.user_agent'     => 'nullable|string|max:512',
            'events.*.ip'             => 'nullable|ip',
            'events.*.occurred_at'    => 'required|date',
        ];
    }
}