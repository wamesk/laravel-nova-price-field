<?php

declare(strict_types = 1);

namespace Wame\LaravelNovaPriceField\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class SimplePriceCast extends AbstractPriceCast
{
    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class () implements CastsAttributes {
            public function get(Model $model, string $key, mixed $value, array $attributes): ?SimplePriceCast
            {
                if (null === $value || 'null' === $value) {
                    return null;
                }

                return new SimplePriceCast(
                    (int) $value,
                    AbstractPriceCast::resolveCurrency($model, $attributes)
                );
            }

            public function set(Model $model, string $key, mixed $value, array $attributes): ?int
            {
                return AbstractPriceCast::resolveSetValue($value);
            }
        };
    }
}
