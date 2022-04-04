<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Utils\NetworkMapping;

class TransactionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function getSupportedNetworks()
    {
        $objNetwork = new NetworkMapping();
        $allNetworks = $objNetwork->getAll();
    
        $output = [];
    
        // See if we have providers for all the networks
        foreach($allNetworks as $network) {
            $formattedNetworkName = $objNetwork->formatNetworkName($network["name"]);
            $class = 'App\\Http\\Providers\\Transactions\\'. $formattedNetworkName .'\Consumer';
            if(class_exists($class) === false) {
                continue;
            }
    
            $output[] = $network;
        }
    
        return response()->json(
            [
                'response' => 'OK',
                'data' => $output
            ], 
            200
        );
    }

    public function getTransactions(Request $request)
    {
       // Map the network ID to the network name
       $objNetwork = new NetworkMapping();

       try {
            $networkName = $objNetwork->getNameFromId($request->get('network'));
       } catch(\Exception $e) {
            return response()->json(
                [
                    'response' => 'ERROR',
                    'code' => "ERR_UNSUPPORTED_NETWORK",
                    'message' => "We currently do not support this network."
                ], 
                400
            );
       }

       $formattedNetworkName = $objNetwork->formatNetworkName($networkName);

       $class = 'App\\Http\\Providers\\Transactions\\'. $formattedNetworkName .'\Consumer';
       if(class_exists($class) === false) {
           return response()->json(
               [
                   'response' => 'ERROR',
                   'code' => "ERR_NO_DATA_PROVIDER",
                   'message' => "We currently do not have an transaction data provider for this network."
               ], 
               400
           );
       }

       $objTxProvider = new $class();
       $txData = $objTxProvider->getDataFromApi(
           $request->get('address')
       )->getResponse();


       return response()->json(
           [
               'response' => 'OK',
               'data' => $txData
           ], 
           200
       );
    }
}
