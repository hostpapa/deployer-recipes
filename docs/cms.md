# CMS Recipe

This HP CMS recipe is responsible for actions on the CMS, often from outside the
CMS repo itself. For example forceably clearing the CMS cache.

**Note:** Now that CMS cache clearing has been removed from other repos, we can
eventually put this _within_ the CMS deploy file. It still lives here just in
case we need to go back to other repos clearing caches as well.

## Installation

Install this package using the instructionsin the main README and then include
this recipe in your Deployer script with

```php
require 'vendor/hostpapa/deployer-recipes/recipe/cms.php';
```

### Additions for `writable_dirs`

- `silverstripe-cache`
- `silverstripe-cache-new`
    - This ensures that when `cms:clear_cache` invokes `deploy:writable` this
      temporary folder will get picked up and permissions are set properly.

## Cache Clearing

```text
cms:clear_cache
```

### Configuration

This task expects 4 variables to be defined

1. `cms_http_user`
2. `cms_http_pass`
3. `cms_cache_path`
    - This should be the file path to the `silverstripe-cache` folder on the
      server

```php
set('cms_cache_path', function () {
        return get('current_path') . '/silverstripe-cache';
});
```

4. `cms_devbuild_url`
    - The fully qualified URL to `/intranet/devbuild` for that server
    - If a server uses fancy branching methodologies, add support for that in
      the Deployer script by defining more a more advanced setter similar to the
      loading of HTTP user/pass below.

#### Defining HTTP user/pass with Dotenv

Instead of statically defining the HTTP user and password, which shouldn't be
included in the Deployer script anyways, one can use Dotenv to load and return
the value.

```php
set('cms_http_user', function () {
    if (!getenv('HTTP_USER')) {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
        $dotenv->required('HTTP_USER')->notEmpty();
    }

    return getenv('HTTP_USER');
});

set('cms_http_pass', function () {
    if (!getenv('HTTP_PASS')) {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
        $dotenv->required('HTTP_PASS')->notEmpty();
    }

    return getenv('HTTP_PASS');
});
```
