<?php

use App\ValueObjects\Money;

it('formats cents as Brazilian Real', function (int $cents, string $expected) {
    expect(Money::fromCents($cents)->format())->toBe($expected)
        ->and((string) Money::fromCents($cents))->toBe($expected);
})->with([
    'zero' => [0, 'R$ 0,00'],
    'cents only' => [5, 'R$ 0,05'],
    'with thousands' => [129_990, 'R$ 1.299,90'],
    'millions' => [123_456_789, 'R$ 1.234.567,89'],
]);

it('adds and multiplies without losing precision', function () {
    $price = Money::fromCents(1_990);

    expect($price->multiply(3)->cents)->toBe(5_970)
        ->and($price->add(Money::fromCents(10))->cents)->toBe(2_000)
        ->and($price->multiply(0)->isZero())->toBeTrue();
});

it('is immutable', function () {
    $price = Money::fromCents(1_000);
    $price->add(Money::fromCents(500));
    $price->multiply(2);

    expect($price->cents)->toBe(1_000);
});

it('compares by value', function () {
    expect(Money::fromCents(100)->equals(Money::fromCents(100)))->toBeTrue()
        ->and(Money::fromCents(100)->equals(Money::fromCents(101)))->toBeFalse()
        ->and(Money::zero()->equals(Money::fromCents(0)))->toBeTrue();
});

it('rejects negative amounts', function () {
    Money::fromCents(-1);
})->throws(InvalidArgumentException::class);

it('rejects negative factors', function () {
    Money::fromCents(100)->multiply(-2);
})->throws(InvalidArgumentException::class);
