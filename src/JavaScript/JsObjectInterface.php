<?php

declare(strict_types=1);

namespace Maaaarcel\DevaluePhp\JavaScript;

/**
 * Interface for JavaScript Object types
 */
interface JsObjectInterface extends JsTypeInterface
{
    public const NULL_PROTOTYPE_KEY = '__jsNullPrototype';

    public function getValue(): mixed;
}