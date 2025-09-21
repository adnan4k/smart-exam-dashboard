<?php
namespace App\Http\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Masmerise\Toaster\Toaster;
use Illuminate\Support\Facades\Crypt;

class UserComponent extends Component
{
    use WithPagination;

    public $showModal = false;
    public $showDeleteModal = false;
    public $selectedUserId;
    public $selectedStatus;
    public $userToDelete;

    protected $rules = [
        'selectedStatus' => 'required|in:active,inactive,suspended',
    ];

    public function edit($userId)
    {
        $this->selectedUserId = $userId;
        $user = User::find($userId);
        $this->selectedStatus = $user->status;
        $this->showModal = true;
    }

    public function updateStatus()
    {
        $user = User::find($this->selectedUserId);
        $user->update([
            'status' => $this->selectedStatus
        ]);
        
        $this->showModal = false;
        Toaster::success('User status updated successfully.');
    }

    public function confirmDelete($userId)
    {
        $this->userToDelete = User::find($userId);
        $this->showDeleteModal = true;
    }

    public function deleteUser()
    {
        if ($this->userToDelete) {
            try {
                $userName = $this->userToDelete->name;
                $this->userToDelete->delete();
                
                $this->showDeleteModal = false;
                $this->userToDelete = null;
                
                Toaster::success("User '{$userName}' has been deleted successfully.");
            } catch (\Exception $e) {
                Toaster::error('Failed to delete user. Please try again.');
            }
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->userToDelete = null;
    }

    public function render()
    {
        $users = User::with(['type', 'referredBy'])->get()->map(function ($user) {
            try {
                $user->password = Crypt::decryptString($user->password);
            } catch (\Exception $e) {
                $user->password = 'Encrypted';
            }
            return $user;
        });

        return view('livewire.user.user-component', [
            'users' => $users
        ]);
    }
}