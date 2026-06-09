<?php

declare(strict_types = 1);

namespace Wame\LaravelNovaPriceField\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Database\Eloquent\Model;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use NumberFormatter;

abstract class AbstractPriceCast implements Castable
{
    public ?int $price;

    public string $currency;

    public string $locale;

    public function __construct(?int $price, string $currency = 'EUR')
    {
        $this->price = $price;
        $this->currency = $currency;
        $this->locale = self::resolveLocale($currency);
    }

    public function asFloat(): float
    {
        return (float) ($this->price / 100);
    }

    public function asMoney(): Money
    {
        return new Money($this->price, new Currency($this->currency));
    }

    public function formatted(): string
    {
        $numberFormatter = new NumberFormatter($this->locale, NumberFormatter::CURRENCY);
        $formatter = new IntlMoneyFormatter($numberFormatter, new ISOCurrencies());

        return $formatter->format($this->asMoney());
    }

    public static function resolveLocale(string $currencyCode): string
    {
        return match ($currencyCode) {
            'EUR' => 'de_DE',
            'CZK' => 'cs_CZ',
            'USD' => 'en_US',
            'JPY' => 'ja_JP',
            'BGN' => 'bg_BG',
            'DKK' => 'da_DK',
            'GBP' => 'en_GB',
            'HUF' => 'hu_HU',
            'PLN' => 'pl_PL',
            'RON' => 'ro_RO',
            'SEK' => 'sv_SE',
            'CHF' => 'de_CH',
            'ISK' => 'is_IS',
            'NOK' => 'nb_NO',
            'HRK' => 'hr_HR',
            'RUB' => 'ru_RU',
            'TRY' => 'tr_TR',
            'AUD' => 'en_AU',
            'BRL' => 'pt_BR',
            'CAD' => 'en_CA',
            'CNY' => 'zh_CN',
            'HKD' => 'zh_HK',
            'IDR' => 'id_ID',
            'ILS' => 'he_IL',
            'INR' => 'hi_IN',
            'KRW' => 'ko_KR',
            'MXN' => 'es_MX',
            'MYR' => 'ms_MY',
            'NZD' => 'en_NZ',
            'PHP' => 'fil_PH',
            'SGD' => 'en_SG',
            'THB' => 'th_TH',
            'ZAR' => 'en_ZA',
            default => 'en_US',
        };
    }

    public static function resolveCurrency(Model $model, array $attributes): string
    {
        if (isset($model::$currencyColumn) && !empty($attributes[$model::$currencyColumn])) {
            return $attributes[$model::$currencyColumn];
        }

        if (!empty($model->currency_id)) {
            return $model->currency_id;
        }

        if (!empty($model->currency)) {
            return $model->currency;
        }

        return 'EUR';
    }

    public static function resolveSetValue(mixed $value): ?int
    {
        if (null === $value) {
            return null;
        }

        if (is_string($value) || is_int($value) || is_float($value)) {
            return (int) round((float) $value * 100);
        }

        if ($value instanceof self) {
            return $value->price;
        }

        if ($value instanceof Money) {
            return (int) $value->getAmount();
        }

        return $value;
    }
}
