<?php
/**
 * Milkyway Multimedia
 *
 * Load an environment file via .env.php
 *
 * @package milkyway-multimedia/ss-mwm-env
 * @author Mellisa Hankins <mellisa.hankins@me.com>
 */

if (file_exists(BASE_PATH . '/vendor/vlucas/phpdotenv/src/Dotenv.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';

    $dirsToCheck = [
        realpath('.'),
        BASE_PATH,
        dirname(BASE_PATH),
        dirname(dirname(BASE_PATH)),
    ];

    if ($dirsToCheck[0] == $dirsToCheck[1]) {
        unset($dirsToCheck[1]);
    }

    foreach ($dirsToCheck as $dir) {
        do {
            $dir .= DIRECTORY_SEPARATOR;

            if (@is_readable($dir) && file_exists($dir . '.env.php')) {
                (new \Dotenv\Dotenv($dir, '.env.php'))->overload();
                break(2);
            }
            else if (@is_readable($dir) && file_exists($dir . '.env')) {
                (new \Dotenv\Dotenv($dir, '.env'))->overload();
                break(2);
            }
            else {
                break;
            }

        } while (dirname($dir) != $dir);
    }
}
