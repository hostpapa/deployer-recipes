<?php

/**
 * CMS Related tasks commonly used *outside* of the CMS itself
 */

namespace Deployer;

use Deployer\Utility\Httpie;

/**
 * Process the CMS cache path to ensure there we're removing the contents, not
 * the folder itself
 */
set('processed_cms_cache_path', function () {
    $cacheFolder = get('cms_cache_path', false);
    if ($cacheFolder && substr($cacheFolder, -1) == '/') {
        $cacheFolder .= '*';
    } elseif ($cacheFolder && substr($cacheFolder, -2) !== '/*') {
        $cacheFolder .= '/*';
    }

    // REALLY be sure we're not about to erase the server
    if ($cacheFolder == "/*") {
        throw new \Exception('Incorrect cache folder path');
    }

    if (!$cacheFolder) {
        throw new \Exception('Unable to determine the cache folder');
    }

    return $cacheFolder;
});

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

    // Remove the contents of the cache folder
    writeln("  ➔ Removing CMS SS Cache files located at {{processed_cms_cache_path}}");
    run("rm -rf {{processed_cms_cache_path}}");

    writeln("  ➔ Triggering CMS Dev Build via browser (wget) using URL {{cms_devbuild_url}}");
    run("wget --no-check-certificate --spider --http-user={$httpUser} --http-password={$httpPass} {{cms_devbuild_url}}");

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
