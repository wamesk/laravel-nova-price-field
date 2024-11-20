<?php

namespace Wame\LaravelNovaPriceField\Fields;

use Illuminate\Validation\ValidationException;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\FieldFilterable;
use Laravel\Nova\Fields\SupportsDependentFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class Price extends Field
{
    use SupportsDependentFields;
    use FieldFilterable;

    /**
     * The field's component.
     *
     * @var string
     */
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

    protected function makeFilter(NovaRequest $request)
    {

    }
}
