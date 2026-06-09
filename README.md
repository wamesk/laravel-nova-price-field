# Laravel Nova Price Field

## Installation

```php
composer require wamesk/laravel-nova-price-field
```

## Casts

This package provides two Eloquent casts for storing prices as integers (cents).

### PriceCast

Full-featured cast with tax support. Use when the model has tax logic (`withTax`, `withoutTax`, `taxAmount`, etc.).

```php
use Wame\LaravelNovaPriceField\Casts\PriceCast;

protected function casts(): array
{
    return [
        'price' => PriceCast::class,
    ];
}
```

**Available methods on the cast value:**

| Method | Description |
|---|---|
| `asFloat()` | Price as float (cents ÷ 100) |
| `asMoney()` | `Money` object |
| `formatted()` | Formatted string (e.g. `1 234,50 EUR`) |
| `withTax($formatted)` | `Money` object or formatted string |
| `withoutTax($formatted)` | `Money` object or formatted string (requires tax config) |
| `tax($formatted)` | Tax rate as int or formatted string |
| `taxAmount($formatted)` | Tax amount as `Money` or formatted string |
| `totalWithTax($formatted)` | Total incl. tax × quantity |
| `totalWithoutTax($formatted)` | Total excl. tax × quantity |
| `totalTaxAmount($formatted)` | Total tax amount × quantity |

**Optional model static properties:**

```php
// Automatically reads price_without_tax column
public static string|bool $priceWithoutTaxColumn = true;

// Custom tax column (default: 'tax')
public static string $taxColumn = 'vat';

// Custom quantity column (default: 'quantity')
public static string $quantityColumn = 'qty';

// Custom currency column
public static string $currencyColumn = 'currency_code';
```

### SimplePriceCast

Lightweight cast without any tax logic. Use for cost fields, budgets, or any price stored without VAT context.

```php
use Wame\LaravelNovaPriceField\Casts\SimplePriceCast;

protected function casts(): array
{
    return [
        'planned_cost' => SimplePriceCast::class,
        'actual_cost'  => SimplePriceCast::class,
    ];
}
```

**Available methods on the cast value:**

| Method | Description |
|---|---|
| `asFloat()` | Price as float (cents ÷ 100) |
| `asMoney()` | `Money` object |
| `formatted()` | Formatted string (e.g. `1 234,50 EUR`) |

Both casts share the same base class (`AbstractPriceCast`) and resolve the currency from the model's `currency`, `currency_id`, or `$currencyColumn` property (defaulting to `EUR`).

---

## Nova Field

### Auto-detection of cast type

The `Price` field automatically detects which cast is used on the model attribute and adjusts its behaviour accordingly — no manual configuration needed.

| Cast | Behaviour |
|---|---|
| `PriceCast` | Full tax UI: shows `withTax`, `withoutTax`, tax amount, tax percentage |
| `SimplePriceCast` | Tax UI disabled automatically; shows only the formatted price |

If `->withAllFieldOnForm()` is called on a field backed by `SimplePriceCast`, the tax input sub-fields are suppressed automatically (the `with_all_field_on_form` meta is overridden to `false` at resolve time).

```php
// PriceCast — full tax fields shown automatically
Price::make('Price', 'price'),

// SimplePriceCast — tax fields hidden automatically, even with withAllFieldOnForm()
Price::make('Planned cost', 'planned_cost'),
```



Shows a single input for price. When viewing on detail or index, shows price with currency suffix.

```php
use Wame\LaravelNovaPriceField\Fields\Price;

Price::make('price'),
```

### Showing total price

```php
Price::make('price')
    ->asTotal(),
```

Usage should be only for viewing (detail and index page).

### Multiple fields on form

Adds price without tax and tax input with automatic calculation. Saves only price with tax.

```php
Price::make()
    ->withAllFieldOnForm()
```

With custom column names:

```php
Price::make()
    ->withAllFieldOnForm(withoutTaxColumn: 'price_without_tax', taxColumn: 'tax')
```

### Using price inputs without tax input

```php
Price::make()
    ->withAllFieldOnForm()
    ->hideTaxFormField()
    ->setTax(23)
```

### Using predefined tax options

```php
Price::make()
    ->withAllFieldOnForm()
    ->taxFieldAsSelect([
        10 => ['name' => 'Reduced tax (10%)'],
        23 => ['name' => 'Standard tax (23%)'],
    ])
```

Save the selected option ID:

```php
Price::make()
    ->withAllFieldOnForm()
    ->taxFieldAsSelect([
        10 => ['id' => 'reduced',  'name' => 'Reduced tax (10%)'],
        23 => ['id' => 'standard', 'name' => 'Standard tax (23%)'],
    ], 'vat_rate_type')
```

Save the option value (key) instead of ID:

```php
Price::make()
    ->withAllFieldOnForm()
    ->taxFieldAsSelect([
        10 => ['id' => 'reduced',  'name' => 'Reduced tax (10%)'],
        23 => ['id' => 'standard', 'name' => 'Standard tax (23%)'],
    ], 'vat_rate_type', true)
```
