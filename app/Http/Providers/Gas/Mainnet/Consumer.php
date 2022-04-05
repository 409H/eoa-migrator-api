<?php
namespace App\Http\Providers\Gas\Mainnet;

use \GuzzleHttp\Client;
use App\Http\Providers\Gas\BaseProvider;
use App\Http\Providers\Gas\ProviderInterface;

class Consumer extends BaseProvider implements ProviderInterface 
{
    const SUPPORTED_NETWORK_ID = 1;
    const PROVIDER_NAME = "Etherscan";
    
    private $formattedResponse;

    public function getSupportedNetworkId() 
    {
        return self::SUPPORTED_NETWORK_ID;
    }
    
    public function getProviderName() 
    {
        return self::PROVIDER_NAME;
    }

    public function getDataFromApi(): Consumer
    {
        $client = new Client([
            'base_uri' => 'https://api.etherscan.io/api',
            'headers' => [
            ],
        ]);

        try {
            $res = $client->request(
                'GET', 
                "?module=gastracker&action=gasoracle&apikey=". env("PROVIDER_TX_MAINNET_API_KEY", "NO_API_KEY")
            );
        } catch(GuzzleHttp\Exception\ClientException $e) {
            // Something went wrong with the request to the third-party
        }

        // If we don't get a HTTP 200 from the API
        if($res->getStatusCode() !== 200) {
            throw new Exception("Invalid response from third-party. Expected HTTP 200, got HTTP {$res->getStatusCode()}");
        }

        return $this->formatResponse(json_decode($res->getBody()->getContents(), true));
    }

    public function formatResponse(array $rawResponse): Consumer
    {
        /**
         * Desired output is:
         * {
         *      provider: <provider_name>
         *      gas: [
         *          safe: int,
         *          fast: int,
         *          base_fee: int
         *      ]
         * }
         */

        $output = [
            "provider" => $this->getProviderName(),
            "gas" => [
                "safe" => ($rawResponse["result"]["SafeGasPrice"]) ?? 0,
                "fast" => ($rawResponse["result"]["FastGasPrice"]) ?? 0,
                "base_fee" => ($rawResponse["result"]["suggestBaseFee"]) ?? 0,
            ]
        ];


        $this->formattedResponse = $output;

        return $this;
    }

    public function getResponse()
    {
        return $this->formattedResponse;
    }
}