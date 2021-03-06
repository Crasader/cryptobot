<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Library\Services\CoinGuru;

use App\User;
use App\Portfolio;
use App\PortfolioOrigin;
use App\PortfolioAsset;
use App\Transaction;

class PortfolioAssetController extends Controller
{
    private $user;

     /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the trades dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    	try {
    		// Validate form
	        $validatedData = $request->validate([
	            'asset_symbol' => 'required',
	            'asset_amount' => 'required',
                'asset_initial_price' => 'required'
	        ]);

	    	$this->user = Auth::user();

	        $portfolio = Portfolio::where('user_id', $this->user->id)->first();
	       

            $guru = new CoinGuru;
            $logoBaseUrl = $guru->cryptocompareCoingetList()->BaseImageUrl;
            $infoBaseUrl = $guru->cryptocompareCoingetList()->BaseLinkUrl;
            $symbol = $request->asset_symbol;
            $coinInfo = $guru->cryptocompareCoingetList()->Data->$symbol;
            $nativeCurrency = strtoupper($request->asset_initial_price_currency);
            $coinPriceInBtc = $guru->getHistoricalPrice($nativeCurrency, ['BTC'], (string)strtotime($request->asset_purchase_date))->$nativeCurrency->BTC;

	        $asset = new PortfolioAsset;
	        $asset->portfolio_id = $portfolio->id;
	        $asset->user_id = $this->user->id;
	        $asset->origin_id = $request->asset_origin;
            $asset->origin_name = $request->asset_origin_name;
	        $asset->symbol = $request->asset_symbol;
	        $asset->amount = $request->asset_amount;
            $asset->full_name = $coinInfo->CoinName;
            $asset->logo_url = $logoBaseUrl . $coinInfo->ImageUrl;
            $asset->info_url = $infoBaseUrl . $coinInfo->Url;
	        $asset->price = 0;
	        $asset->balance = 0;
	        $asset->counter_value = 0;
            $asset->update_id = "-";
            $asset->initial_price = floatval($request->asset_initial_price) * floatval($coinPriceInBtc);
            $asset->purchase_date = $request->asset_purchase_date; 
	        $asset->save();

	        return redirect('/portfolio');
    	
    	} catch (\Exception $e) {
    
    		return response($e->getMessage(). " " . $e->getCode() . " " . $e->getFile() . ":" . $e->getLine(), 500)->header('Content-Type', 'text/plain');
    		
    	}
    	
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
         try {

            $asset = PortfolioAsset::where('id', $id)->first();

            $asset->amount = $request->asset_amount;
            $asset->initial_price = $request->asset_initial_price;
            $asset->save();

            return redirect('/portfolio');


        } catch (\Exception $e) {

            return response($e->getMessage(), 500)->header('Content-Type', 'text/plain');

        }
    }

    public function destroy($id)
    {
         try {
            $user = Auth::user();

            $asset = $user->assets->where('id', $id)->first();
            PortfolioAsset::destroy($asset->id);

            return redirect('/portfolio');


        } catch (\Exception $e) {

            return response($e->getMessage(), 500)->header('Content-Type', 'text/plain');

        }
    }

    public function get($id)
    {
        try {

            $asset = PortfolioAsset::where('id', $id)->first();
           
            if ($asset) {
                return response($asset, 200);
            }
            else {
                return response("No asset found", 500);
            }

        } catch (\Exception $e) {

            return response($e->getMessage(), 500)->header('Content-Type', 'text/plain');

        }

    }

    public function getall()
    {
        try {
            $this->user = Auth::user();

            $portfolio = Portfolio::where('user_id', $this->user->id)->first();

            if ($portfolio->assets) {
                return response($portfolio->assets, 200);
            }
            else {
                return response("No assets found", 500);
            }

        } catch (\Exception $e) {

            return response($e->getMessage(), 500)->header('Content-Type', 'text/plain');

        }

    }


    public function settransaction(Request $request, $id)
    {
         try {
            $this->user = Auth::user();

            $asset = PortfolioAsset::where('id', $id)->first();
            $transaction = new Transaction;

            $transaction->asset_id = $asset->id;
            $transaction->portfolio_id = $asset->portfolio->id;
            $transaction->user_id = $this->user->id;
            $transaction->amount = $request->transaction_amount;
            $transaction->label = $request->transaction_label;
            $transaction->type = $request->transaction_type;
            $transaction->save();

            switch ( strtolower($request->transaction_type)) {
                case 'in':
                    $asset->amount = floatval($asset->amount) + floatval($transaction->amount);
                    $asset->save();
                    break;
                
                case 'out':

                    $asset->amount = floatval($asset->amount) - floatval($transaction->amount);
                    $asset->save();
                    break;
            }

            return redirect('/portfolio');


        } catch (\Exception $e) {

            return response($e->getMessage(), 500)->header('Content-Type', 'text/plain');

        }
    }

    public function gettransactions($originId, $assetId)
    {
         try {
            $this->user = Auth::user();

            $assets =  $this->user->assets->where('origin_id', $originId);

            $asset = $assets->where('id', $assetId)->first();

            $transactions = $this->user->transactions->where('asset_id', $assetId);

            return response($transactions, 200);


        } catch (\Exception $e) {

            return response($e->getMessage(), 500)->header('Content-Type', 'text/plain');

        }
    }

    public function deletetransaction($id)
    {
         try {
            $this->user = Auth::user();

            $transactions =  $this->user->transactions;

            $transaction = $transactions->where('id', $id)->first();

// MODIFICAR LA CANTIDAD!! COPIAR DE ADD
            $transaction->delete();
            
            return response("Ok", 200);


        } catch (\Exception $e) {

            return response($e->getMessage(), 500)->header('Content-Type', 'text/plain');

        }
    }


}
