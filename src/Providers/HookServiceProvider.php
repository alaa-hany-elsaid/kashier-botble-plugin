<?php

namespace Alaa\Kashier\Providers;

use Alaa\Kashier\Services\Gateways\KashierPaymentService;
use Botble\Ecommerce\Repositories\Interfaces\OrderAddressInterface;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Facades\PaymentMethods;
use Html;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Throwable;

class HookServiceProvider extends ServiceProvider
{
    public function boot()
    {

        add_filter(PAYMENT_FILTER_ADDITIONAL_PAYMENT_METHODS, [$this, 'registerKashierMethod'], 1, 2);
        $this->app->booted(function () {
            add_filter(PAYMENT_FILTER_AFTER_POST_CHECKOUT, [$this, 'checkoutWithKashier'], 1, 2);
        });

        add_filter(PAYMENT_METHODS_SETTINGS_PAGE, [$this, 'addPaymentSettings'], -1);

        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class == PaymentMethodEnum::class ) {
                $values['KASHIER'] = KASHIER_PAYMENT_METHOD_NAME;
            }

            return $values;
        }, 1, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == KASHIER_PAYMENT_METHOD_NAME) {
                $value = 'Kashier';
            }

            return $value;
        }, 1, 2);

        add_filter(BASE_FILTER_ENUM_HTML, function ($value, $class) {
//            dd(KASHIER_PAYMENT_METHOD_NAME);
            if ($class == PaymentMethodEnum::class && $value == KASHIER_PAYMENT_METHOD_NAME) {
                $value = Html::tag(
                    'span',
                    PaymentMethodEnum::getLabel($value),
                    ['class' => 'label-success status-label']
                )
                    ->toHtml();
            }

            return $value;
        }, 1, 2);
    }

    /**
     * @param string $settings
     * @return string
     * @throws Throwable
     */
    public function addPaymentSettings($settings)
    {
        return $settings . view('plugins/kashier::settings')->render();
    }

    /**
     * @param string $html
     * @param array $data
     * @return string
     */
    public function registerKashierMethod($html, array $data)
    {
        PaymentMethods::method(KASHIER_PAYMENT_METHOD_NAME, [
            'html' => view('plugins/kashier::methods', $data)->render(),
        ]);
        return $html;
    }

    /**
     * @param Request $request
     * @param array $data
     */
    public function checkoutWithKashier($data, Request $request)
    {
        if ($data['type'] !== KASHIER_PAYMENT_METHOD_NAME) {
            return $data;
        }

        $currentCurrency = get_application_currency();

        $currencyModel = $currentCurrency->replicate();

        $currency = strtoupper($currentCurrency->title);

        $notSupportCurrency = false;
        $supportedCurrencies = KashierPaymentService::supportedCurrencyCodes();

        if (! in_array($currency, $supportedCurrencies)) {
            $notSupportCurrency = true;

            if (! $currencyModel->where('title', 'USD')->exists()) {
                $data['error'] = true;
                $data['message'] = __(
                    ":name doesn't support :currency. List of currencies supported by :name: :currencies.",
                    [
                        'name' => 'Kashier',
                        'currency' => $currency,
                        'currencies' => implode(', ', $supportedCurrencies),
                    ]
                );

                return $data;
            }
        }

        $paymentData = apply_filters(PAYMENT_FILTER_PAYMENT_DATA, [], $request);

        if ($notSupportCurrency) {
            $usdCurrency = $currencyModel->where('title', 'USD')->first();

            $paymentData['currency'] = 'USD';
            if ($currentCurrency->is_default) {
                $paymentData['amount'] = $paymentData['amount'] * $usdCurrency->exchange_rate;
            } else {
                $paymentData['amount'] = format_price(
                    $paymentData['amount'] / $currentCurrency->exchange_rate,
                    $currentCurrency,
                    true
                );
            }
        }
        $data['checkoutUrl'] = KashierPaymentService::getPaymentUrl($paymentData);
        return $data;



    }
}
