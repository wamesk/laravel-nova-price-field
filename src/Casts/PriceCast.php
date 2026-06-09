<?php

declare(strict_types = 1);

namespace Wame\LaravelNovaPriceField\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Money\Currency;
use Money\Money;

class PriceCast extends AbstractPriceCast
{
    public ?int $priceWithoutTax;

    public ?int $tax = null;

    public float|int|null $quantity = null;

    public function __construct(?int $data, ?int $priceWithoutTax, ?int $tax, float|int|null $quantity, string $currency = 'EUR')
    {
        parent::__construct($data, $currency);
        $this->priceWithoutTax = $priceWithoutTax;
        $this->tax = $tax;
        $this->quantity = $quantity;
    }

    public function __get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        if (method_exists($this, 'get' . ucfirst($name))) {
            return $this->{'get' . ucfirst($name)}();
        }

        return null;
    }

    public function __set(string $name, mixed $value): void
    {
        if (property_exists($this, $name)) {
            $this->{$name} = $value;
        }
    }

    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class () implements CastsAttributes {
            public function get(Model $model, string $key, mixed $value, array $attributes): ?PriceCast
            {
                if (null === $value || 'null' === $value) {
                    return null;
                }

                $priceWithoutTax = null;
                if (isset($model::$priceWithoutTaxColumn)) {
                    if (true === $model::$priceWithoutTaxColumn) {
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
                $currency = AbstractPriceCast::resolveCurrency($model, $attributes);

                if (isset($model->{$taxColumn})) {
                    $tax = $model->{$taxColumn};
                } elseif (isset($attributes[$taxColumn])) {
                    $tax = $model->{$taxColumn};
                } else {
                    $tax = null;
                }

                return new PriceCast($value, $priceWithoutTax, $tax, $attributes[$quantityColumn] ?? null, $currency);
            }

            public function set(Model $model, string $key, mixed $value, array $attributes): ?int
            {
                return AbstractPriceCast::resolveSetValue($value);
            }
        };
    }

    public function withTax(bool $formatted = false): Money|string
    {
        $value = $this->asMoney();

        if ($formatted) {
            return currency_format($value);
        }

        return $value;
    }

    public function withoutTax(bool $formatted = false): Money|string|null
    {
        if (null === $this->priceWithoutTax && null === $this->tax) {
            return null;
        }

        $value = null;

        if (null !== $this->priceWithoutTax) {
            $value = new Money($this->priceWithoutTax, new Currency($this->currency));
        }

        if (null === $value) {
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
            return $this->tax . ' %';
        }

        return $this->tax;
    }

    public function taxAmount(bool $formatted = false): Money|string|null
    {
        $withoutTax = $this->withoutTax();

        if (!isset($withoutTax)) {
            return null;
        }

        $withTax = $this->withTax();

        if (!isset($withTax, $withoutTax)) {
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
        if (null === $this->tax) {
            return null;
        }

        $withTax = $this->totalWithTax();
        $withoutTax = $this->totalWithoutTax();

        if (!isset($withTax, $withoutTax)) {
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
        if (!isset($this->quantity)) {
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
        if (!isset($this->quantity) || null === $this->withoutTax()) {
            return null;
        }

        $value = $this->withoutTax()->multiply((string) $this->quantity);

        if ($formatted) {
            return currency_format($value);
        }

        return $value;
    }
}
