<?php

namespace App\Http\Middleware;

use Closure;
use App\Utils\NetworkMapping;

class ValidGasQueryParamsMiddleware
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
            'networkId' => $request->input('network')
        ]);

        return $next($request);
    }
}
