<?php

declare(strict_types=1);

namespace Xtompie\Result;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

class ErrorCollection implements IteratorAggregate, Countable, JsonSerializable
{
    public static function ofEmpty(): static
    {
        return new static([]);
    }

    public static function ofErrors(array $errors): static
    {
        return new static($errors);
    }

    public static function ofError(Error $error): static
    {
        return new static([$error]);
    }

    public static function ofErrorMsg(?string $message = null, ?string $key = null, ?string $space = null): static
    {
        return new static([
            Error::of($message, $key, $space)
        ]);
    }

    public static function fromPrimitive(array $primitive): static
    {
        return new static(
            array_map(
                fn (array $error) => Error::fromPrimitive($error),
                $primitive
            )
        );
    }

    public function __construct(
        protected array $collection,
    ) {}

    /**
     * @return Error[]
     */
    public function toArray(): array
    {
        return $this->collection;
    }

    public function toPrimitive(): array
    {
        return array_map(
            fn (Error $error) => $error->toPrimitive(),
            $this->collection
        );
    }

    public function any(): bool
    {
        return (bool)$this->collection;
    }

    public function none(): bool
    {
        return !$this->any();
    }

    public function first(): ?Error
    {
        foreach ($this->collection as $i) {
            return $i;
        }
        return null;
    }

    public function merge(ErrorCollection $errors): static
    {
        return new static(array_merge($this->collection, $errors->toArray()));
    }

    public function map(callable $callback): static
    {
        return new static(array_map($callback, $this->collection));
    }

    public function mapToArray(callable $callback): array
    {
        return array_map($callback, $this->collection);
    }

    public function filter(callable $callback): static
    {
        return new static(array_filter($this->collection, $callback));
    }

    public function each(callable $callback): static
    {
        array_walk($this->collection, $callback);
        return $this;
    }

    public function withPrefix(string $prefix, string $glue = '')
    {
        return $this->map(fn (Error $error) => $error->withPrefix($prefix, $glue));
    }

    public function withSpace(string $space)
    {
        return $this->map(fn (Error $error) => $error->withSpace($space));
    }

    public function filterByPrefix(string $prefix): static
    {
        return $this->filter(fn(Error $error) => $error->hasPrefix($prefix));
    }

    public function filterBySpace(?string $space): static
    {
        return $this->filter(fn(Error $error) => $error->hasSpace($space));
    }

    public function unique(): static
    {
        $unique = [];
        $this->each(fn(Error $error) => $unique[$error->hashCode()] = $error);
        return new static(array_values($unique));
    }

    public function add(Error $error): static
    {
        return $this->merge(ErrorCollection::ofError($error));
    }

    public function addMsg(?string $message, ?string $key, ?string $space): static
    {
        return $this->merge(ErrorCollection::ofErrorMsg($message, $key, $space));
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->collection);
    }

    public function count(): int
    {
        return count($this->collection);
    }

    public function jsonSerialize(): array
    {
        return $this->collection;
    }
}
