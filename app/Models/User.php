<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'loyalty_points',
        'phone',
        'address',
        'city',
        'postal_code',
        'country',
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
            'loyalty_points' => 'integer',
            // Don't cast role to ensure we can check both string and integer values
        ];
    }

    /**
     * Get the orders for the user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the delivery addresses for the user.
     */
    public function deliveryAddresses(): HasMany
    {
        return $this->hasMany(DeliveryAddress::class)->orderBy('is_default', 'desc')->orderBy('created_at', 'desc');
    }

    /**
     * Get the wishlist items for the user.
     */
    public function wishlist(): HasMany
    {
        return $this->hasMany(Wishlist::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the default delivery address.
     */
    public function defaultDeliveryAddress(): ?DeliveryAddress
    {
        return $this->deliveryAddresses()->where('is_default', true)->first()
            ?? $this->deliveryAddresses()->first();
    }

    /**
     * Get the total amount spent by the user.
     */
    public function getTotalSpentAttribute(): float
    {
        return (float) $this->orders()->where('payment_status', 'paid')->sum('total');
    }

    /**
     * Get the total number of orders.
     */
    public function getTotalOrdersAttribute(): int
    {
        return $this->orders()->count();
    }

    /**
     * Add loyalty points to user.
     */
    public function addLoyaltyPoints(int $points): void
    {
        $this->increment('loyalty_points', $points);
    }

    /**
     * Calculate loyalty points from order total (3% of total).
     */
    public static function calculateLoyaltyPoints(float $total): int
    {
        return (int) round($total * 0.03);
    }

    /**
     * Get formatted loyalty points.
     */
    public function getFormattedLoyaltyPointsAttribute(): string
    {
        return number_format($this->loyalty_points, 0, ',', '.') . ' puncte';
    }

    /**
     * Check if user can access Filament admin panel.
     * This method is required by Filament.
     * Only users with 'admin' role can access the panel.
     */
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return $this->hasRole('admin');
    }
}
