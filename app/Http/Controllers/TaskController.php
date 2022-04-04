<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Status;
use App\Policies\TaskPolicy;
use App\Http\Resources\TaskResource;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Http\Requests\CreateTaskRequest;
use Illuminate\Http\Response;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Requests\UpdateTaskRequest;
use Illuminate\Contracts\Routing\ResponseFactory;
use Carbon\Carbon;

/**
 * Class TaskController
 * @package App\Http\Controllers
 */
class TaskController extends Controller
{

    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Task::class);
        /** @var Task|HasMany $tasks */
        $tasks = $request->user()->tasks();

        $tasks->filteredByStatus($request->get('status'));
        $tasks->filteredByTitle($request->get('title'));
        $tasks->filteredByPriority($request->get('priority'));
        $tasks->filteredBySort($request->get('sort_field', 'created_at'), $request->get('sort_direction', 'asc'));
        $tasks->with('status');

        return TaskResource::collection($tasks->paginate(5));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
    }

    /**
     * @param  CreateTaskRequest  $request
     * @return TaskResource
     */
    public function store(CreateTaskRequest $request)
    {
        $status = Status::where('name', "New")->first();

        $task = Task::create(['user_id' => $request->user()->id, 'status_id' => $status->id] + $request->validated());
        $task->statuses()->attach($status);

        return TaskResource::make($task);
    }

    /**
     * @param  Task  $task
     * @return TaskResource
     * @throws AuthorizationException
     */
    public function show(Task $task)
    {
        $this->authorize('view', $task);

        return TaskResource::make($task->load('statuses'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     * @param  UpdateTaskRequest  $request
     * @param  Task  $task
     * @return TaskResource|ResponseFactory|Response
     * @throws AuthorizationException
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $new_status = Status::where('name', $request->get('status'))->first();

        $task->fill($request->validated());

        if($new_status && !$task->status->is($new_status)) {
            if(!$task->canUpdateStatus($new_status)) {
                return response('Has Incomplete Subtasks!', 403);
            }

            if($new_status->name === 'Done' && $task->status->name == 'In Progress') {
                $task->work_time = $task->status->created_at->diffInSeconds(Carbon::now());
            }

            $task->status_id = $new_status->id;
            $task->statuses()->attach($new_status);
        }

        $task->save();
        $task->refresh();

        return TaskResource::make($task);
    }

    /**
     * Remove the specified resource from storage.
     * @param  Task  $task
     * @return ResponseFactory|Response|void
     * @throws AuthorizationException
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response('', 204);
    }
}
