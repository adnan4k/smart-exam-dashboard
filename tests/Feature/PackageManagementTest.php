<?php

namespace Tests\Feature;

use App\Http\Livewire\Package\PackageComponent;
use App\Http\Livewire\Subjects\Form as SubjectForm;
use App\Models\Package;
use App\Models\Subject;
use App\Models\Subscription;
use App\Models\Type;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PackageManagementTest extends TestCase
{
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PackageSeeder::class);
        $this->admin = User::firstOrCreate(
            ['email' => 'admin_pkg_test@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );
    }

    /** @test */
    public function it_renders_packages_screen_and_lists_seeded_packages()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('packages'));
        $response->assertStatus(200);

        Livewire::test(PackageComponent::class)
            ->assertStatus(200)
            ->assertSee('1st Semester')
            ->assertSee('2nd Semester')
            ->assertSee('COC Exam')
            ->assertSee('All Access');
    }

    /** @test */
    public function it_can_create_a_new_package()
    {
        $this->actingAs($this->admin);

        Livewire::test(PackageComponent::class)
            ->set('selectedPreset', 'custom')
            ->set('name', 'Nursing Exit Exam Special')
            ->set('price', 450.00)
            ->set('description', 'Specialized exam package for nursing students')
            ->set('isActive', true)
            ->call('savePackage')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('packages', [
            'name' => 'Nursing Exit Exam Special',
            'slug' => 'nursing_exit_exam_special',
            'price' => 450.00,
        ]);
    }

    /** @test */
    public function it_can_edit_an_existing_package()
    {
        $this->actingAs($this->admin);

        $package = Package::where('slug', Package::SLUG_SEMESTER_1)->first();

        Livewire::test(PackageComponent::class)
            ->call('edit', $package->id)
            ->set('price', 350.00)
            ->set('name', '1st Semester (Updated)')
            ->call('savePackage')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'name' => '1st Semester (Updated)',
            'price' => 350.00,
        ]);
    }

    /** @test */
    public function it_can_toggle_package_active_status()
    {
        $this->actingAs($this->admin);

        $package = Package::where('slug', Package::SLUG_COC)->first();
        $initialStatus = $package->is_active;

        Livewire::test(PackageComponent::class)
            ->call('toggleStatus', $package->id);

        $this->assertEquals(!$initialStatus, $package->fresh()->is_active);
    }

    /** @test */
    public function it_can_assign_package_to_subject_in_subject_form()
    {
        $this->actingAs($this->admin);

        $type = Type::firstOrCreate(['name' => 'General Medicine']);
        $package = Package::where('slug', Package::SLUG_SEMESTER_1)->first();

        Livewire::test(SubjectForm::class)
            ->set('name', 'Human Anatomy I')
            ->set('typeId', $type->id)
            ->set('packageId', $package->id)
            ->set('year', '2026')
            ->set('defaultDuration', 60)
            ->call('saveSubject')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('subjects', [
            'name' => 'Human Anatomy I',
            'package_id' => $package->id,
            'package_type' => 'semester_1',
        ]);
    }
}
