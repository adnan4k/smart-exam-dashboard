<?php

namespace App\Services;

use App\Models\ContestPrize;
use App\Models\Package;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ContestPrizeService
{
    /**
     * Record a prize and, for a subscription, unlock it straight away.
     *
     * $data: kind, contest_id?, rank?, note?, and either
     *   package_id + subscription_days, or
     *   amount + payment_method? + payment_reference? + paid_now?
     */
    public function grant(User $user, array $data, ?User $admin): ContestPrize
    {
        return DB::transaction(function () use ($user, $data, $admin) {
            $prize = new ContestPrize([
                'user_id'         => $user->id,
                'recipient_name'  => $user->name,
                'recipient_phone' => $user->phone ?: $user->phone_number,
                'contest_id'      => $data['contest_id'] ?? null,
                'rank'            => $data['rank'] ?? null,
                'kind'            => $data['kind'],
                'note'            => $data['note'] ?? null,
                'granted_by'      => $admin?->id,
            ]);

            if ($data['kind'] === 'subscription') {
                $package = Package::findOrFail($data['package_id']);
                $days    = (int) $data['subscription_days'];

                $prize->fill([
                    'package_id'        => $package->id,
                    'subscription_days' => $days,
                    'subscription_id'   => $this->grantSubscription($user, $package, $days)->id,
                    'status'            => 'delivered',
                    'delivered_at'      => now(),
                    'delivered_by'      => $admin?->id,
                ]);
            } else {
                $paidNow = ! empty($data['paid_now']);

                $prize->fill([
                    'amount'            => $data['amount'],
                    'payment_method'    => $data['payment_method'] ?? null,
                    'payment_reference' => $data['payment_reference'] ?? null,
                    'status'            => $paidNow ? 'delivered' : 'pending',
                    'delivered_at'      => $paidNow ? now() : null,
                    'delivered_by'      => $paidNow ? $admin?->id : null,
                ]);
            }

            $prize->save();

            return $prize;
        });
    }

    public function markPaid(ContestPrize $prize, string $method, ?string $reference, ?User $admin): void
    {
        $prize->update([
            'status'            => 'delivered',
            'payment_method'    => $method,
            'payment_reference' => $reference,
            'delivered_at'      => now(),
            'delivered_by'      => $admin?->id,
        ]);
    }

    /**
     * A student who already has this package active gets extra days on the
     * end of it, rather than a second overlapping subscription. Otherwise a
     * new paid subscription is opened at no charge, with the package's default
     * subjects, exactly as an approved payment would be.
     */
    private function grantSubscription(User $user, Package $package, int $days): Subscription
    {
        $active = $user->subscriptions()
            ->active()
            ->where('package_id', $package->id)
            ->orderByDesc('end_date')
            ->lockForUpdate()
            ->first();

        if ($active) {
            $active->update([
                'end_date' => Carbon::parse($active->end_date)->addDays($days)->toDateString(),
            ]);

            return $active;
        }

        $subscription = $user->subscriptions()->create([
            'package_id'     => $package->id,
            'type_id'        => $user->type_id,
            'start_date'     => now()->toDateString(),
            'end_date'       => now()->addDays($days)->toDateString(),
            'amount'         => 0,
            'payment_status' => 'paid',
        ]);

        $subscription->syncDefaultSubjects();

        return $subscription;
    }
}
