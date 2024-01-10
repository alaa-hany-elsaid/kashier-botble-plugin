@if (get_payment_setting('status', KASHIER_PAYMENT_METHOD_NAME) == 1)
    <li class="list-group-item">
        <input
                class="magic-radio js_payment_method"
                id="payment_kashier"
                name="payment_method"
                type="radio"
                value="kashier"
                @if ($selecting == KASHIER_PAYMENT_METHOD_NAME) checked @endif
                data-toggle="collapse" data-target=".payment_{{ KASHIER_PAYMENT_METHOD_NAME }}_wrap"
        >
        <label class="text-start" for="payment_kashier">
            {{ get_payment_setting('name', 'kashier', trans('plugins/payment::payment.payment_via_kashier')) }}
        </label>

        <div
                class="payment_kashier_wrap payment_collapse_wrap collapse @if ($selecting == KASHIER_PAYMENT_METHOD_NAME) show @endif"
                style="padding: 15px 0;"
        >
            <p>{!! BaseHelper::clean(get_payment_setting('description', 'kashier')) !!}</p>

            @php $supportedCurrencies = Alaa\Kashier\Services\Gateways\KashierPaymentService::supportedCurrencyCodes(); @endphp
            @if (function_exists('get_application_currency') &&
                    !in_array(get_application_currency()->title, $supportedCurrencies) &&
                    !get_application_currency()->replicate()->where('title', 'USD')->exists())
                <div
                        class="alert alert-warning"
                        style="margin-top: 15px;"
                >
                    {{ __(":name doesn't support :currency. List of currencies supported by :name: :currencies.", ['name' => 'Kashier', 'currency' => get_application_currency()->title, 'currencies' => implode(', ', $supportedCurrencies)]) }}


                    @php
                        $currencies = get_all_currencies();

                        $currencies = $currencies->filter(function ($item) use ($supportedCurrencies) {
                            return in_array($item->title, $supportedCurrencies);
                        });
                    @endphp
                    @if (count($currencies))
                        <div style="margin-top: 10px;">
                            {{ __('Please switch currency to any supported currency') }}:&nbsp;&nbsp;
                            @foreach ($currencies as $currency)
                                <a
                                        href="{{ route('public.change-currency', $currency->title) }}"
                                        @if (get_application_currency_id() == $currency->id) class="active" @endif
                                ><span>{{ $currency->title }}</span></a>
                                @if (!$loop->last)
                                    &nbsp; | &nbsp;
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </li>
@endif
