<?php

namespace Workdo\ProductVault\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use App\Events\SuperAdminMenuEvent;
use App\Events\CompanyMenuEvent;
use Workdo\ProductVault\Listeners\SuperAdminMenuListener;
use Workdo\ProductVault\Listeners\CompanyMenuListener;

class EventServiceProvider extends Provider
{
    protected $listen = [
        SuperAdminMenuEvent::class => [
            SuperAdminMenuListener::class,
        ],
        CompanyMenuEvent::class => [
            CompanyMenuListener::class,
        ],
    ];
}