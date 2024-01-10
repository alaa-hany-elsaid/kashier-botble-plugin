<?php

namespace Alaa\Kashier\Providers;

use Botble\Base\Supports\Helper;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\ServiceProvider;

class KashierServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register()
    {
        Helper::autoload(__DIR__ . '/../../helpers');
    }

    /**
     * @throws FileNotFoundException
     */
    public function boot()
    {
        if (is_plugin_active('payment')) {
            $this->setNamespace('plugins/kashier')
                ->loadRoutes()
                ->loadAndPublishViews()
                ->loadAndPublishTranslations()
                ->publishAssets();

            $this->app->register(HookServiceProvider::class);

            $config = $this->app->make('config');

            $config->set([
                'kashier.merchant_id' => get_payment_setting('merchantId', KASHIER_PAYMENT_METHOD_NAME),
                'kashier.mode' => get_payment_setting('mode', KASHIER_PAYMENT_METHOD_NAME),
                'kashier.secret' => get_payment_setting('secret', KASHIER_PAYMENT_METHOD_NAME),
                'kashier.payment_url' => KASHIER_PAYMENT_URL,
            ]);
        }
    }
}
