<?php

namespace App\Http\Requests\Support;

use App\Models\Ticket;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class TicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'department' => ['required', Rule::in(array_keys(Ticket::DEPARTMENT))],
        ];
    }
}
