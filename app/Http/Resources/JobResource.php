<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        // Format the attributes as key => value pairs
        $attributes = [];
        
        foreach ($this->attributes as $attributeValue) {
            $attributes[$attributeValue->attribute->name] = $attributeValue->value;
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'company_name' => $this->company_name,
            'salary_min' => $this->salary_min,
            'salary_max' => $this->salary_max,
            'is_remote' => $this->is_remote,
            'job_type' => $this->job_type,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            'languages' => $this->languages->pluck('name'),
            'locations' => $this->locations->map(function ($location) {
                return [
                    'city' => $location->city,
                    'state' => $location->state,
                    'country' => $location->country,
                ];
            }),
            'categories' => $this->categories->pluck('name'),
            
            'attributes' => $attributes,
        ];
    }
}