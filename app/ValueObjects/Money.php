<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * Immutable amount of Brazilian Real stored in cents, so arithmetic never
 * goes through floats.
 */
final readonly class Money implements Stringable
{
    private function __construct(public int $cents)
    {
        if ($cents < 0) {
            throw new InvalidArgumentException('Money cannot be negative.');
        }
    }

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function add(self $other): self
    {
        return new self($this->cents + $other->cents);
    }

    public function multiply(int $factor): self
    {
        if ($factor < 0) {
            throw new InvalidArgumentException('Money cannot be multiplied by a negative factor.');
        }

        return new self($this->cents * $factor);
    }

    public function equals(self $other): bool
    {
        return $this->cents === $other->cents;
    }

    public function isZero(): bool
    {
        return $this->cents === 0;
    }

    /**
     * Formatted in Brazilian Real, e.g. "R$ 1.299,90".
     */
    public function format(): string
    {
        return 'R$ '.number_format($this->cents / 100, 2, ',', '.');
    }

    public function __toString(): string
    {
        return $this->format();
    }
}
