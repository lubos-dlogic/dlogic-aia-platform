<?php

declare(strict_types=1);

namespace App\Models;

use App\States\EngagementAuditState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\ModelStates\HasStates;
use Spatie\Tags\HasTags;

class EngagementAudit extends Model
{
    use HasFactory;
    use HasStates;
    use HasTags;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'engagement_audits';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'engagement_fk',
        'name',
        'type',
        'data',
        'description',
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
            'state' => EngagementAuditState::class,
            'data' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the engagement that owns this audit.
     */
    public function engagement(): BelongsTo
    {
        return $this->belongsTo(Engagement::class, 'engagement_fk');
    }

    /**
     * Get the user who created this audit.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user');
    }
}
