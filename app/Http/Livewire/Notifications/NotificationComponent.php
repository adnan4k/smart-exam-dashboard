<?php

namespace App\Http\Livewire\Notifications;

use App\Models\AppNotification;
use Livewire\Component;
use Livewire\Attributes\On;
use Masmerise\Toaster\Toaster;

class NotificationComponent extends Component
{
    public $notifications;
    public $selectedNotification;
    public $selectedComments = [];
    public $showDeleteModal = false;
    public $notificationToDelete;

    #[On('refreshTable')]
    public function render()
    {
        $this->notifications = AppNotification::orderBy('created_at', 'desc')->get();

        return view('livewire.notifications.notification-component');
    }

    public function confirmDelete($notificationId)
    {
        $this->notificationToDelete = AppNotification::findOrFail($notificationId);
        $this->showDeleteModal = true;
    }

    public function deleteNotification()
    {
        if ($this->notificationToDelete) {
            try {
                $notificationTitle = $this->notificationToDelete->title;
                $this->notificationToDelete->delete();
                
                $this->showDeleteModal = false;
                $this->notificationToDelete = null;
                $this->notifications = AppNotification::orderBy('created_at', 'desc')->get();
                
                Toaster::success("Notification '{$notificationTitle}' has been deleted successfully.");
            } catch (\Exception $e) {
                Toaster::error('Failed to delete notification. Please try again.');
            }
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->notificationToDelete = null;
    }

    public function showComments($notificationId)
    {
        $this->selectedNotification = AppNotification::with(['comments.user'])->findOrFail($notificationId);
        $this->selectedComments = $this->selectedNotification->comments;
    }

    public function editNotification($notificationId)
    {
        $this->dispatch('edit-notification', notificationId: $notificationId);
    }
}


