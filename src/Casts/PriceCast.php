<?php

namespace Wame\LaravelNovaPriceField\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Money\Currency;
use Money\Money;

class PriceCast implements Castable
{
    public ?int $price;

    public ?int $tax = null;

    public ?int $quantity = null;

    public function __construct(?int $data, ?int $tax, ?int $quantity)
    {
        $this->price = $data;
        $this->tax = $tax;
        $this->quantity = $quantity;
    }

    public function __get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        if (method_exists($this, 'get'.ucfirst($name))) {
            return $this->{'get'.ucfirst($name)}();
        }

        return null;
    }

    public function __set(string $name, mixed $value): void
    {
        if (property_exists($this, $name)) {
            $this->{$name} = $value;
        }
    }

    public static function castUsing(array $arguments)
    {
        return new class implements CastsAttributes
        {
            public function get(Model $model, string $key, mixed $value, array $attributes): ?PriceCast
            {
                if ($value === null || $value === 'null') {
                    return null;
                }

                $taxColumn = $model::$taxColumn ?? 'tax';
                $quantityColumn = $model::$quantityColumn ?? 'quantity';

                return new PriceCast($value, $model->$taxColumn, $model->$quantityColumn);
            }

            /**
             * @param  mixed|PriceCast  $value
             */
            public function set(Model $model, string $key, mixed $value, array $attributes): ?int
            {
                // Convert from EUR to cents by multiplying
                if (is_string($value) || is_int($value) || is_float($value)) {
                    $value = (float) $value;
                    $value = (int) ($value * 100);

                    return $value;
                }

                // Return price value if class is PriceCast
                if (get_class($value) === PriceCast::class) {
                    return $value->price;
                }

                // If no conditions met just return value
                return $value;
            }
        };
    }

    public function asFloat(): float
    {
        return (float) ($this->price / 100);
    }

    public function withTax(bool $formatted = false): Money|string
    {
        $value = new Money($this->price, new Currency('EUR'));

        if ($formatted) {
            return currency_format($value);
        }

        return $value;
    }

    public function withoutTax(bool $formatted = false): Money|string|null
    {
        if ($this->tax === null) {
            return null;
        }

        $taxDivider = ($this->tax / 100) + 1;

        $value = $this->withTax()->divide((string) $taxDivider);

        if ($formatted) {
            return currency_format($value);
        }

        return $value;
    }

    public function tax(bool $formatted = false): int|string|null
    {
        if ($formatted) {
            return $this->tax.' %';
        }

        return $this->tax;
    }

    public function taxAmount(bool $formatted = false): Money|string|null
    {
        if ($this->tax === null) {
            return null;
        }

        $withTax = $this->withTax();
        $withoutTax = $this->withoutTax();

        if (! isset($withTax, $withoutTax)) {
            return null;
        }

        $value = $withTax->subtract($withoutTax);

        if ($formatted) {
            return currency_format($value);
        }

        return $value;
    }

    public function totalTaxAmount(bool $formatted = false): Money|string|null
    {
        if ($this->tax === null) {
            return null;
        }

        $withTax = $this->totalWithTax();
        $withoutTax = $this->totalWithoutTax();

        if (! isset($withTax, $withoutTax)) {
            return null;
        }

        $value = $withTax->subtract($withoutTax);

        if ($formatted) {
            return currency_format($value);
        }

        return $value;
    }

    public function totalWithTax(bool $formatted = false): Money|string|null
    {
        if (! isset($this->quantity)) {
            return null;
        }

        $value = $this->withTax()->multiply($this->quantity);

        if ($formatted) {
            return currency_format($value);
        }

        return $value;
    }

    public function totalWithoutTax(bool $formatted = false): Money|string|null
    {
        if (! isset($this->quantity) && ! $this->withoutTax() !== null) {
            return null;
        }

        $value = $this->withoutTax()->multiply($this->quantity);

        if ($formatted) {
            return currency_format($value);
        }

        return $value;
    }
}