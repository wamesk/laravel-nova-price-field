<?php

namespace Wame\LaravelNovaPriceField\Fields;

use Illuminate\Validation\ValidationException;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\SupportsDependentFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class Price extends Field
{
    use SupportsDependentFields;

    protected ?string $withoutTaxColumn = null;

    protected ?string $taxColumn = null;

    public $component = 'laravel-nova-price-field';

    public function __construct($name, $attribute = null, ?callable $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->resolveUsing(function ($value, $resource) {
            if (isset($value)) {
                $this->withMeta([
                    'formatted_price_with_tax' => $value->withTax(true),
                    'formatted_price_without_tax' => $value->withoutTax(true),
                    'formatted_tax_amount' => $value->taxAmount(true),
                    'formatted_total_price_with_tax' => $value->totalWithTax(true),
                    'formatted_total_price_without_tax' => $value->totalWithoutTax(true),
                    'formatted_total_tax_amount' => $value->totalTaxAmount(true),
                ]);

                return $value->asFloat();
            }

            return null;
        });
    }

    /**
     * @throws ValidationException
     */
    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute): void
    {
        $value = $request->input($requestAttribute);
        if (isset($value)) {
            if (! is_numeric($value)) {
                throw ValidationException::withMessages([$attribute => __('validation.numeric', ['attribute' => $requestAttribute])]);
            }

            $model->{$attribute} = $value;

            if (isset($this->withoutTaxColumn)) {
                $withoutTaxColumn = $this->withoutTaxColumn;
                $model->$this->$withoutTaxColumn = $request->input($this->attribute.'_without_tax');
            }

            if (isset($this->taxColumn)) {
                $taxColumn = $this->taxColumn;
                $model->$taxColumn = $request->input($this->attribute.'_tax');
            }
        }
    }

    public function asTotal(): Price
    {
        return $this->withMeta([
            'show_total' => true,
        ]);
    }

    public function withoutTaxAmount(): Price
    {
        return $this->withMeta([
            'without_tax_amount' => true,
        ]);
    }

    public function onlyWithTax(): Price
    {
        return $this->withMeta([
            'only_with_tax' => true,
            'only_without_tax' => false,
        ]);
    }

    public function onlyWithoutTax(): Price
    {
        return $this->withMeta([
            'only_without_tax' => true,
            'only_with_tax' => false,
        ]);
    }

    public function withAllFieldOnForm(?string $withoutTaxColumn = null, ?string $taxColumn = null): Price
    {
        $this->withoutTaxColumn = $withoutTaxColumn;
        $this->taxColumn = $taxColumn;

        return $this->withMeta([
            'with_all_field_on_form' => true,
        ]);
    }

    public function setTax($tax): Price
    {
        return $this->withMeta([
            'taxValue' => $tax,
        ]);
    }

    public function hideTaxFormField(): Price
    {
        return $this->withMeta([
            'hide_tax_form_field' => true,
        ]);
    }
}
