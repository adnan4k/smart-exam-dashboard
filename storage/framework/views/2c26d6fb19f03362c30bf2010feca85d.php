
    <div class="main-content">
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('type.form', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-2288655037-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

        <div class="row">
            <div class="col-12">
                <div class="card mb-4 mx-4">
                    <div class="card-header pb-0">
                        <div class="d-flex flex-row justify-content-between">
                            <div>
                                <h5 class="mb-0">All Types</h5>
                            </div>
                            <button
                                style="background-color:#56C596;"
                                @click="$dispatch('typeModal')"
                                class="btn text-white bg-green-400 btn-sm mb-0"
                                type="button">+&nbsp; New Type</button>
                        </div>
                    </div>

                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                                        <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Description</th>
                                        
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td class="ps-4">
                                                <p class="text-xs font-weight-bold mb-0"><?php echo e($type->name); ?></p>
                                            </td>
                                            <td class="text-center">
                                                <p class="text-xs font-weight-bold mb-0"><?php echo e($type->description); ?></p>
                                            </td>
                                            
                                            <td class="text-center">
                                                <button
                                                    @click="$dispatch('edit-type', { type: <?php echo e($type->id); ?> })"
                                                    class="text-blue-500">
                                                    <i class="fa-regular fa-pen-to-square"></i>
                                                </button>
                                                <button
                                                    wire:click="$dispatch('openDeleteModal', { itemId: <?php echo e($type->id); ?>, model: '<?php echo e(addslashes(App\Models\Type::class)); ?>' })"
                                                    class="text-red-500">
                                                    <i class="fa-solid fa-trash"></i>
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
        </div>
    </div>
<?php /**PATH C:\Users\Administrator\Desktop\apps\quiz\resources\views/livewire/type/type-component.blade.php ENDPATH**/ ?>