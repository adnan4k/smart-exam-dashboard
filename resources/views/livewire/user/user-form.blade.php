                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name" class="form-control-label">Name</label>
                            <input type="text" class="form-control" id="name" wire:model="name">
                            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email" class="form-control-label">Email</label>
                            <input type="email" class="form-control" id="email" wire:model="email">
                            @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="phone_number" class="form-control-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone_number" wire:model="phone_number">
                            @error('phone_number') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="institution_type" class="form-control-label">Institution Type</label>
                            <input type="text" class="form-control" id="institution_type" wire:model="institution_type">
                            @error('institution_type') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="institution_name" class="form-control-label">Institution Name</label>
                            <input type="text" class="form-control" id="institution_name" wire:model="institution_name">
                            @error('institution_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="type_id" class="form-control-label">Type</label>
                            <select class="form-control" id="type_id" wire:model="type_id">
                                <option value="">Select Type</option>
                                @foreach(\App\Models\Type::all() as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('type_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password" class="form-control-label">Password</label>
                            <input type="password" class="form-control" id="password" wire:model="password">
                            @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="role" class="form-control-label">Role</label>
                            <select class="form-control" id="role" wire:model="role">
                                <option value="student">Student</option>
                                <option value="admin">Admin</option>
                            </select>
                            @error('role') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status" class="form-control-label">Status</label>
                            <select class="form-control" id="status" wire:model="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                            @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div> 