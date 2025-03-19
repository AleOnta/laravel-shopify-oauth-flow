<?php

namespace App\Http\Middleware;

use App\Exceptions\InvalidHmacSignatureException;
use App\Exceptions\InvalidRequestException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InstallHmacSignatureMiddleware
{
    /**
     * Verifies the HMac signature provided by the resource server using the application client secret
     * shared by the application and the resource server 
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $data = $request->query();
        ksort($data);
        unset($data['hmac']);
        if (count($data) === 0) {
            $message = 'This route is protected by HMac signature, but the request received doesn\'t contain any parameter.';
            throw new InvalidRequestException($message);
        }

        $trusted = hash_hmac('sha256', http_build_query($data), config('app.shopify.client_secret'));
        if (!hash_equals($trusted, $request->query('hmac'))) {
            throw new InvalidHmacSignatureException('The provided HMac signature is invalid.');
        }

        return $next($request);
    }
}
