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
            'with_tax' => number_format((($price->withTax()->getAmount() / 100) * $quantity), 2, '.', ''),
            'without_tax' => number_format((($price->withoutTax()->getAmount() / 100) * $quantity), 2, '.', ''),
            'tax' => (int) $price->tax(),
        ];
    }
}