<?php

namespace App\JsonApi\Student\V1;

use LaravelJsonApi\Core\Server\Server as BaseServer;

class Server extends BaseServer
{

    /**
     * The base URI namespace for this server.
     *
     * @var string
     */
    protected string $baseUri = '/student/api/v1';

    /**
     * Bootstrap the server when it is handling an HTTP request.
     *
     * @return void
     */
    public function serving(): void
    {
        // no-op
    }

    /**
     * Get the server's list of schemas.
     *
     * @return array
     */
    protected function allSchemas(): array
    {
        return [
            
            Students\StudentSchema::class,
            Cities\CitySchema::class,
            LpkCategories\LpkCategorySchema::class,
            Categories\CategorySchema::class,
            Payments\PaymentSchema::class,
            Contracts\ContractSchema::class,
            Installments\InstallmentSchema::class,
            StudentIdentities\StudentIdentitySchema::class,
            StudentBankAccounts\StudentBankAccountSchema::class,
            Notifications\NotificationSchema::class,
            NotificationContents\NotificationContentSchema::class,
        ];
    }

}
