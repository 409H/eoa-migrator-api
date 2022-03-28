<?php
/** @var \Laravel\Lumen\Routing\Router $router */
use Illuminate\Http\Request;
use App\Utils\NetworkMapping;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', ['middleware' => [], function () use ($router) {
    return response()->json(
        [
            'response' => 'OK'
        ], 
        200
    );
}]);

$router->get('/wallet/nfts/', ['middleware' => ['validNftQueryParams'], function(Request $request) use ($router) {

    // Map the network ID to the network name
    $objNetwork = new NetworkMapping();
    $networkName = $objNetwork->getNameFromId($request->get('network'));
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
}]);