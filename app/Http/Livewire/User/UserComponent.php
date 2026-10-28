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

    protected $paginationTheme = 'bootstrap';

    public $showModal = false;
    public $showDeleteModal = false;
    public $selectedUserId;
    public $selectedStatus;
    public $userToDelete;

    // Search filter
    public $search = '';

    protected $rules = [
        'selectedStatus' => 'required|in:active,inactive,suspended',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

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
        $this->resetPage();
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
                $this->resetPage();
                
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
        $query = User::with(['type', 'referredBy']);

        if ($search = trim($this->search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone_number', 'like', '%' . $search . '%')
                  ->orWhere('institution_name', 'like', '%' . $search . '%')
                  ->orWhere('referral_code', 'like', '%' . $search . '%');

                if (ctype_digit($search)) {
                    $q->orWhere('id', $search);
                }
            });
        }

        $users = $query->latest('created_at')
            ->orderBy('id', 'desc')
            ->paginate(10);

        // Decrypt passwords for each user in the current page
        $users->getCollection()->transform(function ($user) {
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