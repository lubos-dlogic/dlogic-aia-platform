<?php

declare(strict_types=1);

namespace App\Models;

use App\States\ClientState;
use App\Traits\LogsActivityWithContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\ModelStates\HasStates;
use Spatie\Tags\HasTags;

class Client extends Model
{
    use HasFactory;
    use HasStates;
    use HasTags;
    use LogsActivity;
    use LogsActivityWithContext;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'client_key',
        'country',
        'website',
        'company_gid',
        'company_vat_gid',
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
            'state' => ClientState::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user who created this client.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user');
    }

    /**
     * Get all engagements for this client.
     */
    public function engagements(): HasMany
    {
        return $this->hasMany(Engagement::class, 'client_fk');
    }

    /**
     * Configure activity logging options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'client_key',
                'country',
                'website',
                'company_gid',
                'company_vat_gid',
                'description',
                'state',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
