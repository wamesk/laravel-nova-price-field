<?php

declare(strict_types = 1);

namespace Wame\LaravelNovaPriceField\Models;

trait HasPriceWithoutTax
{
    public static string|bool $priceWithoutTaxColumn = true;

    public function getPriceWithoutTaxAttribute(): int
    {
        return (int) round($this->original['price'] / (($this->tax / 100) + 1));
    }
}
