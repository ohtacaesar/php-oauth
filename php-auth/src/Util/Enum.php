<?php

namespace Util;

abstract class Enum
{

    /** @var [] */
    protected static $values;

    private static function init(): void
    {
        if (static::$values !== null) {
            return;
        }
        $class = new \ReflectionClass(static::class);
        static::$values = $class->getConstants();
    }

    public static function values(): array
    {
        static::init();
        return static::$values;
    }

    public static function valueOf(string $name): int
    {
        static::init();
        $name = strtoupper($name);
        if (!isset(static::$values[$name])) {
            throw new \LogicException(sprintf("%s::valueOf('%s')", static::class, $name));
        }

        return static::$values[$name];
    }
}
