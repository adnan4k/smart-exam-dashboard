<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContestPrize extends Model
{
    protected $guarded = [];

    public const PAYMENT_METHODS = ['Telebirr', 'CBE Birr', 'Bank transfer', 'Cash', 'Other'];

    protected $casts = [
        'rank'              => 'integer',
        'subscription_days' => 'integer',
        'amount'            => 'decimal:2',
        'delivered_at'      => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    public function deliveredBy()
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public function isCash(): bool
    {
        return $this->kind === 'cash';
    }

    /** One line describing what was given, e.g. "All Access · 30 days" or "500.00 ETB". */
    public function summary(): string
    {
        return $this->isCash()
            ? number_format((float) $this->amount, 2) . ' ETB'
            : ($this->package?->name ?? 'Subscription') . ' · ' . $this->subscription_days . ' days';
    }

    /** Human status: cash waits for payment, a subscription is granted at once. */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending'   => 'Awaiting payment',
            'delivered' => $this->isCash() ? 'Paid' : 'Granted',
            'cancelled' => 'Cancelled',
            default     => ucfirst($this->status),
        };
    }

    /** What the prize was for, e.g. "#1 · Weekly Math Sprint" or "#2 · Overall ranking". */
    public function forLabel(): string
    {
        $what = $this->contest?->title ?? ($this->contest_id ? 'Deleted contest' : 'Overall ranking');

        return ($this->rank ? '#' . $this->rank . ' · ' : '') . $what;
    }
}
