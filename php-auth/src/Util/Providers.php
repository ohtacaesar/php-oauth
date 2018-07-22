<?php

namespace Util;

class Providers
{
    private static $values = null;

    const GITHUB = 1;
    const GOOGLE = 2;

    private static function init(): void
    {
        if (self::$values !== null) {
            return;
        }
        $class = new \ReflectionClass(self::class);
        self::$values = $class->getConstants();
    }

    public static function values(): array
    {
        self::init();
        return self::$values;
    }

    public static function valueOf(string $name): int
    {
        self::init();
        $name = strtoupper($name);
        if (!isset(self::$values[$name])) {
            throw new \LogicException(sprintf("Providers::valueOf('%s')", $name));
        }

        return self::$values[$name];
    }
}
