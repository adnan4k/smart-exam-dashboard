<div class="main-content">
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('sections.form', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-2333981373-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('components.delete-modal', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-2333981373-1', $__slots ?? [], get_defined_vars());

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
                            <h5 class="mb-0">All Sections</h5>
                        </div>
                        <button
                            class="bg-[#56C596]  hover:bg-green-600 text-white p-2 "
                            style="background-color: #56C596 !important; color: white !important;"
                            @click="$dispatch('sectionModal')"
                            type="button">
                            +&nbsp; New section
                        </button>


                    </div>
                </div>
                <div>

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
                                        Image
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Title
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Section Type
                                    </th>
                                    <!-- <th class=" text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Content
                                    </th> -->



                                    <th class=" text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num => $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0"><?php echo e($num +1); ?></p>
                                    </td>
                                    <td>
                                        <div>
                                            <img src="<?php echo e(asset('storage/' . $section->image)); ?>" class="h-12 w-24 me-3" alt="section Image">
                                        </div>
                                    </td>
                                    <td class="">
                                        <p class="text-xs font-weight-bold mb-0"><?php echo e($section->title); ?></p>
                                    </td>
                                    <td class="">
                                        <p class="text-xs font-weight-bold mb-0"><?php echo e($section->type); ?></p>
                                    </td>
                                    <!-- <td class="">
                                        <p class="text-xs wrap font-weight-bold mb-0"><?php echo $section->content; ?></p>
                                    </td> -->


                                    <td class="">

                                        <button
                                            @click="$dispatch('edit-section',{section:<?php echo e($section->id); ?>})"

                                            class="">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                        <button
                                            wire:click="$dispatch('openDeleteModal', { itemId: <?php echo e($section->id); ?>, model: '<?php echo e(addslashes(App\Models\Section::class)); ?>' })">
                                            <i class="fa-solid fa-trash text-red-400"></i>
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

</div><?php /**PATH /home/faysal/Desktop/apps/kasma/tour-travel-dashbaord/resources/views/livewire/sections/section-component.blade.php ENDPATH**/ ?>