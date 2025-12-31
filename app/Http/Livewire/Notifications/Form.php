<?php

namespace App\Http\Livewire\Notifications;

use App\Models\AppNotification;
use App\Models\Type;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class Form extends Component
{
    use WithFileUploads;

    public $id;
    public $title;
    public $body;
    public $image;
    public $existing_image_url;
    public $type_id;
    public $openModal = false;
    public $is_edit = false;

    protected $listeners = ['notificationModal' => 'openModal', 'edit-notification' => 'edit'];

    protected $rules = [
        'title' => 'required|string|max:255',
        'body' => 'required|string',
        'image' => 'nullable|image|max:2048', // Max 2MB
        'type_id' => 'required|exists:types,id',
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
            'type_id' => $this->type_id,
        ];

        // Handle image upload
        if ($this->image) {
            // Delete old image if editing
            if ($this->is_edit && $this->existing_image_url) {
                // Get raw path (not the full URL from accessor)
                $oldImagePath = $this->existing_image_url;
                // If it's a full URL, extract just the path part
                if (filter_var($oldImagePath, FILTER_VALIDATE_URL)) {
                    $oldImagePath = str_replace(asset('storage/'), '', $oldImagePath);
                }
                Storage::disk('public')->delete($oldImagePath);
            }
            $data['image_url'] = $this->image->store('notifications', 'public');
        } elseif ($this->is_edit && $this->existing_image_url) {
            // Keep existing image if not changed during edit
            // Get raw path (not the full URL from accessor)
            $existingPath = $this->existing_image_url;
            // If it's a full URL, extract just the path part
            if (filter_var($existingPath, FILTER_VALIDATE_URL)) {
                $existingPath = str_replace(asset('storage/'), '', $existingPath);
            }
            $data['image_url'] = $existingPath;
        }

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
        $this->reset(['id', 'title', 'body', 'image', 'existing_image_url', 'type_id', 'is_edit']);
    }

    #[On('edit-notification')]
    public function edit($notificationId = null)
    {
        // Handle event parameter - can be array with 'notificationId' key, object, or direct ID
        if (is_array($notificationId) && isset($notificationId['notificationId'])) {
            $notificationId = $notificationId['notificationId'];
        } elseif (is_object($notificationId) && isset($notificationId->notificationId)) {
            $notificationId = $notificationId->notificationId;
        } elseif ($notificationId === null) {
            // Try to get from request if not provided
            $notificationId = request()->input('notificationId') ?? request()->input('id');
            if (!$notificationId) {
                session()->flash('error', 'Notification ID is required.');
                return;
            }
        }

        $notification = AppNotification::findOrFail($notificationId);

        $this->id = $notification->id;
        $this->title = $notification->title;
        $this->body = $notification->body;
        // Store the raw path for editing (not the full URL from accessor)
        $this->existing_image_url = $notification->getAttributes()['image_url'] ?? null;
        $this->type_id = $notification->type_id;
        $this->image = null; // Reset file input
        $this->is_edit = true;
        $this->openModal = true;
    }

    private function dispatchFcm(AppNotification $notification): void
    {
        $serverKey = Config::get('services.fcm.server_key');
        if (!$serverKey) {
            return;
        }

        // If notification doesn't have a type_id, don't send (type_id is required)
        if (!$notification->type_id) {
            return;
        }

        // Get FCM tokens only for users who have an active subscription to the notification's exam type
        $tokens = User::whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->whereHas('subscriptions', function($query) use ($notification) {
                $query->where('type_id', $notification->type_id)
                      ->where('payment_status', 'paid');
            })
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
                'type_id' => (string) $notification->type_id,
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
        return view('livewire.notifications.form', [
            'types' => Type::orderBy('name')->get(),
        ]);
    }
}


