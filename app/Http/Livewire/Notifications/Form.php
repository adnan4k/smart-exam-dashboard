<?php

namespace App\Http\Livewire\Notifications;

use App\Models\AppNotification;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class Form extends Component
{
    public $id;
    public $title;
    public $body;
    public $image_url;
    public $openModal = false;
    public $is_edit = false;

    protected $listeners = ['notificationModal' => 'openModal', 'edit-notification' => 'edit'];

    protected $rules = [
        'title' => 'required|string|max:255',
        'body' => 'required|string',
        'image_url' => 'nullable|url',
    ];

    public function openModal()
    {
        $this->openModal = true;
    }

    public function saveNotification()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'body' => $this->body,
            'image_url' => $this->image_url,
        ];

        if ($this->is_edit && $this->id) {
            $notification = AppNotification::findOrFail($this->id);
            $notification->update($data);
        } else {
            $notification = AppNotification::create($data);
            // Send FCM push notification to all users when creating new notification
            $this->dispatchFcm($notification);
        }

        $this->resetForm();
        $this->openModal = false;
        $this->dispatch('refreshTable');
    }

    public function resetForm()
    {
        $this->reset(['id', 'title', 'body', 'image_url', 'is_edit']);
    }

    #[On('edit-notification')]
    public function edit($notificationId)
    {
        $notification = AppNotification::findOrFail($notificationId);

        $this->id = $notification->id;
        $this->title = $notification->title;
        $this->body = $notification->body;
        $this->image_url = $notification->image_url;
        $this->is_edit = true;
        $this->openModal = true;
    }

    private function dispatchFcm(AppNotification $notification): void
    {
        $serverKey = Config::get('services.fcm.server_key');
        if (!$serverKey) {
            return;
        }

        $tokens = User::whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->pluck('fcm_token')
            ->all();

        if (empty($tokens)) {
            return;
        }

        $payload = [
            'registration_ids' => $tokens,
            'data' => [
                'id' => (string) $notification->id,
                'title' => $notification->title,
                'body' => $notification->body,
                'image_url' => $notification->image_url ?? '',
                'like_count' => (string) $notification->like_count,
                'dislike_count' => (string) $notification->dislike_count,
                'comment_count' => (string) $notification->comment_count,
                'created_at' => $notification->created_at->toIso8601String(),
            ],
        ];

        Http::withHeaders([
            'Authorization' => 'key=' . $serverKey,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', $payload);
    }

    public function render()
    {
        return view('livewire.notifications.form');
    }
}


