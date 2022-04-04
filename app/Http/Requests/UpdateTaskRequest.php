<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class CreateTaskRequest
 * @package App\Http\Requests
 */
class UpdateTaskRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'sometimes|string',
            'parent_id' => 'sometimes|exists:tasks,id,user_id,'. $this->user()->id,
            'status' => 'sometimes|string|exists:statuses,name',
            'priority' => 'sometimes|numeric',
            'description' => 'sometimes|string',
        ];
    }
}
