<?php

namespace App\Http\Livewire\Package;

use App\Models\Package;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class PackageComponent extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Search filter
    public $search = '';

    // Inline Form state
    public $showForm = false;
    public $isEdit = false;
    public $packageId = null;

    // Simplified Form fields
    public $selectedPreset = 'semester_1';
    public $name = '1st Semester';
    public $price = 300.00;
    public $description = '';
    public $isActive = true;

    // Delete confirmation state
    public $showDeleteModal = false;
    public $packageToDelete = null;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'isActive' => 'boolean',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreateForm()
    {
        $this->resetForm();
        $this->applyPreset('semester_1');
        $this->showForm = true;
        $this->isEdit = false;
    }

    public function selectPreset($preset)
    {
        $this->selectedPreset = $preset;

        if (!$this->isEdit) {
            match ($preset) {
                'semester_1' => [
                    $this->name = '1st Semester',
                    $this->price = 300.00,
                    $this->description = 'Unlocks all subjects and materials for the 1st Semester.',
                ],
                'semester_2' => [
                    $this->name = '2nd Semester',
                    $this->price = 300.00,
                    $this->description = 'Unlocks all subjects and materials for the 2nd Semester.',
                ],
                'coc' => [
                    $this->name = 'COC Exam',
                    $this->price = 400.00,
                    $this->description = 'Preparation materials for Certificate of Competency (COC) / Exit exams.',
                ],
                'all_access' => [
                    $this->name = 'All Access',
                    $this->price = 700.00,
                    $this->description = 'Full access to 1st Semester, 2nd Semester, and COC materials.',
                ],
                default => [
                    $this->name = '',
                    $this->price = 0.00,
                    $this->description = '',
                ],
            };
        }
    }

    public function edit($id)
    {
        $package = Package::findOrFail($id);
        $this->packageId = $package->id;
        $this->name = $package->name;
        $this->price = (float) $package->price;
        $this->description = $package->description ?? '';
        $this->isActive = (bool) $package->is_active;

        if (in_array($package->slug, ['semester_1', 'semester_2', 'coc', 'all_access'])) {
            $this->selectedPreset = $package->slug;
        } else {
            $this->selectedPreset = 'custom';
        }

        $this->isEdit = true;
        $this->showForm = true;
    }

    public function cancelEdit()
    {
        $this->resetForm();
    }

    public function savePackage()
    {
        $this->validate();

        // Determine slug & default order
        $slug = $this->selectedPreset !== 'custom'
            ? $this->selectedPreset
            : Str::slug($this->name, '_');

        $order = match ($slug) {
            'semester_1' => 1,
            'semester_2' => 2,
            'coc' => 3,
            'all_access' => 4,
            default => 10,
        };

        $data = [
            'name' => trim($this->name),
            'slug' => $slug,
            'description' => $this->description ? trim($this->description) : null,
            'price' => $this->price,
            'duration_days' => 365,
            'is_active' => $this->isActive,
            'order' => $order,
        ];

        if ($this->isEdit && $this->packageId) {
            $package = Package::findOrFail($this->packageId);
            $package->update($data);
            Toaster::success("Package '{$package->name}' updated successfully.");
        } else {
            Package::create($data);
            Toaster::success("Package '{$data['name']}' created successfully.");
        }

        $this->resetForm();
    }

    public function toggleStatus($id)
    {
        $package = Package::findOrFail($id);
        $package->is_active = !$package->is_active;
        $package->save();

        $statusText = $package->is_active ? 'activated' : 'deactivated';
        Toaster::success("Package '{$package->name}' is now {$statusText}.");
    }

    public function confirmDelete($id)
    {
        $this->packageToDelete = Package::withCount(['subjects', 'subscriptions'])->findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->packageToDelete = null;
    }

    public function delete()
    {
        if (!$this->packageToDelete) {
            return;
        }

        $package = Package::withCount(['subjects', 'subscriptions'])->find($this->packageToDelete->id);
        if (!$package) {
            $this->cancelDelete();
            return;
        }

        if ($package->subscriptions_count > 0) {
            Toaster::error("Cannot delete package '{$package->name}' because {$package->subscriptions_count} subscription(s) are linked to it. Deactivate it instead.");
            $this->cancelDelete();
            return;
        }

        $name = $package->name;
        $package->delete();

        Toaster::success("Package '{$name}' deleted successfully.");
        $this->cancelDelete();
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->isEdit = false;
        $this->showForm = false;
        $this->packageId = null;
        $this->name = '';
        $this->price = 0.00;
        $this->description = '';
        $this->isActive = true;
        $this->selectedPreset = 'semester_1';
        $this->resetValidation();
    }

    public function render()
    {
        // Fetch core 4 packages for the executive card grid
        $corePackages = Package::withCount(['subjects', 'subscriptions'])
            ->whereIn('slug', ['semester_1', 'semester_2', 'coc', 'all_access'])
            ->orderBy('order', 'asc')
            ->get()
            ->keyBy('slug');

        $query = Package::withCount(['subjects', 'subscriptions']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('slug', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        $packages = $query->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(10);

        return view('livewire.package.package-component', [
            'packages' => $packages,
            'corePackages' => $corePackages,
        ]);
    }
}
