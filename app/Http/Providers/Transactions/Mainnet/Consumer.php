<?php
namespace App\Http\Providers\Transactions\Mainnet;

use \GuzzleHttp\Client;
use App\Http\Providers\Transactions\BaseProvider;
use App\Http\Providers\Transactions\ProviderInterface;

class Consumer extends BaseProvider implements ProviderInterface 
{
    const SUPPORTED_NETWORK_ID = 1;
    const PROVIDER_NAME = "Etherscan";
    
    private $userAddress;
    private $formattedResponse;

    public function getSupportedNetworkId() 
    {
        return self::SUPPORTED_NETWORK_ID;
    }
    
    public function getProviderName() 
    {
        return self::PROVIDER_NAME;
    }

    public function getDataFromApi(string $userAddress): Consumer
    {
        $this->userAddress = $userAddress;

        $client = new Client([
            'base_uri' => 'https://api.etherscan.io/api',
            'headers' => [
            ],
        ]);

        try {
            $res = $client->request(
                'GET', 
                "?module=account&action=txlist&address=". $this->userAddress ."&startblock=0&endblock=99999999&page=1&offset=0&sort=asc&apikey=". env("PROVIDER_TX_MAINNET_API_KEY", "NO_API_KEY")
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
         *      address: $userAddress,
         *      totalTx: int
         *      provider: <provider_name>
         *      transactions: [{
         *          hash: <string>,
         *          to: <string>,
         *          from: <string>,
         *          isError: <number>,
         *          input: <string>,
         *          ....
         *      }]
         * }
         */

        $output = [
            "address" => $this->userAddress,
            "provider" => $this->getProviderName(),
            "totalTx" => 0,
            "transactions" => []
        ];

        if(count($rawResponse["result"])) {
            $output["totalTx"] = count($rawResponse["result"]);
            $output["transactions"] = $rawResponse["result"];
        }

        $this->formattedResponse = $output;

        return $this;
    }

    public function getResponse()
    {
        return $this->formattedResponse;
    }
}