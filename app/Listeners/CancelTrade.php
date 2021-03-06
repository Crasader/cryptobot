<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

use App\Events\TradeCancelled;

use App\Notifications\TradeCancelledNotification;

use App\Trade;
use App\User;

class CancelTrade
{
    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'trades';

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
     * @param  TradeCancelled  $event
     * @return void
     */
    public function handle(TradeCancelled $event)
    {

        try {

            // Mark event as cancelled
            $trade = Trade::find($event->trade->id);
            $trade->status = "Cancelled";
            $trade->save();

            // NOTIFY: Trade Cancelled
            User::find($trade->user_id)->notify(new TradeCancelledNotification($trade));

            // Log INFO: Event cancelled
            Log::info("Trade #" . $trade->id . ": Cancelled.");
            
        } catch (\Exception $e) {

            // Log CRITICAL: Exception
            Log::critical("[User " . $trade->user_id . "] CancelTrade Exception: " . $e->getMessage());

        }

    }
    
}
