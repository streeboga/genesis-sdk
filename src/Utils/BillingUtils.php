<?php

namespace Streeboga\Genesis\Utils;

class BillingUtils
{
    /**
     * Calculate total overage price and round to 2 decimals
     */
    public static function calculateOverageTotal(float $unitPrice, int $amount): float
    {
        return round($unitPrice * $amount, 2);
    }

    /**
     * Format currency according to currency code (simple formatter)
     */
    public static function formatCurrency(float $amount, string $currency = 'USD'): string
    {
        switch (strtoupper($currency)) {
            case 'RUB':
                return number_format($amount, 2, ',', ' ') . ' ₽';
            case 'EUR':
                return number_format($amount, 2, ',', ' ') . ' €';
            case 'USD':
            default:
                return '$' . number_format($amount, 2, '.', ',');
        }
    }

    /**
     * Return short plan summary from metadata array
     */
    public static function summarizePlan(array $metadata): string
    {
        $name = $metadata['name'] ?? ($metadata['plan_uuid'] ?? 'plan');
        $price = $metadata['price'] ?? null;
        $currency = $metadata['currency'] ?? 'USD';

        if ($price === null) {
            return $name;
        }

        return sprintf('%s — %s', $name, self::formatCurrency((float) $price, $currency));
    }
}
