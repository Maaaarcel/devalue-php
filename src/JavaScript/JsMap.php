<?php

declare(strict_types=1);

namespace Maaaarcel\DevaluePhp\JavaScript;

use ArrayAccess;
use Countable;
use Iterator;
use Serializable;

/**
 * Map implementation with similar API to a JavaScript Map.
 *
 * @template TKey
 * @template TValue
 *
 * @implements ArrayAccess<TKey, TValue>
 * @implements Iterator<TKey, TValue>
 */
final class JsMap implements Countable, Iterator, Serializable, ArrayAccess, JsTypeInterface
{

    /**
     * @var array<string, TKey>
     */
    private array $objectMap = [];
    /**
     * @var array<string|int, TValue>
     */
    private array $data = [];

    /**
     * @param array<array{TKey, TValue}> $entries
     */
    public function __construct(array $entries = [])
    {
        $this->insertEntries($entries);
    }

    /**
     * @param TKey $key
     * @param TValue $value
     */
    public function set(mixed $key, mixed $value): void
    {
        $this->offsetSet($key, $value);
    }

    /**
     * @param TKey $key
     *
     * @return TValue
     */
    public function get(mixed $key): mixed
    {
        return $this->offsetGet($key);
    }

    /**
     * @param TKey $key
     *
     * @return bool
     */
    public function has(mixed $key): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * @param TKey $key
     *
     * @return void
     */
    public function delete(mixed $key): void
    {
        $this->offsetUnset($key);
    }

    public function clear(): void
    {
        $this->data = [];
        $this->objectMap = [];
    }

    public function size(): int
    {
        return $this->count();
    }

    /**
     * @return TKey[]
     */
    public function keys(): array
    {
        $keys = array_keys($this->data);
        return array_map(fn(mixed $key) => array_key_exists($key, $this->objectMap) ? $this->objectMap[$key] : $key,
            $keys);
    }

    /**
     * @return TValue[]
     */
    public function values(): array
    {
        return array_values($this->data);
    }

    /**
     * @return array<array{TKey, TValue}>
     */
    public function entries(): array
    {
        $keys = array_keys($this->data);
        return array_map(function (mixed $key) {
            if (array_key_exists($key, $this->objectMap)) {
                return [$this->objectMap[$key], $this->data[$key]];
            }
            return [$key, $this->data[$key]];
        }, $keys);
    }

    /**
     * @return TValue
     */
    public function current(): mixed
    {
        return current($this->data);
    }

    public function next(): void
    {
        next($this->data);
    }

    /**
     * @return TKey|null
     */
    public function key(): mixed
    {
        $key = key($this->data);
        if (is_null($key)) {
            return null;
        }
        if (array_key_exists($key, $this->objectMap)) {
            return $this->objectMap[$key];
        }
        return $key;
    }

    public function valid(): bool
    {
        return current($this->data) !== false;
    }

    public function rewind(): void
    {
        reset($this->data);
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function serialize(): ?string
    {
        return serialize($this->entries());
    }

    public function unserialize(string $data): void
    {
        $this->insertEntries(unserialize($data));
    }

    /**
     * @return array<array{TKey, TValue}>
     */
    public function __serialize(): array
    {
        return $this->entries();
    }

    /**
     * @param array<array{TKey, TValue}> $data
     */
    public function __unserialize(array $data): void
    {
        $this->insertEntries($data);
    }

    /**
     * @param TKey $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        if (is_object($offset)) {
            return array_key_exists($this->hash($offset), $this->data);
        }
        return array_key_exists($offset, $this->data);
    }

    /**
     * @param TKey $offset
     *
     * @return TValue
     */
    public function offsetGet(mixed $offset): mixed
    {
        if (is_object($offset)) {
            return $this->data[$this->hash($offset)] ?? null;
        }
        return $this->data[$offset] ?? null;
    }

    /**
     * @param TKey $offset
     * @param TValue $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_array($offset)) {
            $offset = md5(serialize($offset));
        }
        if (is_object($offset)) {
            $hash = $this->hash($offset);
            $this->objectMap[$hash] = $offset;
            $this->data[$hash] = $value;
            return;
        }
        $this->data[$offset] = $value;
    }

    /**
     * @param TKey $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        if (is_object($offset)) {
            $hash = $this->hash($offset);
            unset($this->objectMap[$hash]);
            unset($this->data[$hash]);
            return;
        }
        unset($this->data[$offset]);
    }

    /**
     * @param array<array{TKey, TValue}> $entries
     */
    private function insertEntries(array $entries): void
    {
        foreach ($entries as [$key, $value]) {
            $this->set($key, $value);
        }
    }

    private function hash(object $object): string
    {
        return '#object__' . spl_object_hash($object) . '#';
    }
}