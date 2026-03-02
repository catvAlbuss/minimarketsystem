<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, \Spatie\Permission\Traits\HasRoles;

    // Roles that have global (cross-branch) access
    const GLOBAL_ROLES = ['root', 'gerencia', 'administracion_general'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'branch_id',
        'name',
        'lastname',
        'dni',
        'phone',
        'address',
        'email',
        'password',
        'children',
        'affiliate',
        'insured',
        'work_modality',
        'entry_date',
        'retention',
        'entry_to_payroll',
        'role',
        'state',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * The branch this user belongs to (null = global access).
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Determine if the user has global (cross-branch) access.
     * Users with global roles see all branches; others are scoped to their branch.
     */
    public function isGlobalRole(): bool
    {
        // Check Spatie roles first
        if ($this->roles->isNotEmpty()) {
            foreach (self::GLOBAL_ROLES as $globalRole) {
                if ($this->hasRole($globalRole)) {
                    return true;
                }
            }
            return false;
        }

        // Fallback to legacy 'role' column
        return in_array($this->role, self::GLOBAL_ROLES, true);
    }

    /**
     * Get the branch ID this user is scoped to.
     * Returns null if the user has global access.
     */
    public function scopedBranchId(): ?int
    {
        if ($this->isGlobalRole()) {
            return null;
        }

        return $this->branch_id;
    }
}
