<?php

namespace App\Http\Livewire\Videos;

use App\Models\Chapter;
use App\Models\Subject;
use App\Models\Type;
use App\Models\Video;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class VideoComponent extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $filterTypeId = '';
    public $filterSubjectId = '';
    public $filterChapterId = '';

    public $showDeleteModal = false;
    public $videoToDelete;

    public $showPlayerModal = false;
    public $videoToPlay;

    protected $queryString = [
        'search'          => ['except' => ''],
        'filterTypeId'    => ['except' => ''],
        'filterSubjectId' => ['except' => ''],
        'filterChapterId' => ['except' => ''],
    ];

    public function updatedSearch()          { $this->resetPage(); }
    public function updatedFilterTypeId()    { $this->filterSubjectId = ''; $this->filterChapterId = ''; $this->resetPage(); }
    public function updatedFilterSubjectId() { $this->filterChapterId = ''; $this->resetPage(); }
    public function updatedFilterChapterId() { $this->resetPage(); }

    public function clearFilters()
    {
        $this->reset(['search', 'filterTypeId', 'filterSubjectId', 'filterChapterId']);
        $this->resetPage();
    }

    #[On('refreshVideos')]
    public function refreshVideos()
    {
        $this->resetPage();
    }

    public function render()
    {
        $videos = Video::with(['subject', 'chapter', 'type', 'user'])
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('title', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->when($this->filterTypeId, fn ($q) => $q->where('type_id', $this->filterTypeId))
            ->when($this->filterSubjectId, fn ($q) => $q->where('subject_id', $this->filterSubjectId))
            ->when($this->filterChapterId, fn ($q) => $q->where('chapter_id', $this->filterChapterId))
            ->ordered()
            ->paginate(10);

        return view('livewire.videos.video-component', [
            'videos'   => $videos,
            'types'    => Type::orderBy('name')->get(),
            'subjects' => Subject::when($this->filterTypeId, fn ($q) => $q->where('type_id', $this->filterTypeId))
                            ->orderBy('name')->get(),
            'chapters' => Chapter::orderBy('name')->get(),
        ]);
    }

    /* ------------------------------------------------------------------ */
    /* Row actions                                                         */
    /* ------------------------------------------------------------------ */

    public function editVideo($videoId)
    {
        $this->dispatch('edit-video', videoId: $videoId);
    }

    public function toggleActive($videoId)
    {
        $video = Video::findOrFail($videoId);
        $video->update(['is_active' => !$video->is_active]);

        Toaster::success($video->is_active ? 'Video published.' : 'Video hidden from the app.');
    }

    public function play($videoId)
    {
        $this->videoToPlay = Video::findOrFail($videoId);
        $this->showPlayerModal = true;
    }

    public function closePlayer()
    {
        $this->showPlayerModal = false;
        $this->videoToPlay = null;
    }

    public function confirmDelete($videoId)
    {
        $this->videoToDelete = Video::with(['subject', 'chapter', 'type'])->findOrFail($videoId);
        $this->showDeleteModal = true;
    }

    public function deleteVideo()
    {
        if (!$this->videoToDelete) {
            return;
        }

        try {
            $title = $this->videoToDelete->title;
            // Model's deleting hook removes the stored file and thumbnail.
            $this->videoToDelete->delete();

            $this->showDeleteModal = false;
            $this->videoToDelete = null;
            $this->resetPage();

            Toaster::success("Video '{$title}' has been deleted successfully.");
        } catch (\Exception $e) {
            Toaster::error('Failed to delete video. Please try again.');
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->videoToDelete = null;
    }
}
