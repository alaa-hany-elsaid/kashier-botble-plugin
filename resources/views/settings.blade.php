<x-plugins-payment::settings-card
        name="Kashier"
        :id="KASHIER_PAYMENT_METHOD_NAME"
        :logo="url('vendor/core/plugins/kashier/images/kashier.png')"
        url="https://www.kashier.io/"
        :description="__('You will be redirected to :name to complete the payment.', ['name' => 'Kashier'])"
>
    <x-slot:instructions>
        <ol>
            <li>
                <p>
                    <a
                            href="https://merchant.kashier.io/en/login"
                            target="_blank"
                    >
                        {{ trans('plugins/payment::payment.service_registration', ['name' => 'Kashier']) }}
                    </a>
                </p>
            </li>
            <li>
                <p>
                    {{ trans('plugins/kashier::index.after_service_registration_msg') }}
                </p>
            </li>
            <li>
                <p>
                    {{ trans('plugins/kashier::index.enter_merchant_id_and_secret') }}
                </p>
            </li>
        </ol>
    </x-slot:instructions>

    <x-slot:fields>
        <x-core::form.text-input
                :name="sprintf('payment_%s_merchant_id', KASHIER_PAYMENT_METHOD_NAME)"
                :label="trans('plugins/kashier::index.merchant_id')"
                :value="BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('merchant_id', KASHIER_PAYMENT_METHOD_NAME)"
        />

        <x-core::form.text-input
                type="password"
                :name="sprintf('payment_%s_secret', KASHIER_PAYMENT_METHOD_NAME)"
                :label="trans('plugins/payment::payment.secret')"
                :value="BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('secret', KASHIER_PAYMENT_METHOD_NAME)"
        />

        <x-core::form.on-off.checkbox
                :name="sprintf('payment_%s_mode', KASHIER_PAYMENT_METHOD_NAME)"
                :label="trans('plugins/kashier::index.test_mode')"
                :checked="get_payment_setting('mode', KASHIER_PAYMENT_METHOD_NAME, true)"
        />
    </x-slot:fields>
</x-plugins-payment::settings-card>
