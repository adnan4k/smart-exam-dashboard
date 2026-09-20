<?php

namespace App\Http\Livewire\Subjects;

use App\Models\Subject;
use App\Services\SubjectMergeService;
use Livewire\Attributes\On;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class SubjectComponent extends Component
{
    public $subjects;
    public $showDeleteModal = false;
    public $subjectToDelete;

    // Subject Merge Modal state
    public $showMergeModal = false;
    public $targetSubjectId = null;
    public $sourceSubjectIds = [];
    public $previewData = null;

    #[On('refreshTable')]
    public function render()
    {
        $this->subjects = Subject::with(['type', 'package'])
            ->withCount(['questions', 'notes', 'videos'])
            ->orderBy('name', 'asc')
            ->orderBy('year', 'desc')
            ->get();

        return view('livewire.subjects.subject-component', [
            'allSubjects' => $this->subjects,
        ]);
    }

    public function confirmDelete($subjectId)
    {
        $this->subjectToDelete = Subject::with('type')->withCount('questions')->findOrFail($subjectId);
        $this->showDeleteModal = true;
    }

    public function deleteSubject()
    {
        if ($this->subjectToDelete) {
            try {
                $subjectName = $this->subjectToDelete->name;
                $this->subjectToDelete->delete();
                
                $this->showDeleteModal = false;
                $this->subjectToDelete = null;
                
                // Refresh the subjects list
                $this->subjects = Subject::with(['type', 'package'])
                    ->withCount(['questions', 'notes', 'videos'])
                    ->get();
                
                Toaster::success("Subject '{$subjectName}' has been deleted successfully.");
            } catch (\Exception $e) {
                Toaster::error('Failed to delete subject. Please try again.');
            }
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->subjectToDelete = null;
    }

    /**
     * Open the merge subjects modal.
     * Optionally preselect a target subject.
     */
    public function openMergeModal($targetId = null)
    {
        $target = $targetId ? Subject::find($targetId) : $this->subjects->first();
        $this->targetSubjectId = $target?->id;
        $this->sourceSubjectIds = [];
        $this->previewData = null;

        // Auto-suggest source subjects with similar names if a target is chosen
        if ($target) {
            $normalizedTarget = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $target->name));
            $suggested = $this->subjects->filter(function ($s) use ($target, $normalizedTarget) {
                if ($s->id === $target->id) {
                    return false;
                }
                $norm = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $s->name));
                return str_contains($norm, $normalizedTarget)
                    || str_contains($normalizedTarget, $norm)
                    || (similar_text($s->name, $target->name, $percent) && $percent > 70);
            })->pluck('id')->map(fn ($id) => (int) $id)->toArray();

            $this->sourceSubjectIds = $suggested;
        }

        $this->refreshPreview();
        $this->showMergeModal = true;
    }

    public function updatedTargetSubjectId()
    {
        // Remove target from selected sources if it was checked
        $this->sourceSubjectIds = array_values(array_filter(
            $this->sourceSubjectIds,
            fn ($id) => (int) $id !== (int) $this->targetSubjectId
        ));

        $this->refreshPreview();
    }

    public function updatedSourceSubjectIds()
    {
        $this->refreshPreview();
    }

    public function refreshPreview()
    {
        if ($this->targetSubjectId && !empty($this->sourceSubjectIds)) {
            $service = app(SubjectMergeService::class);
            $this->previewData = $service->preview((int) $this->targetSubjectId, $this->sourceSubjectIds);
        } else {
            $this->previewData = null;
        }
    }

    public function closeMergeModal()
    {
        $this->showMergeModal = false;
        $this->targetSubjectId = null;
        $this->sourceSubjectIds = [];
        $this->previewData = null;
    }

    /**
     * Execute the merge operation.
     */
    public function executeMerge()
    {
        if (!$this->targetSubjectId) {
            Toaster::error('Please choose a target subject.');
            return;
        }

        if (empty($this->sourceSubjectIds)) {
            Toaster::error('Please select at least one source subject to merge.');
            return;
        }

        try {
            $mergeService = app(SubjectMergeService::class);
            $result = $mergeService->merge((int) $this->targetSubjectId, $this->sourceSubjectIds);

            Toaster::success("Merged {$result['merged_count']} subject(s) into '{$result['target_name']}'. Moved {$result['questions_moved']} question(s)!");

            $this->closeMergeModal();
            $this->dispatch('refreshTable');
        } catch (\Exception $e) {
            Toaster::error('Merge failed: ' . $e->getMessage());
        }
    }
}
