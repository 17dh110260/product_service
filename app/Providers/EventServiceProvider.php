<?php

namespace App\Providers;

use App\Events\QueueEvent;
use App\Listeners\ReceiveMessageFromA;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // Đăng ký event và listener của bạn ở đây
        QueueEvent::class => [
            ReceiveMessageFromA::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
