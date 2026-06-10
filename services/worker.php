<?php

use Shared\EventBus;
use Shared\Database;
use Illuminate\Database\Capsule\Manager as Capsule;

require __DIR__ . '/../vendor/autoload';

// Load Environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

echo "Starting LGBTQ+ Matrimony Microservices Event Worker Daemon...\n";

// Register Channels to listen
$channels = [
    'user.registered',
    'profile.updated',
    'profile.viewed',
    'interest.sent',
    'match.mutual',
    'message.sent',
    'subscription.activated'
];

EventBus::subscribe($channels, function($channel, $payload) {
    echo "Received Event [{$channel}]: " . json_encode($payload) . "\n";

    try {
        switch ($channel) {
            case 'user.registered':
                // Boot profile-service DB
                Database::connect('matrimony_profile');
                Capsule::table('profiles')->insert([
                    'user_id' => $payload['id'],
                    'name' => $payload['name'],
                    'date_of_birth' => $payload['date_of_birth'] ?? null,
                    'gender_identity' => $payload['gender_identity'] ?? 'other',
                    'gender_custom' => $payload['gender_custom'] ?? '',
                    'sexual_orientation' => $payload['sexual_orientation'] ?? 'other',
                    'city' => $payload['city'] ?? '',
                    'country' => $payload['country'] ?? '',
                    'photos' => json_encode([]),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                // Sync discovery-service DB
                Database::connect('matrimony_discovery');
                Capsule::table('discovery_profiles')->insert([
                    'user_id' => $payload['id'],
                    'name' => $payload['name'],
                    'date_of_birth' => $payload['date_of_birth'] ?? null,
                    'gender_identity' => $payload['gender_identity'] ?? 'other',
                    'gender_custom' => $payload['gender_custom'] ?? '',
                    'sexual_orientation' => $payload['sexual_orientation'] ?? 'other',
                    'city' => $payload['city'] ?? '',
                    'country' => $payload['country'] ?? '',
                    'photos' => json_encode([]),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                echo "Worker: Bootstrapped Profile & Discovery records for User {$payload['id']}.\n";
                break;

            case 'profile.updated':
                // Sync discovery-service DB
                Database::connect('matrimony_discovery');
                $exists = Capsule::table('discovery_profiles')->where('user_id', $payload['user_id'])->exists();
                
                $data = [
                    'name' => $payload['name'],
                    'headline' => $payload['headline'],
                    'pronouns' => $payload['pronouns'],
                    'date_of_birth' => $payload['date_of_birth'],
                    'gender_identity' => $payload['gender_identity'],
                    'gender_custom' => $payload['gender_custom'],
                    'sexual_orientation' => $payload['sexual_orientation'],
                    'city' => $payload['city'],
                    'country' => $payload['country'],
                    'relationship_intent' => $payload['relationship_intent'],
                    'photos' => $payload['photos'],
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                if ($exists) {
                    Capsule::table('discovery_profiles')->where('user_id', $payload['user_id'])->update($data);
                } else {
                    $data['user_id'] = $payload['user_id'];
                    $data['created_at'] = date('Y-m-d H:i:s');
                    Capsule::table('discovery_profiles')->insert($data);
                }
                echo "Worker: Synced Discovery Profile for User {$payload['user_id']}.\n";
                break;

            case 'profile.viewed':
                // Log view in activity-service DB
                Database::connect('matrimony_activity');
                Capsule::table('activity_logs')->insert([
                    'user_id' => $payload['viewer_id'],
                    'target_id' => $payload['target_id'],
                    'action_type' => 'view',
                    'status' => 'completed',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                // Create alert notification inside notification-service for Target (if target is Premium, checked via client)
                // In actual deployment, we retrieve target's subscription tier first:
                Database::connect('matrimony_auth');
                $targetUser = Capsule::table('users')->where('id', $payload['target_id'])->first();

                if ($targetUser && $targetUser->tier === 'premium') {
                    // Send notification
                    Database::connect('matrimony_notification');
                    $notifId = Capsule::table('notifications')->insertGetId([
                        'user_id' => $payload['target_id'],
                        'title' => "Profile Viewed 👁️",
                        'message' => "A premium user viewed your profile recently.",
                        'type' => 'profile_view',
                        'is_read' => false,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    // Broadcast real-time websocket packet
                    EventBus::publish('notification.broadcast', [
                        'user_id' => $payload['target_id'],
                        'id' => $notifId,
                        'title' => "Profile Viewed 👁️",
                        'message' => "A premium user viewed your profile recently.",
                        'type' => 'profile_view',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
                break;

            case 'interest.sent':
                // Send notification
                Database::connect('matrimony_notification');
                $notifId = Capsule::table('notifications')->insertGetId([
                    'user_id' => $payload['target_id'],
                    'title' => "Interest Received ❤️",
                    'message' => "{$payload['sender_name']} expressed interest in your profile.",
                    'type' => 'like',
                    'is_read' => false,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                // Broadcast live notification
                EventBus::publish('notification.broadcast', [
                    'user_id' => $payload['target_id'],
                    'id' => $notifId,
                    'title' => "Interest Received ❤️",
                    'message' => "{$payload['sender_name']} expressed interest in your profile.",
                    'type' => 'like',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                break;

            case 'match.mutual':
                // Send mutual notifications to both
                Database::connect('matrimony_notification');
                $n1 = Capsule::table('notifications')->insertGetId([
                    'user_id' => $payload['user1_id'],
                    'title' => "Mutual Match! 🎉",
                    'message' => "You have a mutual match! You can now chat directly.",
                    'type' => 'like',
                    'is_read' => false,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                $n2 = Capsule::table('notifications')->insertGetId([
                    'user_id' => $payload['user2_id'],
                    'title' => "Mutual Match! 🎉",
                    'message' => "{$payload['user1_name']} matched back! Start a conversation.",
                    'type' => 'like',
                    'is_read' => false,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                EventBus::publish('notification.broadcast', [
                    'user_id' => $payload['user1_id'],
                    'id' => $n1,
                    'title' => "Mutual Match! 🎉",
                    'message' => "You have a mutual match! You can now chat directly.",
                    'type' => 'like',
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                EventBus::publish('notification.broadcast', [
                    'user_id' => $payload['user2_id'],
                    'id' => $n2,
                    'title' => "Mutual Match! 🎉",
                    'message' => "{$payload['user1_name']} matched back! Start a conversation.",
                    'type' => 'like',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                break;

            case 'message.sent':
                // Create chat alert notification
                Database::connect('matrimony_notification');
                $notifId = Capsule::table('notifications')->insertGetId([
                    'user_id' => $payload['recipient_id'],
                    'title' => "New Message 💬",
                    'message' => "You received a new message from {$payload['sender_name']}.",
                    'type' => 'message',
                    'is_read' => false,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                EventBus::publish('notification.broadcast', [
                    'user_id' => $payload['recipient_id'],
                    'id' => $notifId,
                    'title' => "New Message 💬",
                    'message' => "You received a new message from {$payload['sender_name']}.",
                    'type' => 'message',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                break;

            case 'subscription.activated':
                // Sync user premium status inside auth-service
                Database::connect('matrimony_auth');
                Capsule::table('users')->where('id', $payload['user_id'])->update([
                    'tier' => 'premium',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                // Create system alert
                Database::connect('matrimony_notification');
                $notifId = Capsule::table('notifications')->insertGetId([
                    'user_id' => $payload['user_id'],
                    'title' => "Subscription Active! 👑",
                    'message' => "Welcome to Proud Hearts Premium. All locked details are now unblurred.",
                    'type' => 'alert',
                    'is_read' => false,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                EventBus::publish('notification.broadcast', [
                    'user_id' => $payload['user_id'],
                    'id' => $notifId,
                    'title' => "Subscription Active! 👑",
                    'message' => "Welcome to Proud Hearts Premium. All locked details are now unblurred.",
                    'type' => 'alert',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                break;
        }
    } catch (\Exception $e) {
        echo "Worker Error processing event: " . $e->getMessage() . "\n";
    }
});
