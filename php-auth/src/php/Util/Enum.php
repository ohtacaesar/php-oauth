<?php

namespace Util;

abstract class Enum
{

    /** @var [] */
    protected static $values;

    protected static $flip;

    private static function init(): void
    {
        if (static::$values !== null) {
            return;
        }
        $class = new \ReflectionClass(static::class);
        static::$values = $class->getConstants();
        static::$flip = array_flip($class->getConstants());
    }

    public static function values(): array
    {
        static::init();
        return static::$values;
    }

    public static function valueOf(string $name): ?int
    {
        static::init();
        return static::$values[strtoupper($name)] ?? null;
    }

    public static function name(int $ordinal): ?string
    {
        static::init();
        return static::$flip[$ordinal] ?? null;
    }
}
