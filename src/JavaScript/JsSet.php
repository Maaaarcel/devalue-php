<?php

declare(strict_types=1);

namespace Maaaarcel\DevaluePhp\JavaScript;

use Countable;
use Iterator;
use Serializable;

/**
 * Set implementation with similar API to a JavaScript Set.
 *
 * @template T
 */
final class JsSet implements Countable, Iterator, Serializable, JsTypeInterface
{

    private bool $dirty = false;

    /**
     * @param T[] $data
     */
    public function __construct(private array $data = [])
    {
        $this->removeDuplicates();
    }

    /**
     * @return void
     * @internal
     */
    public function removeDuplicates(): void
    {
        if ($this->dirty) {
            $this->data = array_unique($this->data);
            $this->dirty = false;
        }
    }

    /**
     * @param T $value
     */
    public function add(mixed $value): void
    {
        $this->dirty = true;
        $this->data[] = $value;
    }

    /**
     * @param T $value
     */
    public function has(mixed $value): bool
    {
        $this->removeDuplicates();
        return in_array($value, $this->data);
    }

    /**
     * @return int[]
     */
    public function keys(): array
    {
        $this->removeDuplicates();
        return array_keys($this->data);
    }

    /**
     * @return T[]
     */
    public function values(): array
    {
        $this->removeDuplicates();
        return array_values($this->data);
    }

    /**
     * @return array<array{int, T}>
     */
    public function entries(): array
    {
        $this->removeDuplicates();
        return array_map(static fn(int $idx, mixed $value) => [$idx, $value], $this->keys(), $this->data);
    }

    /**
     * @param T $value
     */
    public function delete(mixed $value): void
    {
        $this->removeDuplicates();
        if (($idx = array_search($value, $this->data)) !== false) {
            unset($this->data[$idx]);
        }
    }

    public function clear()
    {
        $this->data = [];
    }

    public function size(): int
    {
        return $this->count();
    }

    /**
     * @return T
     */
    public function current(): mixed
    {
        $this->removeDuplicates();
        return current($this->data);
    }

    public function next(): void
    {
        $this->removeDuplicates();
        next($this->data);
    }

    public function key(): string|int|null
    {
        $this->removeDuplicates();
        return key($this->data);
    }

    public function valid(): bool
    {
        $this->removeDuplicates();
        return current($this->data) !== false;
    }

    public function rewind(): void
    {
        $this->removeDuplicates();
        reset($this->data);
    }

    public function count(): int
    {
        $this->removeDuplicates();
        return count($this->data);
    }

    public function serialize(): ?string
    {
        $this->removeDuplicates();
        return serialize($this->data);
    }

    public function unserialize(string $data): void
    {
        $this->data = unserialize($data);
    }

    /**
     * @return T[]
     */
    public function __serialize(): array
    {
        $this->removeDuplicates();
        return $this->data;
    }

    /**
     * @param T[] $data
     */
    public function __unserialize(array $data): void
    {
        $this->data = $data;
    }

    public function __toString(): string
    {
        return spl_object_hash($this);
    }
}