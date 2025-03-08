<div class="main-content">
    <div class="container">
        <div class="card mb-4 mx-4">
            <div class="card-header pb-0">
                <div class="d-flex flex-row justify-content-between">
                    <div>
                        <h5 class="mb-0">All Users</h5>
                    </div>
                </div>
            </div>

            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    ID
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                    Name
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Email
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Role
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Status
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0"><?php echo e($user->id); ?></p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">
                                            <?php echo e($user->name); ?>

                                        </p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">
                                            <?php echo e($user->email); ?>

                                        </p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">
                                            <?php echo e(ucfirst($user->role)); ?>

                                        </p>
                                    </td>
                                    <td>
                                        <!--[if BLOCK]><![endif]--><?php if($user->status === 'active'): ?>
                                            <span class="inline-block rounded bg-green-500 text-white px-2 py-1 text-xs font-bold">
                                                <?php echo e(ucfirst($user->status)); ?>

                                            </span>
                                        <?php elseif($user->status === 'inactive'): ?>
                                            <span style="background-color:yellow " class="inline-block rounded bg-yellow-500 text-white px-2 py-1 text-xs font-bold">
                                                <?php echo e(ucfirst($user->status)); ?>

                                            </span>
                                        <?php elseif($user->status === 'suspended'): ?>
                                            <span class="inline-block rounded bg-red-500 text-white px-2 py-1 text-xs font-bold">
                                                <?php echo e(ucfirst($user->status)); ?>

                                            </span>
                                        <?php else: ?>
                                            <span style="background-color:green " class="inline-block rounded bg-green-500 text-white px-2 py-1 text-xs font-bold">
                                                <?php echo e(ucfirst($user->status)); ?>

                                            </span>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </td>
                                    <td>
                                        <button
                                            wire:click="edit(<?php echo e($user->id); ?>)"
                                            class="btn btn-primary btn-sm">
                                            Update Status
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for updating user status -->
    <!--[if BLOCK]><![endif]--><?php if($showModal): ?>
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Update User Status</h5>
                        <button type="button" class="close" wire:click="$set('showModal', false)">
                            &times;
                        </button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="updateStatus">
                            <div class="form-group">
                                <label for="selectedStatus">User Status</label>
                                <select id="selectedStatus" class="form-control" wire:model="selectedStatus">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['selectedStatus'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">
                                Update Status
                            </button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" wire:click="$set('showModal', false)">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div><?php /**PATH C:\Users\Administrator\Desktop\apps\quiz\resources\views/livewire/user/user-component.blade.php ENDPATH**/ ?>