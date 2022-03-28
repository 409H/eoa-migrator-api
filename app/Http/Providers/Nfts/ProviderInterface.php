<?php
namespace App\Http\Providers\Nfts;

interface ProviderInterface
{
    public function getSupportedNetworkId();
    public function getProviderName();

    // Fetches data from the third-party API
    public function getDataFromApi(string $userAddress, int $offset, int $limit);

    // Formats the response from the API
    public function formatResponse(array $response);

    // Returns a uniformed response for the application to use
    public function getResponse();
}