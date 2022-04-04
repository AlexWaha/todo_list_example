<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Task;

/**
 * Class TaskResource
 * @package App\Http\Resources
 * @property Task $resource
 */
class TaskResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'parent_id' => $this->resource->parent_id,
            'description' => $this->resource->description,
            'priority' => $this->resource->priority,
            'deleted_at' =>$this->resource->deleted_at,
            'created_at' =>$this->resource->created_at,
            'updated_at' =>$this->resource->updated_at,
            'work_time' =>$this->resource->work_time,
            'status' => [
                'name' => $this->resource->status->name,
                'created_at' => $this->resource->status->created_at
            ],
            'status_history' => $this->whenLoaded('statuses')
        ];
    }
}
