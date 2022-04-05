# EOA-Migrator-API

This is the API to proxy HTTP requests from the client side to a third party with the goal of keeping API keys secret - and in the future to enable data caching, more endpoints, and more microservices to make the experience better.

[![Deploy to Heroku](https://www.herokucdn.com/deploy/button.svg)](https://dashboard.heroku.com/new?template=https%3A%2F%2Fgithub.com%2F409h%2Feoa-migrator-api)

## Add a network

You can add a network to the mapping table stored in `storage/network_mapping.json`. The `app/Providers/AppServiceProvider.php` will read this file on boot and store it within the Laravel helper `config()`.

## NFTs

### **Route:** `GET /wallet/nfts/networks`

This will give a list of networks that is supported with this proxy API to return some NFT data for an account

The output for this route is:

```
{
    "response": "OK",
    "data": [
        {
            "id": 4,
            "name": "rinkeby"
        },
        {
            // ...
        }
    ]
}
```

### **Route:** `GET /wallet/nfts/?address=<address>&network=<network_id>`

This will give a list of any NFTs and the asset traits for a particular address on a particular network.

The output for this route is:

```
{
    "response": "OK",
    "data": {
        "address": "0x000..000",
        "provider": "OpenSea",
        "totalAssets": null,
        "collections": [{
			"contractAddress": "0xab9d2c623ec60a60a08a87e22adc83b91a486f2c",
			"collectionName": "Sample NFT Project",
			"assets": [{
				"id": "4",
				"name": "Sample NFT Project #4",
				"image": "https:\/\/lh3.googleusercontent.com\/fA-8q_bEXYhZuEGtX5wQC-lfcpCvQRa-hhPXv-UxSK2yDMsayVT5a4KhueSdt8GW-hsps-0nLksMM0iOtAIpSQP0m9E9agGTa0UxSNI",
				"owner": "0x661b5dc032bedb210f225df4b1aa2bdd669b38bc",
				"traits": [{
					"trait_type": "Head Color",
					"value": "black",
					"display_type": null,
					"max_value": null,
					"trait_count": 3,
					"order": null
				}
            }]
        }]
    }
}
```

To add a provider for NFT data, add `Consumer.php` to one of the network directories in `app/Http/Providers/Nfts/<network_name>`. You should copy the [Rinkeby](app/Http/Providers/Nfts/Rinkeby/Consumer.php) one for responses to the caller.

The output should be:

```
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

## Transactions

### **Route:** `GET /wallet/transactions/networks`

This will give a list of networks that is supported with this proxy API to return some transaction data for an account

The output for this route is:

```
{
    "response": "OK",
    "data": [
        {
            "id": 4,
            "name": "rinkeby"
        },
        {
            // ...
        }
    ]
}
```


### **Route:** `GET /wallet/transactions/?address=<address>&network=<network_id>`

This will give a list of transactions for a particular address on a particular network.

The output for this route is:

```
{
    "response": "OK",
    "data": {
        "address": "0x000..000",
        "provider": "Etherscan",
        "totalTx": 94,
        "transactions": [{
			"blockNumber": "3915061",
			"timeStamp": "1498170361",
			"hash": "0xda604c689601d222dd139d448c02b10ad67634fc7338abad38d6ff6ca3512362",
			"nonce": "0",
			"blockHash": "0x7ef1b519c0a0f8fc3e772995a864c50a00b426b5628e619ffb0db1f6314872f2",
			"transactionIndex": "82",
			"from": "0x78f069e3eedd5428a068cec12453fe719366102e",
			"to": "0x661b5dc032bedb210f225df4b1aa2bdd669b38bc",
			"value": "124799840000000000",
			"gas": "90000",
			"gasPrice": "30411003716",
			"isError": "0",
			"txreceipt_status": "",
			"input": "0x",
			"contractAddress": "",
			"cumulativeGasUsed": "2880348",
			"gasUsed": "21000",
			"confirmations": "10606886"
		}, {
            // ...
        }]
    }
}
```

## Gas

### **Route:** `GET /gas/networks`

This will give a list of networks that is supported with this proxy API to return some gas data for a network

The output for this route is:

```
{
    "response": "OK",
    "data": [
        {
            "id": 4,
            "name": "rinkeby"
        },
        {
            // ...
        }
    ]
}
```


### **Route:** `GET /gas?network=<network_id>`

This will give the current gas estimates for a particular network

The output for this route is:

```
{
    "response": "OK",
    "data": {
        "provider": "Etherscan",
        "gas": {
			"safe": "71",
			"fast": "71",
			"base_fee": "70.220708583"
        }
    }
}
```


## Response Structures

### Errors

```
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

```
HTTP 200

{
    "response": "OK",
    "data": {
        //
    }
}
```