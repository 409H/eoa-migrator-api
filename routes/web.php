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

/**
 * Returns a list of networks that the API supports for fetching NFT data
 */
$router->get('/wallet/nfts/networks', [
    'uses' => 'NftController@getSupportedNetworks'
]);

/**
 * Return a list of NFTs for a specific wallet
 */
$router->get('/wallet/nfts/', [
    'middleware' => ['validNftQueryParams'],
    'uses' => 'NftController@getAccountNfts'
]);

/**
 * Return a list of recent transactions for a specific wallet
 */
$router->get('/wallet/transactions', [
    'middleware' => ['validTransactionsQueryParams'],
    'uses' => 'TransactionController@getTransactions'
]);

/**
 * Returns a list of networks that the API supports for fetching transaction data
 */
$router->get('/wallet/transactions/networks', [
    'uses' => 'TransactionController@getSupportedNetworks'
]);

/**
 * Return estimates of gas prices for a specific network
 */
$router->get('/gas', [
    'middleware' => ['validGasQueryParams'],
    'uses' => 'GasController@getGasEstimates'
]);

/**
 * Returns a list of networks that the API supports for fetching gas data
 */
$router->get('/gas/networks', [
    'uses' => 'GasController@getSupportedNetworks'
]);