<?php

namespace App\Http\Requests\Support;

use Illuminate\Foundation\Http\FormRequest;

class MessageRequest extends FormRequest
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
            'text' => ['required', 'string', 'max:1024'],
            'file' => ['nullable', 'mimes:pdf,png,jpg,gif', 'max:5120']
        ];
    }
}
