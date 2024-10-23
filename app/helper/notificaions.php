<?php

use App\Models\User;
use App\Models\Notification;

function notificationCreate($userId, $type, $extraUserId = null)
{
    // Fetch the user's name
    $user = User::find($userId);
    $userName = $user ? $user->name : 'User';

    // Fetch the extra user's name if provided (for invitation scenarios)
    $extraUser = $extraUserId ? User::find($extraUserId) : null;
    $extraUserName = $extraUser ? $extraUser->name : '';

    // Define the message based on the notification type
    $message = '';

    switch ($type) {
        case 'profile_view':
            $message = "{$userName} has viewed your profile.";
            break;

        case 'invitation_send':
            $message = "You have sent an invitation to {$extraUserName}.";
            break;

        case 'invitation_received':
            $message = "You have received an invitation from {$extraUserName}.";
            break;

        case 'invitation_accept':
            $message = "{$extraUserName} has accepted your invitation.";
            break;

        case 'invitation_reject':
            $message = "{$extraUserName} has rejected your invitation.";
            break;

        case 'received_invitation_accept':
            $message = "You have accepted the invitation from {$extraUserName}.";
            break;

        case 'received_invitation_reject':
            $message = "You have rejected the invitation from {$extraUserName}.";
            break;

        case 'payment':
            $message = "{$userName}, your payment of $123.45 has been successfully processed.";
            break;

        case 'message':
            $message = "{$userName} has sent you a new message. Click here to read it.";
            break;

        case 'connection_request':
            $message = "{$userName} has sent you a connection request. Click here to accept or decline.";
            break;

        case 'profile_update':
            $message = "{$userName} has updated their profile information. Click here to check the latest updates.";
            break;

        case 'comment':
            $message = "{$userName} has commented on your post. Click here to view the comment.";
            break;

        case 'friendship':
            $message = "You and {$userName} are now friends. Click here to view their profile.";
            break;

        case 'event_reminder':
            $message = "Reminder: You have an upcoming event scheduled for tomorrow.";
            break;

        case 'system_alert':
            $message = "System Alert: Please review the latest updates to our privacy policy.";
            break;

        default:
            $message = "You have a new notification.";
            break;
    }

    // Create the notification
    Notification::create([
        'user_id' => $userId,
        'type' => $type,
        'message' => $message,
        'read' => false,
    ]);
}


