<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=> $this->id,
            'name'=> $this->name,
            'email'=> $this->email,
            'salary'=> $this->salary,
            'position' => [
                'id' => $this->position?->id,
                'title' => $this->position?->title,
            ],
            'manager' => $this->manager ? [
                'id' => $this->manager->id,
                'name' => $this->manager->name,
            ] : null,
            'created_at' => $this->created_at,

            ];
    }
}
