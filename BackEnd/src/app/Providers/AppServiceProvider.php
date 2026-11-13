<?php

namespace App\Providers;

use App\JsonApi\BackOffice\V1\DuikuPOPPaymentTransactions\DuikuPOPPaymentTransactionSchema;
use Illuminate\Support\ServiceProvider;
use LaravelJsonApi\Core\Schema\TypeResolver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        TypeResolver::register(
            DuikuPOPPaymentTransactionSchema::class,
            'duiku-pop-payment-transaction',
        );
    }
}
