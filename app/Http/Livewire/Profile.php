<?php

namespace App\Http\Livewire;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Masmerise\Toaster\Toaster;
use Livewire\Attributes\Rule;

class Profile extends Component
{
    public $name;
    public $email;
    public $current_password;
    public $new_password;
    public $confirm_password;
    public $showPasswordSection = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'current_password' => 'nullable|string|min:6',
        'new_password' => 'nullable|string|min:6',
        'confirm_password' => 'nullable|string|min:6|same:new_password',
    ];

    protected $messages = [
        'name.required' => 'Name is required.',
        'email.required' => 'Email is required.',
        'email.email' => 'Please enter a valid email address.',
        'current_password.min' => 'Current password must be at least 6 characters.',
        'new_password.min' => 'New password must be at least 6 characters.',
        'confirm_password.same' => 'Password confirmation does not match.',
    ];

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
        ]);

        try {
            $user = Auth::user();
            $user->update([
                'name' => $this->name,
                'email' => $this->email,
            ]);

            Toaster::success('Profile updated successfully!');
        } catch (\Exception $e) {
            Toaster::error('Failed to update profile. Please try again.');
        }
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6',
            'confirm_password' => 'required|string|min:6|same:new_password',
        ]);

        try {
            $user = Auth::user();

            // Check if current password is correct
            if (!Hash::check($this->current_password, $user->password)) {
                $this->addError('current_password', 'Current password is incorrect.');
                return;
            }

            // Update password
            $user->update([
                'password' => Hash::make($this->new_password),
            ]);

            // Clear password fields
            $this->current_password = '';
            $this->new_password = '';
            $this->confirm_password = '';
            $this->showPasswordSection = false;

            Toaster::success('Password updated successfully!');
        } catch (\Exception $e) {
            Toaster::error('Failed to update password. Please try again.');
        }
    }

    public function togglePasswordSection()
    {
        $this->showPasswordSection = !$this->showPasswordSection;
        
        // Clear password fields when hiding the section
        if (!$this->showPasswordSection) {
            $this->current_password = '';
            $this->new_password = '';
            $this->confirm_password = '';
            $this->resetErrorBag(['current_password', 'new_password', 'confirm_password']);
        }
    }

    public function render()
    {
        return view('livewire.profile');
    }
}
