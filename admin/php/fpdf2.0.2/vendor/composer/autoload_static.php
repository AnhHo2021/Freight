<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitfb5ab92e8e7198357d536c9315c3c80a
{
    public static $prefixLengthsPsr4 = array (
        's' => 
        array (
            'setasign\\Fpdi\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'setasign\\Fpdi\\' => 
        array (
            0 => __DIR__ . '/..' . '/setasign/fpdi/src',
        ),
    );

    public static $classMap = array (
        'FPDF' => __DIR__ . '/..' . '/setasign/fpdf/fpdf.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitfb5ab92e8e7198357d536c9315c3c80a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitfb5ab92e8e7198357d536c9315c3c80a::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitfb5ab92e8e7198357d536c9315c3c80a::$classMap;

        }, null, ClassLoader::class);
    }
}
