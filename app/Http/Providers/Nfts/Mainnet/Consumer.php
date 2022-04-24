<?php
namespace App\Http\Providers\Nfts\Mainnet;

use \GuzzleHttp\Client;
use App\Http\Providers\Nfts\BaseProvider;
use App\Http\Providers\Nfts\ProviderInterface;

class Consumer extends BaseProvider implements ProviderInterface 
{
    const SUPPORTED_NETWORK_ID = 1;
    const PROVIDER_NAME = "OpenSea";
    
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

    public function getDataFromApi(string $userAddress, int $offset, int $limit): Consumer
    {
        $this->userAddress = $userAddress;

        $client = new Client([
            'base_uri' => 'https://api.opensea.io/api/v1/assets/',
            'headers' => [
                "X-API-KEY" => getenv("PROVIDER_NFT_MAINNET_API_KEY", "")
            ],
        ]);

        try {
            $res = $client->request(
                'GET', 
                "?owner={$userAddress}&order_direction=desc&offset={$offset}&limit={$limit}"
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
         *      totalAssets: null | int
         *      provider: <provider_name>
         *      collections: [{
         *          contractAddress: <address>,
         *          collectionName: <name>,
         *          assets: [{
         *              id: <id>,
         *              name: <asset_name>
         *              image: <img_hotlink.jpg>
         *              owner: <owner>
         *              traits: [{}]
         *          }]
         *      }]
         * }
         */

        $output = [
            "address" => $this->userAddress,
            "provider" => $this->getProviderName(),
            "totalAssets" => null, // OS does not provide this with this endpoint
            "collections" => []
        ];

        // Build a small array of collection metadata
        foreach($rawResponse["assets"] as $res) {
            $collectionAddress = $res["asset_contract"]["address"];

            if(in_array($collectionAddress, array_column($output["collections"], "contractAddress"))) {
                continue;
            }

            $output["collections"][] = [
                "contractAddress" => $collectionAddress,
                "collectionName" => $res["asset_contract"]["name"],
                "assets" => []
            ];
        }

        foreach($output["collections"] as &$col) {

            $assets = array_filter($rawResponse["assets"], function($v, $k) use($col) {
                return $v["asset_contract"]["address"] === $col["contractAddress"];
            }, ARRAY_FILTER_USE_BOTH);

            foreach($assets as $asset) {
                // Get all the entries under this collection from the response
                $col["assets"][] = [
                    "id" => $asset["token_id"],
                    "name" => $asset["name"],
                    "image" => $asset["image_url"],
                    "owner" => $asset["owner"]["address"],
                    "traits" => $asset["traits"]
                ];
            }
        }

        $this->formattedResponse = $output;

        return $this;
    }

    public function getResponse()
    {
        return $this->formattedResponse;
    }
}