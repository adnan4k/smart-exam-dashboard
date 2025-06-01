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
    public $selectedUserId;
    public $selectedStatus;

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
        session()->flash('message', 'User status updated successfully.');
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