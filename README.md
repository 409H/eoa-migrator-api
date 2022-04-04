# EOA-Migrator-API

This is the API to proxy HTTP requests from the client side to a third party with the goal of keeping API keys secret - and in the future to enable data caching, more endpoints, and more microservices to make the experience better.

## Add a network

You can add a network to the mapping table stored in `storage/network_mapping.json`. The `app/Providers/AppServiceProvider.php` will read this file on boot and store it within the Laravel helper `config()`.

## NFTs

**Route:** `GET /wallet/nfts/?address=<address>&network=<network_id>`

To add a provider for NFT data, add `Consumer.php` to one of the network directories in `app/Http/Providers/Nfts/<network_name>`. You should copy the [Rinkeby](app/Http/Providers/Nfts/Rinkeby/Consumer.php) one for responses to the caller.

The output for these files should be:

```json
{
    address: "<string>",
    totalAssets: "<null | int>"
    provider: "<provider_name>"
    collections: [{
        contractAddress: "<string>",
        collectionName: "<string>",
        assets: [{
            id: "<string>",
            name: "<string>"
            image: "<string>"
            owner: "<string>"
            traits: "<array_of_objs>"
        }]
    }]
}
```

Note: All NFT endpoint hits are validated through `CorsMiddleware.php` and `ValidNftQueryParamsMiddleware.php`.


## Responses

### Errors

```json
HTTP 400

{
    "response": "ERROR",
    "code": "ERR_CODE",
    "message": "Some reason for the error"
}
```

* `ERR_ADDRESS_INVALID` - The address passed in the request query string is not the correct format (`/^0x[a-fA-F0-9]{40}$/`)
* `ERR_NETWORK_ID_INVALID` - The network ID passed in the request query string is not the correct format (`/^\d+$/`)
* `ERR_OFFSET_INVALID` - The offset amount passed in the request query string is not the correct format (`/^\d+$/`)
* `ERR_LIMIT_INVALID` - The limit amount passed in the request query string is not the correct format (`/^\d+$/`)
* `ERR_NO_DATA_PROVIDER` - No data provider for the endpoint (ie: BSC does not have an NFT data provider)


### Successful

```json
HTTP 200

{
    "response": "OK",
    "data": {
        //
    }
}
```