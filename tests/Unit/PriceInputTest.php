<?php

use App\Support\PriceInput;

it('converts typed prices to cents', function (string $input, int $cents) {
    expect(PriceInput::toCents($input))->toBe($cents);
})->with([
    'integer' => ['1299', 129_900],
    'comma decimals' => ['1299,9', 129_990],
    'brazilian notation' => ['1.299,90', 129_990],
    'thousands only' => ['1.299', 129_900],
    'millions' => ['12.345,67', 1_234_567],
    'dot decimals' => ['1299.90', 129_990],
    'one dot decimal' => ['10.5', 1_050],
    'currency prefix' => ['R$ 49,90', 4_990],
    'leading zeros' => ['0,99', 99],
    'upper bound' => ['999.999,99', PriceInput::MAX_CENTS],
]);

it('rejects malformed or out of range prices', function (string $input) {
    expect(PriceInput::toCents($input))->toBeNull();
})->with([
    'empty' => [''],
    'letters' => ['abc'],
    'negative' => ['-10,00'],
    'three decimals' => ['12,345'],
    'mixed separators' => ['1,299.90'],
    'misplaced thousands' => ['12.34.567'],
    'too large' => ['1.000.000,00'],
    'overflow' => ['99999999999999999999'],
]);

it('formats cents back to the brazilian notation it accepts', function () {
    expect(PriceInput::fromCents(129_990))->toBe('1.299,90')
        ->and(PriceInput::fromCents(5))->toBe('0,05')
        ->and(PriceInput::toCents(PriceInput::fromCents(1_234_567)))->toBe(1_234_567);
});
