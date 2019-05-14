<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitd330e48d1f4ceb05dc3ae6e9b2556fe1
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'M' => 
        array (
            'MyException' => 
            array (
                0 => __DIR__ . '/../..' . '/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitd330e48d1f4ceb05dc3ae6e9b2556fe1::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitd330e48d1f4ceb05dc3ae6e9b2556fe1::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitd330e48d1f4ceb05dc3ae6e9b2556fe1::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
