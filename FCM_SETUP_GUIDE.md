# Firebase Cloud Messaging (FCM) Setup Guide

This application uses **Firebase Cloud Messaging HTTP v1 API** (100% modern, zero legacy dependencies) with topic-based messaging for push notifications.

## Overview

- **Modern Approach**: Pure FCM HTTP v1 API - NO legacy server keys
- **Topics**: Each user subscribes to ONE topic based on their exam type (e.g., `1`, `2`, `3`)
- **Authentication**: OAuth 2.0 using Firebase service account
- **Scalability**: Topic-based messaging (unlimited subscribers per topic)
- **Security**: Comprehensive error handling and logging

---

## Architecture

### Server-Side (Laravel)
- ✅ Sends notifications to topics using FCM HTTP v1 API
- ✅ Provides topic list for each user based on subscriptions
- ✅ OAuth 2.0 authentication with JWT
- ✅ NO legacy server keys required

### Client-Side (Mobile Apps)
- ✅ Handles topic subscription using Firebase SDK
- ✅ Gets single topic from server when registering FCM token
- ✅ Subscribes to one topic per user (based on their exam type)
- ✅ Full control over topic management

---

## Setup Instructions

### 1. Create Firebase Project

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Click **"Add project"** or select an existing project
3. Follow the setup wizard

### 2. Get Firebase Project ID

1. In Firebase Console → Project settings
2. Copy your **Project ID** (not Project Number)
3. Add to `.env`:
   ```env
   FCM_PROJECT_ID=your-project-id
   ```

### 3. Download Service Account JSON

1. In Firebase Console → Project settings → **Service accounts** tab
2. Click **"Generate new private key"**
3. Download the JSON file
4. Rename it to `firebase-service-account.json`
5. Place it in: `storage/app/firebase-service-account.json`
6. Add to `.env`:
   ```env
   FCM_SERVICE_ACCOUNT_PATH=/full/path/to/storage/app/firebase-service-account.json
   ```

> **Security**: This file is in `.gitignore` - NEVER commit it to version control!

### 4. Enable Cloud Messaging API

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Select your Firebase project
3. Go to **APIs & Services** → **Library**
4. Search for **"Firebase Cloud Messaging API"**
5. Click **Enable**

### 5. Update .env File

Your `.env` should contain:

```env
# Firebase Cloud Messaging Configuration (HTTP v1 API - Modern Approach)
FCM_PROJECT_ID=your-project-id
FCM_SERVICE_ACCOUNT_PATH=/full/path/to/storage/app/firebase-service-account.json
```

That's it! **No legacy server keys needed!** 🎉

---

## API Endpoints

### 1. Register FCM Token
```http
POST /api/fcm/register-token
Content-Type: application/json

{
  "user_id": 1,
  "fcm_token": "device_fcm_token_here"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "FCM token registered successfully.",
  "topic": "1",
  "type_id": 1
}
```

**Important**: The response contains the single topic the client should subscribe to based on the user's active paid subscription. Each user has only ONE exam type.

---

### 2. Get User Topic
```http
POST /api/fcm/user-topic
Content-Type: application/json

{
  "user_id": 1
}
```

**Response:**
```json
{
  "status": "success",
  "topic": "1",
  "type_id": 1
}
```

**Use Case**: Fetch current topic when user's subscription changes.

---

### 3. Send Notification (Admin)
```http
POST /api/notifications
Content-Type: multipart/form-data

{
  "title": "New Exam Available",
  "body": "Check out the latest questions for your exam type!",
  "image": [file upload],
  "type_id": 2
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "title": "New Exam Available",
    "body": "Check out the latest questions...",
    "image_url": "http://yourapp.com/storage/notifications/image.jpg",
    "type_id": 2,
    "like_count": 0,
    "dislike_count": 0,
    "comment_count": 0,
    "created_at": "2025-01-05T12:00:00.000000Z"
  }
}
```

**Behavior**: Automatically sends FCM notification to topic `2` (all users subscribed to exam type 2).

---

## Mobile App Integration

### Android (Kotlin + Firebase SDK)

#### 1. Setup Firebase

Add to `app/build.gradle`:
```gradle
dependencies {
    implementation 'com.google.firebase:firebase-messaging:23.4.0'
}
```

Add `google-services.json` to `app/` directory.

#### 2. Register Token and Subscribe to Topic

```kotlin
import com.google.firebase.messaging.FirebaseMessaging

class FCMManager {

    suspend fun registerTokenAndSubscribe(userId: Int) {
        // Get FCM token
        val token = FirebaseMessaging.getInstance().token.await()

        // Register with server
        val response = apiService.registerFcmToken(userId, token)

        // Subscribe to the topic returned by server (only one topic per user)
        response.topic?.let { topic ->
            FirebaseMessaging.getInstance().subscribeToTopic(topic).await()
            Log.d("FCM", "Subscribed to topic: $topic")
        }
    }

    suspend fun syncTopic(userId: Int, oldTopic: String? = null) {
        // Unsubscribe from old topic if subscription changed
        oldTopic?.let {
            FirebaseMessaging.getInstance().unsubscribeFromTopic(it).await()
            Log.d("FCM", "Unsubscribed from old topic: $it")
        }

        // Get current topic from server
        val response = apiService.getUserTopic(userId)

        // Subscribe to new topic
        response.topic?.let { topic ->
            FirebaseMessaging.getInstance().subscribeToTopic(topic).await()
            Log.d("FCM", "Subscribed to new topic: $topic")
        }
    }
}
```

#### 3. Handle Incoming Messages

```kotlin
import com.google.firebase.messaging.FirebaseMessagingService
import com.google.firebase.messaging.RemoteMessage

class MyFirebaseMessagingService : FirebaseMessagingService() {

    override fun onMessageReceived(message: RemoteMessage) {
        // Handle data payload
        val data = message.data
        val notificationId = data["id"]
        val title = data["title"]
        val body = data["body"]
        val imageUrl = data["image_url"]
        val typeId = data["type_id"]

        // Show notification
        showNotification(title, body, imageUrl)
    }

    override fun onNewToken(token: String) {
        // Token refreshed, update server
        val userId = getUserId() // Get from local storage
        if (userId != null) {
            updateTokenOnServer(userId, token)
        }
    }
}
```

#### 4. Add to AndroidManifest.xml

```xml
<service
    android:name=".MyFirebaseMessagingService"
    android:exported="false">
    <intent-filter>
        <action android:name="com.google.firebase.MESSAGING_EVENT" />
    </intent-filter>
</service>
```

---

### iOS (Swift + Firebase SDK)

#### 1. Setup Firebase

Add to `Podfile`:
```ruby
pod 'Firebase/Messaging'
```

Add `GoogleService-Info.plist` to your Xcode project.

#### 2. Register Token and Subscribe to Topic

```swift
import FirebaseMessaging

class FCMManager {

    func registerTokenAndSubscribe(userId: Int) async throws {
        // Get FCM token
        guard let token = try await Messaging.messaging().token() else {
            throw FCMError.noToken
        }

        // Register with server
        let response = try await apiService.registerFcmToken(userId: userId, token: token)

        // Subscribe to the topic returned by server (only one topic per user)
        if let topic = response.topic {
            try await Messaging.messaging().subscribe(toTopic: topic)
            print("Subscribed to topic: \(topic)")
        }
    }

    func syncTopic(userId: Int, oldTopic: String? = nil) async throws {
        // Unsubscribe from old topic if subscription changed
        if let oldTopic = oldTopic {
            try await Messaging.messaging().unsubscribe(fromTopic: oldTopic)
            print("Unsubscribed from old topic: \(oldTopic)")
        }

        // Get current topic from server
        let response = try await apiService.getUserTopic(userId: userId)

        // Subscribe to new topic
        if let topic = response.topic {
            try await Messaging.messaging().subscribe(toTopic: topic)
            print("Subscribed to new topic: \(topic)")
        }
    }
}
```

#### 3. Handle Incoming Messages in AppDelegate

```swift
import FirebaseMessaging

extension AppDelegate: MessagingDelegate {

    func messaging(_ messaging: Messaging, didReceiveRegistrationToken fcmToken: String?) {
        // Token refreshed, update server
        if let userId = getUserId(), let token = fcmToken {
            Task {
                try await updateTokenOnServer(userId: userId, token: token)
            }
        }
    }
}

extension AppDelegate: UNUserNotificationCenterDelegate {

    func userNotificationCenter(_ center: UNUserNotificationCenter,
                               willPresent notification: UNNotification) async
                               -> UNNotificationPresentationOptions {
        let userInfo = notification.request.content.userInfo

        // Handle notification data
        let notificationId = userInfo["id"] as? String
        let title = userInfo["title"] as? String
        let body = userInfo["body"] as? String
        let imageUrl = userInfo["image_url"] as? String

        return [[.banner, .sound, .badge]]
    }
}
```

---

## How It Works

### Flow Diagram

```
1. User Login/Subscription
   ↓
2. App gets FCM token from Firebase SDK
   ↓
3. App calls POST /api/fcm/register-token
   ↓
4. Server returns topic: "1" (for exam type 1)
   ↓
5. App subscribes to topic "1" using Firebase SDK
   ↓
6. Admin creates notification with type_id=1
   ↓
7. Server sends to topic "1" using HTTP v1 API
   ↓
8. All devices subscribed to topic "1" receive notification
```

### Topic Naming Convention

- Topics are named simply by their type ID: `1`, `2`, `3`, etc.
- Generated by: `FcmService::getTopicName($typeId)` which returns `(string)$typeId`
- Each user subscribes to only ONE topic based on their active **paid** subscription

### When to Sync Topic

Call `syncTopic()` when:
- ✅ User purchases a new subscription (different exam type)
- ✅ User's subscription changes from one exam type to another
- ✅ User logs in (to ensure topic is up to date)
- ✅ App launches

---

## Server Implementation Details

### FcmService Class (`app/Services/FcmService.php`)

**Methods**:
- `sendToTopic($topic, $data, $notification)` - Send to topic using FCM HTTP v1
- `sendToTokens($tokens, $data, $notification)` - Send to individual tokens (fallback)
- `getTopicName($typeId)` - Generate topic name

**Features**:
- ✅ OAuth 2.0 token generation with JWT
- ✅ Token caching (55 min TTL)
- ✅ RSA private key signing
- ✅ Comprehensive error handling
- ✅ Detailed logging

### NotificationController Updates

**Methods**:
- `registerToken()` - Save token and return single topic
- `getUserTopic()` - Get topic for user based on their subscription
- `dispatchFcm()` - Send notification to topic

---

## Logging

All FCM operations are logged to `storage/logs/laravel.log`:

**Success Logs**:
```
[INFO] FCM notification sent successfully to topic
[INFO] FCM notification dispatched successfully
```

**Error Logs**:
```
[ERROR] FCM notification failed
[ERROR] FCM OAuth token request failed
[ERROR] FCM dispatch exception
```

---

## Testing

### 1. Test Token Registration
```bash
curl -X POST http://yourapp.com/api/fcm/register-token \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "fcm_token": "test_token_123"
  }'
```

Expected response with topics list.

### 2. Test Get Topic
```bash
curl -X POST http://yourapp.com/api/fcm/user-topic \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1
  }'
```

### 3. Test Notification Send
```bash
curl -X POST http://yourapp.com/api/notifications \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Test Notification",
    "body": "This is a test",
    "type_id": 2
  }'
```

### 4. Check Logs
```bash
tail -f storage/logs/laravel.log
```

---

## Troubleshooting

### Error: "FCM service account file not found"
- ✅ Verify `FCM_SERVICE_ACCOUNT_PATH` in `.env` is correct
- ✅ Check file exists at `storage/app/firebase-service-account.json`
- ✅ Ensure file permissions are readable

### Error: "Failed to obtain FCM access token"
- ✅ Verify service account JSON is valid
- ✅ Check Firebase Cloud Messaging API is enabled
- ✅ Verify service account has correct permissions

### Notifications not received
- ✅ Verify device token is registered
- ✅ Check client subscribed to correct topic
- ✅ Verify user has active paid subscription
- ✅ Verify notification has valid `type_id`
- ✅ Check Firebase Cloud Messaging API is enabled
- ✅ Review logs in `storage/logs/laravel.log`
- ✅ Test with Firebase Console's test message feature

---

## Advantages of This Approach

| Feature | Legacy API | Modern HTTP v1 |
|---------|-----------|----------------|
| **Server Key** | Required | NOT REQUIRED ✅ |
| **Authentication** | Static key | OAuth 2.0 ✅ |
| **Topic Management** | Server-side (IID API) | Client-side (SDK) ✅ |
| **Security** | Basic | Service account ✅ |
| **Token Limit** | 1000 per request | Unlimited (topics) ✅ |
| **Scalability** | Limited | Unlimited ✅ |
| **Flexibility** | Low | High ✅ |
| **Future-Proof** | Being deprecated | Modern standard ✅ |

---

## Migration Summary

✅ **Removed**:
- Legacy server key (`FCM_LEGACY_SERVER_KEY`)
- Server-side topic subscription endpoints
- IID API dependencies

✅ **Added**:
- Pure FCM HTTP v1 API
- Client-side topic management (one topic per user)
- OAuth 2.0 authentication
- Topic endpoint for clients

✅ **Result**:
- 100% modern implementation
- No legacy dependencies
- Better security
- More scalable
- Future-proof

---

## Support

For issues:
- Check `storage/logs/laravel.log` for detailed errors
- Review Firebase Console → Cloud Messaging for metrics
- Verify service account JSON is valid
- Ensure Firebase Cloud Messaging API is enabled

---

**Last Updated**: January 5, 2026
**FCM API Version**: HTTP v1 (Modern)
**Laravel Version**: 11.0
**Status**: ✅ Production Ready - Zero Legacy Dependencies
