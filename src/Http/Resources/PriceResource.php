<?php

declare(strict_types = 1);

namespace Wame\LaravelNovaPriceField\Http\Resources;

use Wame\LaravelNovaPriceField\Casts\PriceCast;

class PriceResource
{
    public static function make(PriceCast $price): array
    {
        return [
            'with_tax' => (string) ($price->totalWithTax()->getAmount() / 100),
            'without_tax' => (string) ($price->totalWithoutTax()->getAmount() / 100),
            'tax' => (int) $price->tax(),
        ];
    }
}