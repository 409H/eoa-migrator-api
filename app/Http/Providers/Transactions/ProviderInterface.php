<?php
namespace App\Http\Providers\Transactions;

interface ProviderInterface
{
    public function getSupportedNetworkId();
    public function getProviderName();

    // Fetches data from the third-party API
    public function getDataFromApi(string $userAddress);

    // Formats the response from the API
    public function formatResponse(array $response);

    // Returns a uniformed response for the application to use
    public function getResponse();
}