<?php

namespace App\Http\Middleware;

use Closure;
use App\Utils\NetworkMapping;

class ValidTransactionsQueryParamsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Ensure they pass in a valid EVM address format
        if(!!preg_match('/^0x[a-fA-F0-9]{40}$/', $request->input('address')) === false) {
            return response()->json(
                [
                    'response' => 'ERROR',
                    'code' => "ERR_ADDRESS_INVALID",
                    'message' => "The address in the query string is not a valid EVM address."
                ], 
                400
            );
        }

        // Ensure they pass in a valid network (numeric)
        // @todo - maybe check it's valid with NetworkMapping->isSupportedNetworkId()
        if(!!preg_match('/^\d+$/', $request->input('network')) === false) {
            return response()->json(
                [
                    'response' => 'ERROR',
                    'code' => "ERR_NETWORK_ID_INVALID",
                    'message' => "The network ID is invalid."
                ], 
                400
            );
        }

        $request->attributes->add([
            'address' => $request->input('address'),
            'networkId' => $request->input('network')
        ]);

        return $next($request);
    }
}
