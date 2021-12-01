<?php

declare(strict_types=1);

namespace Xtompie\Result;

class ErrorCollection
{
    public static function ofEmpty(): static
    {
        return new static([]);
    }

    public static function ofError(Error $error): static
    {
        return new static([$error]);
    }

    public static function ofErrorMsg(?string $message = null, ?string $code = null, ?string $key = null): static
    {
        return new static([
            Error::of($message, $code, $key)
        ]);
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

    public function filter(callable $callback): static
    {
        return new static(array_filter($this->collection, $callback));
    }

    public function each(callable $callback): static
    {
        array_walk($this->collection, $callback);
        return $this;
    }

    public function withPrefix(string $prefix)
    {
        return $this->map(fn (Error $error) => $error->withPrefix($prefix));
    }

    public function filterByPrefix(string $prefix): static
    {
        return $this->filter(fn(Error $error) => $error->hasPrefix($prefix));
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
}
