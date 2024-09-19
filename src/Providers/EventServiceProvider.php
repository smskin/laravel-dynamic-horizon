<?php

namespace SMSkin\LaravelDynamicHorizon\Providers;

use Laravel\Horizon\Events\MasterSupervisorLooped;
use SMSkin\LaravelDynamicHorizon\Listeners\LMasterSupervisorLooped;

class EventServiceProvider extends \Illuminate\Foundation\Support\Providers\EventServiceProvider
{
    protected $listen = [
        MasterSupervisorLooped::class => [
            LMasterSupervisorLooped::class,
        ],
    ];
}
