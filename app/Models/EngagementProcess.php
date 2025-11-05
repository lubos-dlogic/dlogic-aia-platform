<?php

declare(strict_types=1);

namespace App\Models;

use App\States\EngagementProcessState;
use App\Traits\LogsActivityWithContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\ModelStates\HasStates;
use Spatie\Tags\HasTags;

class EngagementProcess extends Model
{
    use HasFactory;
    use HasStates;
    use HasTags;
    use LogsActivity;
    use LogsActivityWithContext;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'engagement_processes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'engagement_fk',
        'key',
        'name',
        'description',
        'data',
        'created_by_user',
        'created_by_process',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'state' => EngagementProcessState::class,
            'data' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the engagement that owns this process.
     */
    public function engagement(): BelongsTo
    {
        return $this->belongsTo(Engagement::class, 'engagement_fk');
    }

    /**
     * Get the user who created this process.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user');
    }

    /**
     * Get all versions for this process.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(EngagementProcessVersion::class, 'process_fk');
    }

    /**
     * Configure activity logging options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'engagement_fk',
                'key',
                'name',
                'description',
                'data',
                'state',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
