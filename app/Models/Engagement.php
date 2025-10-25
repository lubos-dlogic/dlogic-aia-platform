<?php

declare(strict_types=1);

namespace App\Models;

use App\States\EngagementState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\ModelStates\HasStates;
use Spatie\Tags\HasTags;

class Engagement extends Model
{
    use HasFactory;
    use HasStates;
    use HasTags;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'name',
        'client_fk',
        'version',
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
            'state' => EngagementState::class,
            'data' => 'array',
            'version' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the client that owns this engagement.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_fk');
    }

    /**
     * Get the user who created this engagement.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user');
    }

    /**
     * Get all audits for this engagement.
     */
    public function audits(): HasMany
    {
        return $this->hasMany(EngagementAudit::class, 'engagement_fk');
    }

    /**
     * Get all processes for this engagement.
     */
    public function processes(): HasMany
    {
        return $this->hasMany(EngagementProcess::class, 'engagement_fk');
    }
}
