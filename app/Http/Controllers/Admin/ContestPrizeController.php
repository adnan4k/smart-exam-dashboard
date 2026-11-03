<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\ContestAttempt;
use App\Models\ContestPrize;
use App\Models\Package;
use App\Models\User;
use App\Services\ContestPrizeService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Manual prizes for top-ranked students: a free subscription, or money paid
 * outside the app and tracked here until it has been sent.
 */
class ContestPrizeController extends Controller
{
    public function __construct(private ContestPrizeService $prizes)
    {
    }

    public function index(Request $request)
    {
        $status = $request->input('status');
        $kind   = $request->input('kind');
        $search = trim((string) $request->input('q', ''));

        $prizes = ContestPrize::with(['user:id,name,email,phone,phone_number', 'contest:id,title', 'package:id,name', 'grantedBy:id,name', 'deliveredBy:id,name'])
            ->when(in_array($status, ['pending', 'delivered', 'cancelled'], true), fn ($q) => $q->where('status', $status))
            ->when(in_array($kind, ['subscription', 'cash'], true), fn ($q) => $q->where('kind', $kind))
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('recipient_name', 'like', "%{$search}%")
                    ->orWhere('recipient_phone', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$search}%"));
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $cash = ContestPrize::where('kind', 'cash');

        return view('contests.prizes.index', [
            'prizes'  => $prizes,
            'summary' => [
                'cash_paid'     => (float) (clone $cash)->where('status', 'delivered')->sum('amount'),
                'cash_pending'  => (float) (clone $cash)->where('status', 'pending')->sum('amount'),
                'pending_count' => (clone $cash)->where('status', 'pending')->count(),
                'subscriptions' => ContestPrize::where('kind', 'subscription')->where('status', 'delivered')->count(),
            ],
            'methods' => ContestPrize::PAYMENT_METHODS,
        ]);
    }

    public function create(Request $request)
    {
        $user = User::with(['subscriptions' => fn ($q) => $q->active()->with('package:id,name')])
            ->findOrFail($request->integer('user'));

        // Contests this student actually sat, newest first, for the "awarded for" list.
        $attempts = ContestAttempt::where('user_id', $user->id)
            ->whereIn('status', ['submitted', 'expired'])
            ->with('contest:id,title,status,starts_at')
            ->get()
            ->filter(fn ($a) => $a->contest)
            ->sortByDesc(fn ($a) => $a->contest->starts_at)
            ->values();

        return view('contests.prizes.create', [
            'user'      => $user->load('type:id,name'),
            'attempts'  => $attempts,
            'packages'  => Package::where('is_active', true)->orderBy('order')->get(['id', 'name', 'duration_days', 'price']),
            'methods'   => ContestPrize::PAYMENT_METHODS,
            'contestId' => $request->integer('contest') ?: null,
            'rank'      => $request->integer('rank') ?: null,
            'previous'  => ContestPrize::where('user_id', $user->id)->with(['contest:id,title', 'package:id,name'])->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'           => ['required', 'exists:users,id'],
            'contest_id'        => ['nullable', 'exists:contests,id'],
            'rank'              => ['nullable', 'integer', 'min:1', 'max:100000'],
            'kind'              => ['required', Rule::in(['subscription', 'cash'])],
            'package_id'        => ['required_if:kind,subscription', 'nullable', Rule::exists('packages', 'id')],
            'subscription_days' => ['required_if:kind,subscription', 'nullable', 'integer', 'min:1', 'max:3650'],
            'amount'            => ['required_if:kind,cash', 'nullable', 'numeric', 'min:1', 'max:10000000'],
            'payment_method'    => ['nullable', Rule::in(ContestPrize::PAYMENT_METHODS)],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'paid_now'          => ['nullable', 'boolean'],
            'note'              => ['nullable', 'string', 'max:2000'],
        ]);

        if ($data['kind'] === 'cash' && ! empty($data['paid_now']) && empty($data['payment_method'])) {
            return back()->withInput()->withErrors(['payment_method' => 'Pick how the money was sent, or untick "Already paid".']);
        }

        $user  = User::findOrFail($data['user_id']);
        $prize = $this->prizes->grant($user, $data, $request->user());

        $message = $prize->isCash()
            ? ($prize->status === 'delivered'
                ? "Recorded {$prize->summary()} paid to {$user->name}."
                : "Prize of {$prize->summary()} for {$user->name} is awaiting payment. Mark it paid once the money is sent.")
            : "{$user->name} now has {$prize->summary()}.";

        return redirect()->route('contest-prizes.index')->with('success', $message);
    }

    public function markPaid(Request $request, ContestPrize $prize)
    {
        if (! $prize->isCash() || $prize->status !== 'pending') {
            return back()->with('error', 'Only a cash prize awaiting payment can be marked paid.');
        }

        $data = $request->validate([
            'payment_method'    => ['required', Rule::in(ContestPrize::PAYMENT_METHODS)],
            'payment_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $this->prizes->markPaid($prize, $data['payment_method'], $data['payment_reference'] ?? null, $request->user());

        return back()->with('success', "Marked {$prize->summary()} to {$prize->recipient_name} as paid.");
    }

    /**
     * Only an unpaid cash prize can be cancelled. A granted subscription is
     * already in the student's hands; change it from the subscriptions screen.
     */
    public function cancel(ContestPrize $prize)
    {
        if (! $prize->isCash() || $prize->status !== 'pending') {
            return back()->with('error', 'Only a cash prize awaiting payment can be cancelled.');
        }

        $prize->update(['status' => 'cancelled']);

        return back()->with('success', 'Prize cancelled.');
    }
}
