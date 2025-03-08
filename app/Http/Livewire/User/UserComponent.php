<?php
namespace App\Http\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Masmerise\Toaster\Toaster;

class UserComponent extends Component
{
    use WithPagination;

    public $showModal = false;
    public $selectedUser;
    public $selectedStatus;

    protected $rules = [
        'selectedStatus' => 'required|in:active,inactive,suspended',
    ];

    public function edit($userId)
    {
        $this->selectedUser = User::find($userId);
        $this->selectedStatus = $this->selectedUser->status;
        $this->showModal = true;
    }

    public function updateStatus()
    {
        $this->validate();

        // Update the user status
        $this->selectedUser->status = $this->selectedStatus;
        $this->selectedUser->save();

        // Close the modal and reset the form
        $this->showModal = false;
        $this->reset(['selectedUser', 'selectedStatus']);

        // Show success message
        Toaster::success('User status updated successfully!');
    }

    public function render()
    {
        $users = User::paginate(10);
        return view('livewire.user.user-component', [
            'users' => $users,
        ]);
    }
}