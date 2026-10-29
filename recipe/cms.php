<?php

/**
 * CMS Related tasks commonly used *outside* of the CMS itself
 */

namespace Deployer;

use Deployer\Utility\Httpie;

/**
 * Process the CMS cache path to ensure we're dealing with the *folder* not the
 * contents
 */
set('processed_cms_cache_path', function () {
    $cacheFolder = get('cms_cache_path', false);

    // Trim the trailing slash if there is one
    $cacheFolder = rtrim($cacheFolder, '/');

    // REALLY be sure we're not about to erase the server
    if ($cacheFolder == "/") {
        throw new \Exception('Incorrect cache folder path');
    }

    if (!$cacheFolder) {
        throw new \Exception('Unable to determine the cache folder');
    }

    return $cacheFolder;
});

/**
 * The address the Dev Build request is sent to on the server. The default keeps the request on the machine: the
 * public hostname resolves to Cloudflare, whose Access policies a server can't pass. Empty uses the public DNS.
 */
set('cms_devbuild_resolve_ip', '127.0.0.1');

desc("Clear and rebuild CMS's Silverstripe cache");
task("cms:clear_cache", function () {
    $httpUser = get('cms_http_user', false);
    $httpPass = get('cms_http_pass', false);

    if (!$httpUser || !$httpPass) {
        throw new \Exception('Missing CMS HTTP user and pass for cache rebuilding. Define cms_http_user and cms_http_pass Deployer configs.');
    }

    if (get('slack_webhook', false)) {
        Httpie::post(get('slack_webhook'))
            ->body([
                'attachments' => [[
                    'title' => get('slack_title'),
                    'text' => 'Flushing CMS SS Cache and running Dev Build - :four_leaf_clover: :socks:',
                    'color' => 'warning',
                    'fields' => [
                        [
                            'title' => 'Environment',
                            'value' => get('server_name'),
                            'short' => true,
                        ],
                        [
                            'title' => 'Branch',
                            'value' => get('branch'),
                            'short' => true,
                        ],
                    ]
                ]]
            ])
            ->send();
    }

    // Remove the "old" SS Cache folder _if it exists_
    if (test('[ -d {{processed_cms_cache_path}}-old ]')) {
        writeln("  ➔ Removing OLD CMS SS Cache folder located at {{processed_cms_cache_path}}-old");
        run("rm -rf {{processed_cms_cache_path}}-old");
    } else {
        writeln("  ➔ No OLD CMS SS Cache folder found. Skipping removal.");
    }

    // Pre-create the new folder so we can set permissions before moving anything
    writeln("  ➔ Pre-creating NEW CMS SS Cache folder located at {{processed_cms_cache_path}}-new to pre-set permissions");
    run("mkdir -p {{processed_cms_cache_path}}-new");

    // Invoke deploy:writable again to ensrue the new folder has the correct permissions
    writeln("  ➔ Ensuring permissions are correct pre-move");
    invoke('deploy:writable');

    // Move the current cache folder to "old" to allow any active write connections to finish
    writeln("  ➔ Moving the live SS Cache folder to become old");
    run("mv {{processed_cms_cache_path}} {{processed_cms_cache_path}}-old");

    // Create the cache folder again
    writeln("  ➔ Moving the new, empty, cache folder into place");
    run("mv {{processed_cms_cache_path}}-new {{processed_cms_cache_path}}");

    // Trigger a fresh Dev Build
    $devBuildUrl = get('cms_devbuild_url');
    $resolveIp = get('cms_devbuild_resolve_ip', '');
    $resolve = '';
    if ($resolveIp) {
        $host = parse_url($devBuildUrl, PHP_URL_HOST);
        $port = parse_url($devBuildUrl, PHP_URL_PORT) ?: (parse_url($devBuildUrl, PHP_URL_SCHEME) === 'http' ? 80 : 443);
        $resolve = '--resolve ' . escapeshellarg("$host:$port:$resolveIp") . ' ';
    }
    writeln("  ➔ Triggering CMS Dev Build via browser (curl) using URL $devBuildUrl" . ($resolveIp ? " on $resolveIp" : ''));
    // -f fails on an HTTP error, so a refused request stops the deploy instead of passing as a login page
    run("curl -fsSkL -o /dev/null {$resolve}-u " . escapeshellarg("$httpUser:$httpPass") . ' ' . escapeshellarg($devBuildUrl));

    if (get('slack_webhook', false)) {
        Httpie::post(get('slack_webhook'))
            ->body([
                'attachments' => [[
                    'title' => get('slack_title'),
                    'text' => 'Finished flushing CMS SS Cache and running Dev Build',
                    'color' => 'good',
                    'fields' => [
                        [
                            'title' => 'Environment',
                            'value' => get('server_name'),
                            'short' => true,
                        ],
                        [
                            'title' => 'Branch',
                            'value' => get('branch'),
                            'short' => true,
                        ],
                    ]
                ]]
            ])
            ->send();
    }
});
