<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ReceiveMessageFromA
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        //
        $connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST'),
            env('RABBITMQ_PORT'),
            env('RABBITMQ_USER'),
            env('RABBITMQ_PASSWORD')
        );
        $channel = $connection->channel();

        // Khai báo queue
        $channel->queue_declare('order_queue_test', false, true, false, false, false);

        $callback = function ($msg) {
            Log::info('Received message in listener: ' . $msg->body);

            echo 'Received: ', $msg->body, "\n";
            Log::info('Received: ', $msg->body);
            // Xử lý dữ liệu và tạo phản hồi
            $responseData = 'Response to: ' . $msg->body;

            // Gửi phản hồi trở lại Service A
            $responseConnection = new AMQPStreamConnection(
                env('RABBITMQ_HOST'),
                env('RABBITMQ_PORT'),
                env('RABBITMQ_USER'),
                env('RABBITMQ_PASSWORD')
            );
            $responseChannel = $responseConnection->channel();
            $responseChannel->queue_declare('product_response_queue', false, true, false, false, false);

            $responseMsg = new AMQPMessage($responseData);
            $responseChannel->basic_publish($responseMsg, '', 'product_response_queue');

            $responseChannel->close();
            $responseConnection->close();
        };

        // Lắng nghe queue
        $channel->basic_consume('order_queue_test', '', false, true, false, false, $callback);

        // Giữ cho script chạy
        while ($channel->is_consuming()) {
            $channel->wait();
        }

        Log::info("Listening for A");

        $channel->close();
        $connection->close();
    }
}
