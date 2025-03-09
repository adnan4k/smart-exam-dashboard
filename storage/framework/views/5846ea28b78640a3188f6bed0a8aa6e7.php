
<div class="main-content">
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('chapter.form', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-3836258991-0', $__slots ?? [], get_defined_vars());

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
                            <h5 class="mb-0">All chapters</h5>
                        </div>
                        <button
                            style="background-color:#56C596;"
                            @click="$dispatch('chapterModal')"
                            class="btn text-white bg-green-400 btn-sm mb-0"
                            chapter="button">+&nbsp; New chapter</button>
                    </div>
                </div>

                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Description</th>
                                    <th class=" text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Image</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $chapters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chapter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="ps-4">
                                            <p class="text-xs font-weight-bold mb-0"><?php echo e($chapter->name); ?></p>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-weight-bold mb-0"><?php echo e($chapter->description); ?></p>
                                        </td>
                                        <td class="text-center">
                                            <!--[if BLOCK]><![endif]--><?php if($chapter->image): ?>
                                                <img src="<?php echo e(asset('storage/'.$chapter->image)); ?>"
                                                     alt="<?php echo e($chapter->name); ?>"
                                                     class="w-8 h-8 object-cover rounded-lg border border-gray-200 shadow cursor-pointer hover:scale-105 transition-transform duration-300"
                                                     @click="$dispatch('showImage', { image: '<?php echo e($chapter->image); ?>' })">
                                            <?php else: ?>
                                                <span class="text-xs">No Image</span>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </td>
                                        <td class="text-center">
                                            <button
                                                @click="$dispatch('edit-chapter', { chapter: <?php echo e($chapter->id); ?> })"
                                                class="text-blue-500">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </button>
                                            <button
                                                wire:click="$dispatch('openDeleteModal', { itemId: <?php echo e($chapter->id); ?>, model: '<?php echo e(addslashes(App\Models\chapter::class)); ?>' })"
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
<?php /**PATH C:\Users\Administrator\Desktop\apps\quiz\resources\views/livewire/chapter/chapter-component.blade.php ENDPATH**/ ?>