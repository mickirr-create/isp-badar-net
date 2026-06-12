<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'tbl_customers';

    protected $fillable = [
        'username',
        'password',
        'name',
        'email',
        'phone',
        'address',
        'balance',
        'auto_renewal',
        'billing_day',
        'throttle_enabled',
        'throttle_profile',
        'last_login',
        'last_login_ip',
        'status',
        'created_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'balance' => 'float',
        'auto_renewal' => 'boolean',
        'throttle_enabled' => 'boolean',
        'created_at' => 'datetime',
        'last_login' => 'datetime',
    ];

    public function recharges(): HasMany
    {
        return $this->hasMany(UserRecharge::class, 'customer_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(CustomerField::class, 'customer_id');
    }

    public function inboxMessages(): HasMany
    {
        return $this->hasMany(CustomerInbox::class, 'customer_id');
    }

    public function plans(): HasManyThrough
    {
        return $this->HasManyThrough(Plan::class, UserRecharge::class, 'customer_id', 'id', 'id', 'plan_id');
    }

    /**
     * Get the due date for this month
     */
    public function getDueDateAttribute(): ?Carbon
    {
        if (!$this->billing_day) {
            return null;
        }

        $now = Carbon::now();
        $dueDate = $now->copy()->day($this->billing_day);

        // If billing_day already passed this month, due date is next month
        if ($dueDate->lt($now)) {
            $dueDate->addMonth();
        }

        return $dueDate;
    }

    /**
     * Check if this customer is due soon (within N days)
     */
    public function getIsDueSoonAttribute(): bool
    {
        $dueDate = $this->due_date;
        if (!$dueDate) {
            return false;
        }

        $notifyDays = (int) AppConfig::getSetting('billing_notify_days_before', 7);
        return Carbon::now()->diffInDays($dueDate, false) <= $notifyDays && $dueDate->isFuture();
    }

    /**
     * Check if this customer is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        $dueDate = $this->due_date;
        if (!$dueDate) {
            return false;
        }

        return $dueDate->isPast();
    }

    /**
     * Get active recharge for current billing cycle
     */
    public function getActiveRecharge()
    {
        return $this->recharges()
            ->where('status', 'on')
            ->where('expiration', '>=', Carbon::today())
            ->first();
    }
}
