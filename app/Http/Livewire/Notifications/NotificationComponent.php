<?php

namespace App\Http\Livewire\Notifications;

use App\Models\AppNotification;
use Livewire\Component;
use Livewire\Attributes\On;

class NotificationComponent extends Component
{
    public $notifications;
    public $selectedNotification;
    public $selectedComments = [];

    #[On('refreshTable')]
    public function render()
    {
        $this->notifications = AppNotification::orderBy('created_at', 'desc')->get();

        return view('livewire.notifications.notification-component');
    }

    public function deleteNotification($notificationId)
    {
        $notification = AppNotification::findOrFail($notificationId);
        $notification->delete();
        $this->notifications = AppNotification::orderBy('created_at', 'desc')->get();
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


