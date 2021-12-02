<?php

declare(strict_types=1);

namespace Xtompie\Result;

class Result
{
    public static function ofSuccess(): static
    {
        return new static(true, null, ErrorCollection::ofEmpty());
    }

    public static function ofValue(mixed $value): static
    {
        return new static(true, $value, ErrorCollection::ofEmpty());
    }

    public static function ofFail(): static
    {
        return new static(false, null, ErrorCollection::ofEmpty());
    }

    public static function ofError(Error $error): static
    {
        return new static(false, null, ErrorCollection::ofError($error));
    }

    public static function ofErrors(ErrorCollection $errors): static
    {
        return new static(false, null, $errors);
    }

    public static function ofErrorMsg(?string $message, ?string $key = null, ?string $space = null): static
    {
        return new static(false, null, ErrorCollection::ofErrorMsg($message, $key, $space));
    }

    public static function ofCombine(Result ...$results): static
    {
        $errors = ErrorCollection::ofEmpty();
        $value = null;
        $success = true;

        foreach ($results as $result) {
            $errors = $errors->merge($result->errors());
            if ($value === null && $result->value() !== null) {
                $value = $result->value();
            }
            if (!$result->success()) {
                $success = false;
            }
        }

        return $success
            ? new static(true, $value, ErrorCollection::ofEmpty())
            : new static(false, null, $errors)
        ;
    }

    protected function __construct(
        protected bool $success,
        protected mixed $value,
        protected ErrorCollection $errors,
    ) {}

    public function success(): bool
    {
        return $this->success;
    }

    public function fail(): bool
    {
        return !$this->success();
    }

    public function value(): mixed
    {
        return $this->value;
    }

    public function errors(): ErrorCollection
    {
        return $this->errors;
    }

    public function ifSuccess(callable $callback): static
    {
        if ($this->success()) {
            $callback();
        }
        return $this;
    }

    public function ifFail(callable $callback): static
    {
        if ($this->fail()) {
            $callback($this);
        }
        return $this;
    }

    public function tap(callable $callback): static
    {
        $callback($this);
        return $this;
    }

    public function map(callable $callback): static
    {
        return $callback($this);
    }
}
