<?php

namespace App\Http\Resources;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'author' => $this->user->name,
            'group' => $this->user->hasAccess('platform.systems.support') ? 'Support' : 'User',
            'message' => $this->message,
            'file' => isset($this->file) ? Storage::url($this->file) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
