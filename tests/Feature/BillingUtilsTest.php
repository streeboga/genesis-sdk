<?php

use Streeboga\Genesis\Utils\BillingUtils;

it('calculates overage total', function () {
    $total = BillingUtils::calculateOverageTotal(0.05, 10);
    expect($total)->toBe(0.5);
});

it('formats currency USD', function () {
    $s = BillingUtils::formatCurrency(1234.5, 'USD');
    expect($s)->toBe('$1,234.50');
});

it('formats currency RUB', function () {
    $s = BillingUtils::formatCurrency(1234.5, 'RUB');
    expect($s)->toBe('1 234,50 ₽');
});

it('summarizes plan metadata', function () {
    $meta = ['plan_uuid' => 'plan-1', 'name' => 'Pro', 'price' => 1000, 'currency' => 'RUB'];
    $s = BillingUtils::summarizePlan($meta);
    expect($s)->toBe('Pro — 1 000,00 ₽');
});
