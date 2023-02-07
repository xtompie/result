<?php

declare(strict_types=1);

namespace Xtompie\Result;

class Error
{
    public static function fromPrimitive(array $primitive): static
    {
        return new static(
            message: $primitive['message'],
            key: $primitive['key'],
            space: $primitive['space'],
        );
    }

    public static function of(?string $message = null, ?string $key = null, ?string $space = null): static
    {
        return new static($message, $key, $space);
    }

    public function __construct(
        protected ?string $message = null,
        protected ?string $key = null,
        protected ?string $space = null,
    ) {}

    public function toPrimitive(): array
    {
        return [
            'message' => $this->message,
            'key' => $this->key,
            'space' => $this->space,
        ];
    }

    public function message(): ?string
    {
        return $this->message;
    }

    public function key(): ?string
    {
        return $this->key;
    }

    public function space(): ?string
    {
        return $this->space;
    }

    public function withSpace(?string $space): static
    {
        $new = clone $this;
        $new->space = $space;
        return $new;
    }

    public function withPrefix(string $prefix): static
    {
        $new = clone $this;
        $new->space = $prefix . $this->space;
        return $new;
    }

    public function hasPrefix(string $prefix): bool
    {
        if ($this->space() === null) {
            return false;
        }

        return $prefix === substr($this->space(), 0, strlen($prefix));
    }

    public function hasSpace(?string $space): bool
    {
        return $this->space === $space;
    }

    public function hashCode(): string
    {
        return sha1(serialize([
            $this->key,
            $this->space,
        ]));
    }

    public function equals(Error $error): bool
    {
        return $this->key === $error->key && $this->space === $error->space;
    }
}
