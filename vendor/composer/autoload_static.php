<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit12f6ea68d42665b1ea2314d2d2fa3bdd
{
    public static $files = array (
        'e53bf83d7694229d99b07ea5d1145936' => __DIR__ . '/../..' . '/functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Premmerce\\SDK\\' => 14,
            'Premmerce\\PriceTypes\\' => 21,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Premmerce\\SDK\\' => 
        array (
            0 => __DIR__ . '/..' . '/premmerce/wordpress-sdk/src',
        ),
        'Premmerce\\PriceTypes\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit12f6ea68d42665b1ea2314d2d2fa3bdd::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit12f6ea68d42665b1ea2314d2d2fa3bdd::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit12f6ea68d42665b1ea2314d2d2fa3bdd::$classMap;

        }, null, ClassLoader::class);
    }
}
