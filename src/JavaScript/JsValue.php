<?php

declare(strict_types=1);

namespace Maaaarcel\DevaluePhp\JavaScript;

/**
 * Enum with special values
 */
enum JsValue: int
{
    case Undefined = -1;
    case Hole = -2;
    case Nan = -3;
    case PositiveInfinity = -4;
    case NegativeInfinity = -5;
    case NegativeZero = -6;

    public function toPhpValue(): int|float|null
    {
        return match ($this) {
            self::Undefined, self::Hole => null,
            self::Nan => NAN,
            self::PositiveInfinity => INF,
            self::NegativeInfinity => -INF,
            self::NegativeZero => -0,
        };
    }
}