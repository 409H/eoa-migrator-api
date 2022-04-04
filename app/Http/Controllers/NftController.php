<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Utils\NetworkMapping;

class NftController extends Controller
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
            $class = 'App\\Http\\Providers\\Nfts\\'. $formattedNetworkName .'\Consumer';
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

    public function getAccountNfts(Request $request)
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

        $class = 'App\\Http\\Providers\\Nfts\\'. $formattedNetworkName .'\Consumer';
        if(class_exists($class) === false) {
            return response()->json(
                [
                    'response' => 'ERROR',
                    'code' => "ERR_NO_DATA_PROVIDER",
                    'message' => "We currently do not have an NFT data provider for this network."
                ], 
                400
            );
        }

        $objNftProvider = new $class();
        $nftData = $objNftProvider->getDataFromApi(
            $request->get('address'),
            $request->get('offset'),
            $request->get('limit'),
        )->getResponse();


        return response()->json(
            [
                'response' => 'OK',
                'data' => $nftData
            ], 
            200
        );
    }
}
