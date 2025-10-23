# Laravel Nova Price Field

## Installation

```php
composer install wamesk/laravel-nova-price-field
```

## Usage

### Basic usage

Shows single input for price, when viewing on detail or index page, shows price with currency suffix symbol.

```php
use Wame\LaravelNovaPriceField\Fields\Price;
....
Price::make('price'),
```

#### Showing total price

When you have quantity field (or something similar) you can show total price that dynamically calculates total price multiplying price with quantity.

```php
use Wame\LaravelNovaPriceField\Fields\Price;
....
Price::make('price')
    ->asTotal(),
```

Usage should be only for viewing (detail and index page).

#### Multiple fields on form

This adds price without tax and tax input. Includes automatic calculation. Saves only price with tax.

```php
use Wame\LaravelNovaPriceField\Fields\Price;
....
Price::make()
    ->withAllFieldOnForm()
```

If you want to save other fields you can set database column names so it can save multiple values.

```php
use Wame\LaravelNovaPriceField\Fields\Price;
....
Price::make()
    ->withAllFieldOnForm(withoutTaxColumn: 'price_without_tax', taxColumn: 'tax')
```

Now it will save values to corresponding columns.

#### Using price inputs without tax input

You can hide tax input, but the calculation is not turned off. Adding `->setTax()` method is recommended.

```php
use Wame\LaravelNovaPriceField\Fields\Price;
....
Price::make()
    ->withAllFieldOnForm()
    ->hideTaxFormField()
    ->setTax(23)
```

#### Using predefined tax options

You can add `taxFieldAsSelect()` method to change number tax input to select with options.

```php
use Wame\LaravelNovaPriceField\Fields\Price;
....
Price::make()
    ->withAllFieldOnForm()
    ->taxFieldAsSelect([
        10 => [
            'name' => 'Reduced tax (10%)',
        ],
        23 => [
            'name' => 'Standard tax (23%)',
        ],
    ])
```

You have option to save which option was selected by adding id to object and column name as 2nd parameter ot method.

```php
use Wame\LaravelNovaPriceField\Fields\Price;
....
Price::make()
    ->withAllFieldOnForm()
    ->taxFieldAsSelect([
        10 => [
            'id' => 'reduced',
            'name' => 'Reduced tax (10%)',
        ],
        23 => [
            'id' => 'standard',
            'name' => 'Standard tax (23%)',
        ],
    ], 'vat_rate_type')
```



