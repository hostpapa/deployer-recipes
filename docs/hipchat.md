# Hipchat V2 Recipe

This Hipchat recipe uses the V2 of their API which requires OAuth2.

## Installation

In your `deploy.php`, require this recipe.

```php
require 'recipe/hipchatv2.php';
```

## Configuration

This recipe requires some global configuration using Deployer's `set()`
command.

### Project Name

Hipchat notifications use a "project name" to better differentiate multiple
projects in the notification room. Set this once to describe the overall
project.

```php
set('project_name', 'Best Project Ever');
```

### Hipchat Room Credentials

This recipe also requires a Hipchat room token and ID in order to communicate
with your Hipchat installation.

```php
set('hp_hipchat_room_token', 'superspecialtoken');
set('hp_hipchat_room_id', '123456789');
```

## Tasks

This recipe exposes the following tasks

- `hipchat:notify_start`
- `hipchat:notify_finish`
- `hipchat:notify_failure`
- `hipchat:notify_rollback_start`
- `hipchat:notify_rollback_finish`

## Suggested Usage

```php
after('deploy:release', 'hipchat:notify_start');
after('success', 'hipchat:notify_finish');
after('deploy:failed', 'hipchat:notify_failure');
before('rollback', 'hipchat:notify_rollback_start');
after('rollback', 'hipchat:notify_rollback_finish');
```

