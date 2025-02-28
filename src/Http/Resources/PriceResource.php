<?php

declare(strict_types = 1);

namespace Wame\LaravelNovaPriceField\Http\Resources;

use Wame\LaravelNovaPriceField\Casts\PriceCast;

class PriceResource
{
    public static function make(PriceCast $price): array
    {
        $quantity = $price->quantity ?: 1;

        return [
            'with_tax' => (string) ($price->withTax()->getAmount() / 100) * $quantity,
            'without_tax' => (string) ($price->withoutTax()->getAmount() / 100) * $quantity,
            'tax' => (int) $price->tax(),
        ];
    }
}