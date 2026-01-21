<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'total_amount',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'total_amount' => 'decimal:2',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Helpers
    public function isCancelled(): bool
    {
        return $this->status === OrderStatus::Cancelled;
    }

    public function isCompleted(): bool
    {
        return $this->status === OrderStatus::Completed;
    }

    public function isPending(): bool
    {
        return $this->status === OrderStatus::Pending;
    }

    public function isProcessing(): bool
    {
        return $this->status === OrderStatus::Processing;
    }

    public function canTransitionToStatus(OrderStatus $newStatus): bool
    {
        return match (true) {
            $this->isCancelled() => false,
            $this->isCompleted() => false,
            $this->isPending() => in_array($newStatus, [OrderStatus::Processing, OrderStatus::Cancelled]),
            $this->isProcessing() => in_array($newStatus, [OrderStatus::Completed, OrderStatus::Cancelled]),
            default => false,
        };
    }
}
