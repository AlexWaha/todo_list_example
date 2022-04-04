<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Task
 *
 * @property int $id
 * @property int $parent_id
 * @property int $user_id
 * @property string $title
 * @property string|null $description
 * @property int $priority
 * @property int $status_id
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Status $status
 * @property-read Collection|Status[] $statuses
 * @property-read int|null $statuses_count
 * @method static Builder|Task filteredByPriority($priority)
 * @method static Builder|Task filteredBySort($field, $direction)
 * @method static Builder|Task filteredByStatus($status)
 * @method static Builder|Task filteredByTitle($title)
 * @method static Builder|Task newModelQuery()
 * @method static Builder|Task newQuery()
 * @method static \Illuminate\Database\Query\Builder|Task onlyTrashed()
 * @method static Builder|Task query()
 * @method static Builder|Task whereCreatedAt($value)
 * @method static Builder|Task whereDeletedAt($value)
 * @method static Builder|Task whereDescription($value)
 * @method static Builder|Task whereId($value)
 * @method static Builder|Task whereParentId($value)
 * @method static Builder|Task wherePriority($value)
 * @method static Builder|Task whereStatusId($value)
 * @method static Builder|Task whereTitle($value)
 * @method static Builder|Task whereUpdatedAt($value)
 * @method static Builder|Task whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|Task withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Task withoutTrashed()
 * @mixin Eloquent
 * @property-read Collection|Task[] $subTasks
 * @property-read int|null $sub_tasks_count
 * @property int|null $work_time
 * @method static Builder|Task whereWorkTime($value)
 */
class Task extends Model
{

    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['parent_id', 'user_id', 'title', 'description', 'priority', 'status_id'];

    /**
     * @return BelongsToMany
     */
    public function statuses()
    {
        return $this->belongsToMany(Status::class, 'task_statuses')->withTimestamps()->orderByPivot('created_at',
                'desc');
    }

    /**
     * @return BelongsTo
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * @return HasMany
     */
    public function subTasks()
    {
        return $this->hasMany(Task::class, 'parent_id');
    }

    /**
     * @param $query
     * @param $status
     */
    public function scopeFilteredByStatus(Builder $query, $status)
    {
        $query->when($status, function (Builder $query) use ($status) {
            return $query->whereHas('status', function (Builder $query) use ($status) {
                return $query->where('name', $status);
            });
        });
    }

    /**
     * @param $query
     * @param $title
     */
    public function scopeFilteredByTitle(Builder $query, $title)
    {
        $query->when($title, function (Builder $query) use ($title) {
            return $query->whereFullText('title', $title);
            //return $query->whereRaw("MATCH(title) AGAINST(?)", [$title]);
        });
    }

    /**
     * @param $query
     * @param $priority
     */
    public function scopeFilteredByPriority(Builder $query, $priority)
    {
        $query->when($priority, function (Builder $query) use ($priority) {
            return $query->where('priority', $priority);
        });
    }

    /**
     * @param  Builder  $query
     * @param $field
     * @param $direction
     */
    public function scopeFilteredBySort(Builder $query, $field, $direction)
    {
        $query->when($field && $direction, function (Builder $query) use ($field, $direction) {
            return $query->orderBy($field, $direction);
        });
    }

    /**
     * @param  User  $user
     * @return bool
     */
    public function isBelongsToUser(User $user)
    {
        return $this->user_id === $user->id;
    }

    /**
     * @param  Status  $status
     * @return bool
     */
    public function canUpdateStatus(Status $status)
    {
        if ($status->name == 'Done') {
            return !$this->hasIncompleteSubTasks();
        }

        return true;
    }

    /**
     * @return bool
     */
    public function hasIncompleteSubTasks()
    {
        return !$this->subTasks->every(function (Task $task) {
            return $task->isComplete();
        });
    }

    /**
     * @return bool
     */
    public function isComplete()
    {
        return $this->status->name === 'Done';
    }
}
