<?php

namespace App\JsonApi\BackOffice\V1;

use LaravelJsonApi\Core\Server\Server as BaseServer;

class Server extends BaseServer
{

    /**
     * The base URI namespace for this server.
     *
     * @var string
     */
    protected string $baseUri = '/back-office/api/v1';

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
            Users\UserSchema::class,
            Categories\CategorySchema::class,
            Cities\CitySchema::class,
            Installments\InstallmentSchema::class,
            PaymentPlans\PaymentPlanSchema::class,
            LpkCategories\LpkCategorySchema::class,
            Students\StudentSchema::class,
            StudentStatuses\StudentStatusSchema::class,
            StudentIdentities\StudentIdentitySchema::class,
            StudentBankAccounts\StudentBankAccountSchema::class,
            ContractStatuses\ContractStatusSchema::class,
            Contracts\ContractSchema::class,
            Lpks\LpkSchema::class,
            Roles\RoleSchema::class,
            Payments\PaymentSchema::class,
            DuikuPOPPaymentTransactions\DuikuPOPPaymentTransactionSchema::class,
            Notifications\NotificationSchema::class,
            NotificationContents\NotificationContentSchema::class,
            NotificationStudents\NotificationStudentSchema::class,
        ];
    }

  

}
