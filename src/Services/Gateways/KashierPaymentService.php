<?php

namespace Alaa\Kashier\Services\Gateways;

class KashierPaymentService
{

    public static function supportedCurrencyCodes()
    {
        return [
            'EGP',
            'USD',
            'GBP',
            'EUR'
        ];
    }

    public static function generateKashierOrderHash($mid, $secret, $order, $orderId)
    {
        $amount = $order['amount']; //eg: 100
        $currency = $order['currency']; //eg: "EGP"
        $path = "/?payment=" . $mid . "." . $orderId . "." . $amount . "." . $currency . (isset($CustomerReference) ? ("." . $CustomerReference) : null);
        return hash_hmac('sha256', $path, $secret, false);
    }


    public static function getPaymentUrl($order)
    {
//        dd($order);
        $orderId = $order['orders']->map(function ($o) {
            return $o->id ;
        })->implode('-') . "-" . uniqid();
        $mid = get_payment_setting('merchant_id', KASHIER_PAYMENT_METHOD_NAME);
        $secret = get_payment_setting('secret', KASHIER_PAYMENT_METHOD_NAME);
        $mode = get_payment_setting('mode', KASHIER_PAYMENT_METHOD_NAME, true) ? 'test' : 'live';
        $basePaymentUrl = get_payment_setting('payment_url', KASHIER_PAYMENT_METHOD_NAME, KASHIER_PAYMENT_URL);
        $hash = self::generateKashierOrderHash($mid, $secret, $order , $orderId);
        $displayLang = app()->currentLocale();
        $redirectUrl = \Str::replace("127.0.0.1" , "localhost" , route('payments.kashier.status'));
        return "$basePaymentUrl?amount=${order['amount']}&merchantId=$mid&orderId=${orderId}&"
            . "currency=${order['currency']}&hash=$hash&mode=$mode&merchantRedirect=$redirectUrl&data-type=external&data-display=$displayLang";
    }


    public static function checkPayment()
    {
        $secret = get_payment_setting('secret', KASHIER_PAYMENT_METHOD_NAME);
        $queryString = '';

        foreach (request()->all() as $key => $value) {
            if ($key == "signature" || $key == "mode") {
                continue;
            }
            $queryString = $queryString . "&" . $key . "=" . $value;
        }

        $queryString = ltrim($queryString, $queryString[0]);
        $signature = hash_hmac('sha256', $queryString, $secret, false);
//        dd(request()->all() , $signature);
        return request('signature') == $signature ;
    }
}