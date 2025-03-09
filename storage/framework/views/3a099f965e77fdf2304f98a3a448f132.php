<div class="main-content">
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('questions.form', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-4058190345-0', $__slots ?? [], get_defined_vars());

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
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">All Questions</h5>
                    <button
                        style="background-color:#56C596;"
                        @click="$dispatch('questionModal')"
                        class="btn text-white btn-sm px-3"
                        type="button">
                        <i class="fa-solid fa-plus"></i> New Question
                    </button>
                </div>

                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">#</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Question</th>
                                    <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Subject</th>
                                    <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Year Group</th>
                                    <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Type</th>
                                    <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="ps-4 text-xs"><?php echo e($num + 1); ?></td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0"><?php echo e(Str::limit($question->question_text, 50)); ?></p>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary text-white"><?php echo e($question->subject->name); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info text-white"><?php echo e($question->yearGroup->year); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?php echo e($question->type == 'exam' ? 'bg-danger' : 'bg-success'); ?> text-white">
                                                <?php echo e(ucfirst($question->type ? $question->type->name : "" )); ?>

                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button
                                                @click="$dispatch('edit-question', { question: <?php echo e($question->id); ?> })"
                                                class="btn btn-sm text-primary"
                                                data-bs-toggle="tooltip" title="Edit">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <button
                                                wire:click="$dispatch('openDeleteModal', { itemId: <?php echo e($question->id); ?>, model: '<?php echo e(addslashes(App\Models\Question::class)); ?>' })"
                                                class="btn btn-sm text-danger"
                                                data-bs-toggle="tooltip" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </tbody>
                        </table>
                    </div>

                    <!--[if BLOCK]><![endif]--><?php if($questions->isEmpty()): ?>
                        <div class="text-center p-3 text-muted">No questions available.</div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\Administrator\Desktop\apps\quiz\resources\views/livewire/questions/question-component.blade.php ENDPATH**/ ?>