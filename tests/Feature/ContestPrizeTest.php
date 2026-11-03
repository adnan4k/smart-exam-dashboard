<?php

namespace Tests\Feature;

use App\Models\Contest;
use App\Models\ContestAttempt;
use App\Models\ContestPrize;
use App\Models\Package;
use App\Models\Subscription;
use App\Models\Type;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Admins reward top-ranked students with a subscription (unlocked at once) or
 * money (tracked until marked paid), and can see how to contact them.
 */
class ContestPrizeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $student;

    private Contest $contest;

    private Package $package;

    protected function setUp(): void
    {
        parent::setUp();

        $type = Type::create(['name' => 'Freshman Exams', 'price' => '250']);

        $this->admin = User::factory()->create(['name' => 'Admin']);
        $this->student = User::factory()->create([
            'name'             => 'Top Student',
            'type_id'          => $type->id,
            'phone'            => '0911223344',
            'institution_name' => 'Addis Ababa University',
        ]);

        $this->package = Package::create([
            'name' => 'All Access', 'slug' => 'all_access', 'price' => 700,
            'duration_days' => 365, 'max_subjects' => 50, 'is_active' => true, 'order' => 1,
        ]);

        $this->contest = Contest::create([
            'title' => 'Weekly Sprint', 'type_id' => $type->id, 'duration_minutes' => 20,
            'starts_at' => now()->subDays(2), 'ends_at' => now()->subDays(2)->addHour(),
            'status' => 'finalized', 'question_count' => 5,
        ]);

        ContestAttempt::create([
            'contest_id' => $this->contest->id, 'user_id' => $this->student->id,
            'started_at' => $this->contest->starts_at, 'expires_at' => $this->contest->ends_at,
            'submitted_at' => $this->contest->starts_at->copy()->addMinutes(5),
            'status' => 'submitted', 'score' => 5, 'question_stars' => 50, 'stars_awarded' => 250,
            'correct_count' => 5, 'total_questions' => 5, 'time_taken_seconds' => 300, 'rank' => 1,
        ]);
    }

    public function test_rankings_show_reward_button_and_contact_details(): void
    {
        $this->actingAs($this->admin)
            ->get(route('contest-rankings.index'))
            ->assertOk()
            ->assertSee(route('contest-prizes.create', ['user' => $this->student->id, 'rank' => 1]))
            ->assertSee('tel:0911223344', false)
            ->assertSee('Addis Ababa University');
    }

    public function test_subscription_prize_unlocks_a_paid_subscription_immediately(): void
    {
        $this->actingAs($this->admin)
            ->post(route('contest-prizes.store'), [
                'user_id' => $this->student->id, 'contest_id' => $this->contest->id, 'rank' => 1,
                'kind' => 'subscription', 'package_id' => $this->package->id, 'subscription_days' => 30,
            ])
            ->assertRedirect(route('contest-prizes.index'));

        $prize = ContestPrize::sole();
        $this->assertSame('delivered', $prize->status);
        $this->assertSame($this->admin->id, $prize->granted_by);
        $this->assertSame('0911223344', $prize->recipient_phone);

        $subscription = Subscription::findOrFail($prize->subscription_id);
        $this->assertSame('paid', $subscription->payment_status);
        $this->assertEquals(0, $subscription->amount);
        $this->assertSame(now()->addDays(30)->toDateString(), \Illuminate\Support\Carbon::parse($subscription->end_date)->toDateString());
        $this->assertTrue($this->student->fresh()->hasPaidPackage());
    }

    public function test_subscription_prize_extends_an_active_subscription_instead_of_duplicating_it(): void
    {
        $existing = Subscription::create([
            'user_id' => $this->student->id, 'package_id' => $this->package->id, 'type_id' => $this->student->type_id,
            'start_date' => now()->subDays(10)->toDateString(), 'end_date' => now()->addDays(20)->toDateString(),
            'amount' => 700, 'payment_status' => 'paid',
        ]);

        $this->actingAs($this->admin)->post(route('contest-prizes.store'), [
            'user_id' => $this->student->id, 'kind' => 'subscription',
            'package_id' => $this->package->id, 'subscription_days' => 30,
        ]);

        $this->assertSame(1, Subscription::count());
        $this->assertSame(now()->addDays(50)->toDateString(), \Illuminate\Support\Carbon::parse($existing->fresh()->end_date)->toDateString());
        $this->assertSame($existing->id, ContestPrize::sole()->subscription_id);
    }

    public function test_cash_prize_is_tracked_until_marked_paid(): void
    {
        $this->actingAs($this->admin)->post(route('contest-prizes.store'), [
            'user_id' => $this->student->id, 'contest_id' => $this->contest->id, 'rank' => 1,
            'kind' => 'cash', 'amount' => 500,
        ])->assertRedirect(route('contest-prizes.index'));

        $prize = ContestPrize::sole();
        $this->assertSame('pending', $prize->status);
        $this->assertSame(0, Subscription::count());

        $this->actingAs($this->admin)->get(route('contest-prizes.index'))
            ->assertOk()->assertSee('Awaiting payment')->assertSee('500.00 ETB');

        $this->actingAs($this->admin)->post(route('contest-prizes.paid', $prize), [
            'payment_method' => 'Telebirr', 'payment_reference' => 'TX123',
        ])->assertRedirect();

        $prize->refresh();
        $this->assertSame('delivered', $prize->status);
        $this->assertSame('Telebirr', $prize->payment_method);
        $this->assertSame('TX123', $prize->payment_reference);
        $this->assertSame($this->admin->id, $prize->delivered_by);
        $this->assertNotNull($prize->delivered_at);

        // Paid money can no longer be cancelled.
        $this->actingAs($this->admin)->post(route('contest-prizes.cancel', $prize));
        $this->assertSame('delivered', $prize->fresh()->status);
    }

    public function test_cash_marked_already_paid_needs_a_payment_method(): void
    {
        $this->actingAs($this->admin)->post(route('contest-prizes.store'), [
            'user_id' => $this->student->id, 'kind' => 'cash', 'amount' => 500, 'paid_now' => 1,
        ])->assertSessionHasErrors('payment_method');

        $this->assertSame(0, ContestPrize::count());
    }

    public function test_pending_cash_prize_can_be_cancelled(): void
    {
        $this->actingAs($this->admin)->post(route('contest-prizes.store'), [
            'user_id' => $this->student->id, 'kind' => 'cash', 'amount' => 300,
        ]);

        $this->actingAs($this->admin)->post(route('contest-prizes.cancel', ContestPrize::sole()));

        $this->assertSame('cancelled', ContestPrize::sole()->status);
    }

    public function test_grant_form_shows_contact_and_contests_entered(): void
    {
        $this->actingAs($this->admin)
            ->get(route('contest-prizes.create', ['user' => $this->student->id, 'rank' => 1]))
            ->assertOk()
            ->assertSee('0911223344')
            ->assertSee('Weekly Sprint — placed #1');
    }
}
