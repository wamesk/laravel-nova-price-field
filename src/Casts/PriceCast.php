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

    public ?int $priceWithoutTax;

    public ?int $tax = null;

    public float|int|null $quantity = null;

    public string $currency;

    public function __construct(?int $data, ?int $priceWithoutTax, ?int $tax, float|int|null $quantity, string $currency = 'EUR')
    {
        $this->price = $data;
        $this->priceWithoutTax = $priceWithoutTax;
        $this->tax = $tax;
        $this->quantity = $quantity;
        $this->currency = $currency;
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

                $priceWithoutTax = null;
                if (isset($model::$priceWithoutTaxColumn)) {
                    if ($model::$priceWithoutTaxColumn === true) {
                        if (isset($attributes['price_without_tax'])) {
                            $priceWithoutTax = $attributes['price_without_tax'];
                        } elseif (in_array('price_without_tax', $model->getAppends())) {
                            $priceWithoutTax = $model->price_without_tax;
                        }
                    }

                    if (is_string($model::$priceWithoutTaxColumn)) {
                        $priceWithoutTaxColumn = $model::$priceWithoutTaxColumn;
                        $priceWithoutTax = $attributes[$priceWithoutTaxColumn];
                    }
                }

                $taxColumn = $model::$taxColumn ?? 'tax';
                $quantityColumn = $model::$quantityColumn ?? 'quantity';

                $currency = 'EUR';
                if (isset($model::$currencyColumn)) {
                    $currency = $attributes[$model::$currencyColumn];
                } elseif (isset($model->currency_id)) {
                    $currency = $model->currency_id;
                } elseif (isset($model->currency)) {
                    $currency = $model->currency;
                }

                if (isset($model->$taxColumn)) {
                    $tax = $model->$taxColumn;
                } elseif (isset($attributes[$taxColumn])) {
                    $tax = $model->$taxColumn;
                } else {
                    $tax = null;
                }

                return new PriceCast($value, $priceWithoutTax, $tax, $attributes[$quantityColumn] ?? null, $currency);
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
                    return (int) $value->withTax()->getAmount();
                }

                // Return price value if class is Money
                if (get_class($value) === Money::class) {
                    return (int) $value->getAmount();
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
        $value = new Money($this->price, new Currency($this->currency));

        if ($formatted) {
            return currency_format($value);
        }

        return $value;
    }

    public function withoutTax(bool $formatted = false): Money|string|null
    {
        if ($this->priceWithoutTax === null && $this->tax === null) {
            return null;
        }

        $value = null;

        if ($this->priceWithoutTax !== null) {
            $value = new Money($this->priceWithoutTax, new Currency($this->currency));
        }

        if ($value === null) {
            $taxDivider = ($this->tax / 100) + 1;

            $value = $this->withTax()->divide((string) $taxDivider);
        }

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
        $withoutTax = $this->withoutTax();

        if (! isset($withoutTax)) {
            return null;
        }

        $withTax = $this->withTax();

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

        $value = $this->withTax()->multiply((string) $this->quantity);

        if ($formatted) {
            return currency_format($value);
        }

        return $value;
    }

    public function totalWithoutTax(bool $formatted = false): Money|string|null
    {
        if (! isset($this->quantity) || $this->withoutTax() === null) {
            return null;
        }

        $value = $this->withoutTax()->multiply((string) $this->quantity);

        if ($formatted) {
            return currency_format($value);
        }

        return $value;
    }
}
