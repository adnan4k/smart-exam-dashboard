<?php

namespace App\Http\Livewire\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class UserForm extends Component
{
    public $name;
    public $email;
    public $phone_number;
    public $institution_type;
    public $institution_name;
    public $type_id;
    public $password;
    public $role = 'student';
    public $status = 'active';
    public $user_id;

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'nullable|email',
        'phone_number' => 'required|string|max:255',
        'institution_type' => 'nullable|string',
        'institution_name' => 'nullable|string',
        'type_id' => 'required|exists:types,id',
        'password' => 'required|min:6',
        'role' => 'required|in:admin,student',
        'status' => 'required|in:active,inactive,suspended'
    ];

    public function mount($user_id = null)
    {
        if ($user_id) {
            $user = User::findOrFail($user_id);
            $this->user_id = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->phone_number = $user->phone_number;
            $this->institution_type = $user->institution_type;
            $this->institution_name = $user->institution_name;
            $this->type_id = $user->type_id;
            $this->role = $user->role;
            $this->status = $user->status;
            $this->password = ''; // Don't load password
        }
    }

    public function save()
    {
        $this->validate();

        if ($this->user_id) {
            $user = User::find($this->user_id);
            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'phone_number' => $this->phone_number,
                'institution_type' => $this->institution_type,
                'institution_name' => $this->institution_name,
                'type_id' => $this->type_id,
                'role' => $this->role,
                'status' => $this->status
            ];
            
            if ($this->password) {
                $data['password'] = Hash::make($this->password);
            }
            
            $user->update($data);
            session()->flash('message', 'User updated successfully.');
        } else {
            User::create([
                'name' => $this->name,
                'email' => $this->email,
                'phone_number' => $this->phone_number,
                'institution_type' => $this->institution_type,
                'institution_name' => $this->institution_name,
                'type_id' => $this->type_id,
                'password' => Hash::make($this->password),
                'role' => $this->role,
                'status' => $this->status
            ]);
            session()->flash('message', 'User created successfully.');
        }

        return redirect()->route('users.index');
    }

    public function render()
    {
        return view('livewire.user.user-form');
    }
} 