<?php

namespace App\Console\Commands;

use App\Events\QueueEvent;
use Illuminate\Console\Command;

class QueueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:queue-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Lấy thông điệp từ đối số command
        $message = $this->argument('message');
        
        // Dispatch event
        event(new QueueEvent($message));
        
        $this->info('Event dispatched with message: ' . $message);
    }
}
