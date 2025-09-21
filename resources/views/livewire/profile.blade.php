<div class="main-content">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4 mx-4">
                    <div class="card-header pb-0">
                        <div class="d-flex flex-row justify-content-between align-items-center">
                            <h5 class="mb-0">Profile Settings</h5>
                        </div>
                    </div>

                    <div class="card-body px-4 pt-0 pb-2">
                        <form wire:submit.prevent="updateProfile">
                            <!-- Profile Information Section -->
                            <div class="mb-4">
                                <h6 class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 mb-3">
                                    Profile Information
                                </h6>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name" class="form-control-label">Full Name</label>
                                            <input 
                                                type="text" 
                                                id="name"
                                                wire:model="name" 
                                                class="form-control @error('name') is-invalid @enderror"
                                                placeholder="Enter your full name"
                                            >
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email" class="form-control-label">Email Address</label>
                                            <input 
                                                type="email" 
                                                id="email"
                                                wire:model="email" 
                                                class="form-control @error('email') is-invalid @enderror"
                                                placeholder="Enter your email address"
                                            >
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-3">
                                    <button 
                                        type="submit" 
                                        class="btn btn-primary"
                                        wire:loading.attr="disabled"
                                        style="background-color:#56C596; border-color:#56C596;"
                                    >
                                        <span wire:loading.remove>Update Profile</span>
                                        <span wire:loading>Updating...</span>
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Password Section -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 mb-0">
                                    Password Settings
                                </h6>
                                <button 
                                    type="button" 
                                    class="btn btn-outline-secondary btn-sm"
                                    wire:click="togglePasswordSection"
                                >
                                    <i class="fas fa-{{ $showPasswordSection ? 'eye-slash' : 'eye' }}"></i>
                                    {{ $showPasswordSection ? 'Hide' : 'Change Password' }}
                                </button>
                            </div>

                            @if($showPasswordSection)
                                <form wire:submit.prevent="updatePassword">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="current_password" class="form-control-label">Current Password</label>
                                                <input 
                                                    type="password" 
                                                    id="current_password"
                                                    wire:model="current_password" 
                                                    class="form-control @error('current_password') is-invalid @enderror"
                                                    placeholder="Enter current password"
                                                >
                                                @error('current_password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="new_password" class="form-control-label">New Password</label>
                                                <input 
                                                    type="password" 
                                                    id="new_password"
                                                    wire:model="new_password" 
                                                    class="form-control @error('new_password') is-invalid @enderror"
                                                    placeholder="Enter new password"
                                                >
                                                @error('new_password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="confirm_password" class="form-control-label">Confirm New Password</label>
                                                <input 
                                                    type="password" 
                                                    id="confirm_password"
                                                    wire:model="confirm_password" 
                                                    class="form-control @error('confirm_password') is-invalid @enderror"
                                                    placeholder="Confirm new password"
                                                >
                                                @error('confirm_password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end mt-3">
                                        <button 
                                            type="button" 
                                            class="btn btn-secondary me-2"
                                            wire:click="togglePasswordSection"
                                        >
                                            Cancel
                                        </button>
                                        <button 
                                            type="submit" 
                                            class="btn btn-warning"
                                            wire:loading.attr="disabled"
                                        >
                                            <span wire:loading.remove>Update Password</span>
                                            <span wire:loading>Updating...</span>
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>

                        <!-- Account Information Section -->
                        <div class="mb-4">
                            <h6 class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 mb-3">
                                Account Information
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">User ID</label>
                                        <input 
                                            type="text" 
                                            class="form-control" 
                                            value="{{ Auth::user()->id }}" 
                                            readonly
                                        >
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Role</label>
                                        <input 
                                            type="text" 
                                            class="form-control" 
                                            value="{{ ucfirst(Auth::user()->role ?? 'User') }}" 
                                            readonly
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Account Status</label>
                                        <input 
                                            type="text" 
                                            class="form-control" 
                                            value="{{ ucfirst(Auth::user()->status ?? 'Active') }}" 
                                            readonly
                                        >
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Last Login</label>
                                        <input 
                                            type="text" 
                                            class="form-control" 
                                            value="{{ Auth::user()->last_login_at ? Auth::user()->last_login_at->format('M d, Y H:i:s') : 'Never' }}" 
                                            readonly
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 