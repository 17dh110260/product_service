<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQConsumer extends Command
{
    protected $signature = 'rabbitmq:consume';
    protected $description = 'Consume messages from RabbitMQ';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Log::info('RabbitMQ Consumer started.');

        try {
            $connection = new AMQPStreamConnection(
                env('RABBITMQ_HOST'),
                env('RABBITMQ_PORT'),
                env('RABBITMQ_USER'),
                env('RABBITMQ_PASSWORD')
            );
            $channel = $connection->channel();

            $queueName = 'order_queue_test';

            // Declare queue (must match sender queue)
            $channel->queue_declare($queueName, false, true, false, false);

            $callback = function ($msg) use ($channel) {
                $body = \json_decode($msg->body);
                if (!empty($body)) {
                    $products = $body->product_id;
                    foreach ($products as $key => $product_id) {
                        $product = DB::table('products')->select()->where('id', $product_id)->first();
                        if ($product) {
                            $responseData[$key] = [
                                'product_id' => $product->id,
                                'name' => $product->name,
                                'unit_price' => $product->price
                            ];
                        } else {
                            $responseData[$key] = [
                                'error' => 'Product not found'
                            ];
                        }
                    }
                    $responseData = json_encode($responseData);
                    
                }
                $responseMessage = new AMQPMessage($responseData, [
                    'correlation_id' => $msg->get('correlation_id')
                ]);
                $channel->basic_publish($responseMessage, '', $msg->get('reply_to'));

                Log::info('Received message: ' . $msg->body);
                echo " [x] Received ", $msg->body, "\n";

                // Process the message here (e.g., store in DB, trigger actions, etc.)
            };

            // Start consuming
            $channel->basic_consume($queueName, '', false, true, false, false, $callback);

            while ($channel->is_consuming()) {
                $channel->wait();
            }

            $channel->close();
            $connection->close();
        } catch (\Exception $e) {
            Log::error('RabbitMQ Consumer error: ' . $e->getMessage());
        }
    }
}
