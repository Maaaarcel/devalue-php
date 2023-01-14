<?php
declare(strict_types = 1);

namespace Maaaarcel\DevaluePhp\JavaScript;

/**
 * JavaScript BigInt() Object
 */
final class JsBigInt implements JsTypeInterface {

    public function __construct(private int|float $value = 0) {
    }

    /**
     * @return int|float
     */
    public function getValue(): int|float {
        return $this->value;
    }

    /**
     * @param int|float $value
     */
    public function setValue(int|float $value): void {
        $this->value = $value;
    }
}