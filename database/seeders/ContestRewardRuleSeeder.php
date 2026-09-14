<?php

namespace Database\Seeders;

use App\Models\ContestRewardRule;
use Illuminate\Database\Seeder;

class ContestRewardRuleSeeder extends Seeder
{
    /**
     * Global default bands, used by any contest that has no rules of its own.
     *
     * The long tail matters as much as the top: in a contest of 500 students,
     * paying only the top three sends 497 of them away with nothing, and most
     * of them do not come back the following week.
     */
    public function run(): void
    {
        $bands = [
            ['rank_from' => 1,  'rank_to' => 1,    'stars' => 200, 'coins' => 50],
            ['rank_from' => 2,  'rank_to' => 2,    'stars' => 150, 'coins' => 30],
            ['rank_from' => 3,  'rank_to' => 3,    'stars' => 100, 'coins' => 20],
            ['rank_from' => 4,  'rank_to' => 10,   'stars' => 60,  'coins' => 10],
            ['rank_from' => 11, 'rank_to' => 50,   'stars' => 30,  'coins' => 0],
            ['rank_from' => 51, 'rank_to' => 100,  'stars' => 15,  'coins' => 0],
            // Open ended: everyone else who finished the paper.
            ['rank_from' => 101, 'rank_to' => null, 'stars' => 5,  'coins' => 0],
        ];

        foreach ($bands as $band) {
            ContestRewardRule::updateOrCreate(
                ['contest_id' => null, 'rank_from' => $band['rank_from']],
                $band + ['is_active' => true]
            );
        }
    }
}
