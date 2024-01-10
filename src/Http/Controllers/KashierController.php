<?php

namespace Alaa\Kashier\Http\Controllers;

use Alaa\Kashier\Services\Gateways\KashierPaymentService;
use Botble\Ecommerce\Models\Order;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Payment\Repositories\Interfaces\PaymentInterface;
use Botble\Payment\Services\Traits\PaymentErrorTrait;
use Botble\Payment\Supports\PaymentHelper;
use Illuminate\Support\Facades\Auth;


class KashierController extends BaseController
{

    public function getCallback(
        BaseHttpResponse $response,

    ) {
        Auth::shouldUse("customer");
        $status = KashierPaymentService::checkPayment() && \request('paymentStatus') == 'SUCCESS';
        $orderIds = collect(explode('-' , request('merchantOrderId')))->filter(function ($id){
            return is_numeric($id);
        })->toArray();
        PaymentHelper::storeLocalPayment([
            'amount'          => request('amount'),
            'currency'        => request('currency'),
            'charge_id'       => request('orderReference'),
            'payment_channel' => KASHIER_PAYMENT_METHOD_NAME,
            'status'          => $status ? PaymentStatusEnum::COMPLETED() : PaymentStatusEnum::FAILED(),
            'customer_id'     => auth('customer')->check() ? auth('customer')->user()->getAuthIdentifier() : null,
            'order_id'        => $orderIds,
        ]);
        OrderHelper::processOrder( $orderIds, request('orderReference'));
        if (! $status  ) {
            return $response
                ->setError()
                ->setNextUrl(route('public.checkout.success', OrderHelper::getOrderSessionToken()))
                ->setMessage(__('Payment failed!'));
        }

        return $response
            ->setNextUrl(route('public.checkout.success', OrderHelper::getOrderSessionToken()))
            ->setMessage(__('Checkout successfully!'));

    }
}
