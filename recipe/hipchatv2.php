<?php

/**
 * Hipchat tasks using Version 2 of the API which uses OAuth for
 * authentication.
 */

namespace Deployer;

use GorkaLaucirica\HipchatAPIv2Client\Auth\OAuth2;
use GorkaLaucirica\HipchatAPIv2Client\Client;
use GorkaLaucirica\HipchatAPIv2Client\Model\Message;
use GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI;

//TODO: Configure what settings there are with examples

/**
 * Functions
 */

/**
 * Notify Hipchat
 *
 * Send off a Hipchat notification using OAuth2
 *
 * @param string $type Type of notification to send
 * @param string $notification The text content of the notification
 *
 * @param string hp_hipchat_room_id The room ID to send notifications to set as
 * Deployer global variable
 * @param string hp_hipchat_room_token The room token for authentication set as
 * Deployer global varialbe
 */
function notifyHipchat($type = 'info', $notification) {
    $roomId = get('hipchat_room_id');
    $roomToken = get('hipchat_room_token');

    //TODO: Ensure these are set, otherwise show warning but don't fail completely

    // Set up the Hipchat connection
    $auth = new OAuth2($roomToken);
    $client = new Client($auth);
    $roomApi = new RoomAPI($client);

    // Map type to color
    switch ($type) {
        case 'warning':
            $color = Message::COLOR_YELLOW;
            break;
        case 'error':
            $color = Message::COLOR_RED;
            break;
        case 'good':
            $color = Message::COLOR_GREEN;
            break;
        case 'info':
        default:
            $color = Message::COLOR_GRAY;
            break;
    }

    $message = new Message();
    $message->setColor($color);
    $message->setMessage($notification);

    $roomApi->sendRoomNotification($roomId, $message);
}

/**
 * Get the branch, tag, or revision that's being deployed and return a
 * string to be used in notifications
 *
 * This is the same approach Deployers "Update Code" step takes to
 * determine what's actually being deployed.
 *
 * @see https://github.com/deployphp/deployer/blob/a7f54ac65e89465d43c6b4b61e6db9f32ec3c23d/recipe/deploy/update_code.php#L11
 *
 * @return string
 */
function getDeployingItem() {
    $branch = get('branch');

    // If option `branch` is set.
    if (input()->hasOption('branch')) {
        $inputBranch = input()->getOption('branch');
        if (!empty($inputBranch)) {
            $branch = $inputBranch;
        }
    }

    // If option `tag` is set.
    if (input()->hasOption('tag')) {
        $tag = input()->getOption('tag');
    }

    // If option `revision` is set, pushing a specific commit
    if (input()->hasOption('revision')) {
        $revision = input()->getOption('revision');
    }

    // If a revision is being deployed, return that
    if (!empty($revision)) {
        return "commit $revision";
    }

    // If a tag is being deployed, return that
    if (!empty($tag)) {
        return "tag $tag";
    }

    //Finally, fall back to the branch being deployed since that's the
    //default if tag or revision aren't provided
    return "$branch branch";
}

/**
 * Deployer Tasks
 */
desc('Notify Hipchat about started deploy');
task('hipchat:notify_start', function () {
    // Build the message to send to Hipchat
    $message = "<strong>" . get('project_name') . ":</strong> Started deploying <strong>" . getDeployingItem() . "</strong> to <strong>" . get('server_name') . "</strong>\n(" . get('release_path') . ")";

    notifyHipchat('info', $message);
});

desc('Notify Hipchat about finished deploy');
task('hipchat:notify_finish', function () {
    // Build the message to send to Hipchat
    $message = "<strong>" . get('project_name') . ":</strong> Deployment of <strong>" . getDeployingItem() . "</strong> to <strong>" . get('server_name') . "</strong> finished\n(" . get('release_path') . ")";

    notifyHipchat('good', $message);
});

desc('Notify Hipchat about failed deploy');
task('hipchat:notify_failure', function () {
    // Build the message to send to Hipchat
    $message = "<strong>" . get('project_name') . ":</strong> Deployment of <strong>" . getDeployingItem() . "</strong> to <strong>" . get('server_name') . "</strong> failed";

    notifyHipchat('error', $message);
});

desc('Notify Hipchat about rollback start');
task('hipchat:notify_rollback_start', function () {
    // Build the message to send to Hipchat
    $releases = get('releases_list');
    $message = "<strong>" . get('project_name') . ":</strong> Rolling back to release {$releases[1]} on <strong>" . get('server_name') . "</strong>";

    notifyHipchat('info', $message);
});

desc('Notify Hipchat about rollback finish');
task('hipchat:notify_rollback_finish', function () {
    // Build the message to send to Hipchat
    $releases = get('releases_list');
    $message = "<strong>" . get('project_name') . ":</strong> Rollback to release {$releases[1]} on <strong>" . get('server_name') . "</strong> finished";

    notifyHipchat('good', $message);
});
