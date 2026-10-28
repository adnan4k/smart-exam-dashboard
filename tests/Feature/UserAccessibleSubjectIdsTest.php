<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Subject;
use App\Models\Subscription;
use App\Models\Type;
use App\Models\User;
use App\Models\YearGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * User::accessibleSubjectIds() is the batched form of canAccessSubject(), used
 * by every endpoint that gates many subjects at once. The two must never
 * disagree, or a subject's lock would depend on which endpoint asked.
 */
class UserAccessibleSubjectIdsTest extends TestCase
{
    use RefreshDatabase;

    private Type $type;
    private YearGroup $yearGroup;
    private Package $sem1;
    private Package $sem2;
    private Package $allAccess;

    protected function setUp(): void
    {
        parent::setUp();

        $this->type = Type::create(['name' => 'University Prep']);
        $this->yearGroup = YearGroup::create(['year' => 2024]);
        $this->sem1 = Package::updateOrCreate(['slug' => 'semester_1'], ['name' => 'S1', 'price' => 1, 'is_active' => true, 'type_id' => $this->type->id]);
        $this->sem2 = Package::updateOrCreate(['slug' => 'semester_2'], ['name' => 'S2', 'price' => 1, 'is_active' => true, 'type_id' => $this->type->id]);
        $this->allAccess = Package::updateOrCreate(['slug' => Package::SLUG_ALL_ACCESS], ['name' => 'All', 'price' => 1, 'is_active' => true, 'type_id' => $this->type->id]);

        $this->subject('Sample', ['is_sample' => true]);
        $this->subject('Direct sem1', ['package_id' => $this->sem1->id]);
        $this->subject('Direct sem2', ['package_id' => $this->sem2->id]);
        $this->subject('Typed sem1', ['package_type' => 'semester_1']);
        $this->subject('Typed upper', ['package_type' => 'SEMESTER_1']);
        $this->subject('Typed empty', ['package_type' => '']);
        $this->subject('Untied');
        $this->sem1->subjects()->attach($this->subject('Default sem1')->id, ['is_default' => true]);
        $this->sem1->subjects()->attach($this->subject('Optional sem1')->id, ['is_default' => false]);
        $this->sem2->subjects()->attach($this->subject('Default sem2')->id, ['is_default' => true]);
        $this->subject('Biology', ['package_id' => $this->sem2->id, 'year' => '2015']);
        $this->subject('biology ', ['package_id' => $this->sem2->id, 'year' => '2016']);
        $this->subject('Chemistry', ['package_id' => $this->sem2->id]);
    }

    public static function scenarios(): array
    {
        return [
            'no subscription' => [[]],
            'sem1 paid' => [[['sem1', 'paid', 'active']]],
            'sem1 expired' => [[['sem1', 'paid', 'expired']]],
            'sem1 pending' => [[['sem1', 'pending', 'active']]],
            'sem1 failed' => [[['sem1', 'failed', 'active']]],
            'sem2 paid' => [[['sem2', 'paid', 'active']]],
            'sem1 paid, Biology selected' => [[['sem1', 'paid', 'active', ['Biology']]]],
            'sem1 paid, Optional selected' => [[['sem1', 'paid', 'active', ['Optional sem1']]]],
            'sem1 expired, Biology selected' => [[['sem1', 'paid', 'expired', ['Biology']]]],
            'sem1 paid + sem2 expired' => [[['sem1', 'paid', 'active'], ['sem2', 'paid', 'expired']]],
            'legacy paid' => [[[null, 'paid', 'active']]],
            'legacy expired' => [[[null, 'paid', 'expired']]],
            'all access' => [[['all', 'paid', 'active']]],
            'all access expired' => [[['all', 'paid', 'expired']]],
            'no exam type, sem1 paid' => [[['sem1', 'paid', 'active']], false],
        ];
    }

    /**
     * @test
     * @dataProvider scenarios
     */
    public function batched_access_matches_the_single_subject_check(array $subscriptions, bool $hasType = true)
    {
        $user = User::factory()->create(['type_id' => $hasType ? $this->type->id : null]);

        foreach ($subscriptions as $row) {
            [$package, $status, $period] = $row;

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'type_id' => $this->type->id,
                'year_group_id' => $this->yearGroup->id,
                'package_id' => ['sem1' => $this->sem1, 'sem2' => $this->sem2, 'all' => $this->allAccess][$package]->id ?? null,
                'start_date' => now()->subYears(2),
                'end_date' => $period === 'active' ? now()->addMonth() : now()->subDay(),
                'payment_status' => $status,
            ]);
            $subscription->subjects()->sync(Subject::whereIn('name', $row[3] ?? [])->pluck('id'));
        }

        $subjects = Subject::all();
        $expected = $subjects->filter(fn (Subject $subject) => $user->canAccessSubject($subject))->pluck('id')->sort()->values()->all();

        $this->assertSame($expected, $user->accessibleSubjectIds($subjects)->sort()->values()->all());
        $this->assertSame([], $user->accessibleSubjectIds([])->all());
    }

    private function subject(string $name, array $attributes = []): Subject
    {
        return Subject::create($attributes + [
            'name' => $name,
            'year' => '2015',
            'type_id' => $this->type->id,
            'is_sample' => false,
        ]);
    }
}
