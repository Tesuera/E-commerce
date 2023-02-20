<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'id' => $this->id,
            'name' => $this->name,
            'unique_id' => $this->unique_id,
            'email' => $this->email,
            'role' => $this->role,
            'profile' => asset('storage/profiles/'. $this->profile),
            'join_date' => $this->created_at->format('jS M Y'),
            'date' => $this->created_at->format('d/m/Y')
        ];
    }
}
