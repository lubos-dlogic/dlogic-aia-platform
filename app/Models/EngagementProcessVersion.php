<?php

declare(strict_types=1);

namespace App\Models;

use App\States\EngagementProcessVersionState;
use App\Traits\LogsActivityWithContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\ModelStates\HasStates;
use Spatie\Tags\HasTags;

class EngagementProcessVersion extends Model
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
    protected $table = 'engagement_processes_versions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'process_fk',
        'name',
        'version_number',
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
            'state' => EngagementProcessVersionState::class,
            'data' => 'array',
            'version_number' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the process that owns this version.
     */
    public function process(): BelongsTo
    {
        return $this->belongsTo(EngagementProcess::class, 'process_fk');
    }

    /**
     * Get the user who created this version.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user');
    }

    /**
     * Configure activity logging options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'process_fk',
                'name',
                'version_number',
                'description',
                'data',
                'state',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
