<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

use App\Events\OrderNotCompleted;
use App\Events\CloseOrderCompleted;
use App\Events\OpenOrderCompleted;
use App\Events\TradeCancelled;

use App\Library\Services\Broker;
use App\Library\FakeOrder;

use App\Trade;
use App\User;
use App\Order;

class KeepTrackingOrder implements ShouldQueue
{
     /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'orders';

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OrderLaunched  $event
     * @return void
     */
    public function handle(OrderNotCompleted $event)
    {
        try {

            //Log::info("Tracking order: " . $event->order['order_id']);

            // Get user
            $user = User::find($event->order->user_id);

            $order = Order::find($event->order->id);

            if ($order->cancel) {

                // Call to Broker or a fakeOrder based on ENV->ORDERS_TEST
                if ( env('ORDERS_TEST', true) == true ) {

                    // TESTING SUCCESS
                    $remoteOrder = FakeOrder::success();

                    // TESTING FAIL
                    // $order = FakeOrder::fail();
                    
                }
                else {

                    // CANCEL ORDER
                    $broker = new Broker;
                    $broker->setUser($user);
                    $broker->setExchange($event->order->exchange);
                    $remoteOrder = $broker->cancelOrder2($event->order->order_id);
                    
                }
                
                // Check for remoteOrder success
                if ($remoteOrder->success == true) {

                    // Delete order from the database
                    Order::destroy($event->order->id);

                    // EVENT: TradeCancelled
                    $trade = Trade::find($event->order->trade_id);
                    event(new TradeCancelled($trade));
                    
                    // Log NOTICE: Conditional cancelled
                    Log::notice("Order #" . $event->order->id . " cancelled.");

                }
                else {

                    // Log ERROR: Broker returned error
                    Log::error("[KeepTrackingOrder] Broker: " . $remoteOrder->message);

                }

            }
            else {

                // Call to exchange or a fakeOrder based on ENV->ORDERS_TEST
                if (env('ORDERS_TEST', true) == true) {

                    if( rand() % 2 == 0) {

                        // TESTING SUCCESS
                        $onlineOrder = FakeOrder::success();    

                    }
                    else {

                        // TESTING FAIL
                        $onlineOrder = FakeOrder::notClosed();

                    }
                    
                }
                else {

                    // GET ORDER
                    $broker = new Broker;
                    $broker->setUser($user);
                    $broker->setExchange($event->order->exchange);
                    $onlineOrder = $broker->getOrder2($event->order->order_id);
                    
                }

                // Check for success on call
                if (! $onlineOrder->success) {

                    // Log ERROR: Broker returned error
                    Log::error("[User " . $user->id . "] KeepTrackingOrder Broker: " . $onlineOrder->message);

                    // Add delay before requeueing
                    sleep(env('FAILED_ORDER_DELAY', 5));

                    // Event: OrderNotCompleted
                    event(new OrderNotCompleted($event->order));
                    
                }
                else {

                    // If order is closed then update trade
                    if ( $onlineOrder->result->Closed != "" ) {

                        switch ($event->order->type) {
                            case 'open':

                                // EVENT: OpenOrderCompleted
                                event(new OpenOrderCompleted($event->order, $onlineOrder->result->PricePerUnit));
                                break;
                            
                            case 'close':

                                // EVENT: CloseOrderCompleted
                                event(new CloseOrderCompleted($event->order, $onlineOrder->result->PricePerUnit));
                                break;
                        }

                    }
                    else {

                        // If the order is not completed
                        // Add delay before requeueing
                        sleep(env('ORDER_DELAY', 0));
                        
                        // Event: OrderNotCompleted
                        event(new OrderNotCompleted($event->order));

                    }
                }    
            }  

        } catch (\Exception $e) {

            // Log CRITICAL: Exception
            Log::critical("[User " . $user->id . "] KeepTrackingOrder Exception: " . $e->getMessage());

            // Add delay before requeueing
            sleep(env('FAILED_ORDER_DELAY', 5));

            // Event: OrderNotCompleted
            event(new OrderNotCompleted($event->order));
            
        }
        
    }
}
