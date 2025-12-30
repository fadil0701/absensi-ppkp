<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get authentication credentials from header
        // Support multiple authentication methods:
        // 1. API Key method: X-API-KEY + X-SECRET-KEY
        // 2. ConsID/UserKey method: X-CONSID + X-USERKEY + X-SECRET-KEY

        $apiKey = $request->header('X-API-KEY') ?? $request->header('Api-Key');
        $consid = $request->header('X-CONSID') ?? $request->header('ConsID');
        $userkey = $request->header('X-USERKEY') ?? $request->header('UserKey');
        $secretKey = $request->header('X-SECRET-KEY') ?? $request->header('Secret-Key');

        $key = null;

        // Method 1: API Key authentication
        if ($apiKey) {
            $key = ApiKey::where('api_key', $apiKey)->first();

            if (! $key) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API Key',
                ], 401);
            }

            // Verify Secret Key if provided
            if ($secretKey && ! $key->verifySecretKey($secretKey)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Secret Key',
                ], 401);
            }
        }
        // Method 2: ConsID/UserKey authentication
        elseif ($consid && $userkey) {
            $key = ApiKey::where('consid', $consid)
                ->where('userkey', $userkey)
                ->first();

            if (! $key) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid ConsID or UserKey',
                ], 401);
            }

            // Secret Key is required for ConsID/UserKey method
            if (! $secretKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Secret Key is required',
                ], 401);
            }

            if (! $key->verifySecretKey($secretKey)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Secret Key',
                ], 401);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'API Key or ConsID/UserKey is required',
            ], 401);
        }

        // Check if API Key is valid
        if (! $key->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'API Key is inactive or expired',
            ], 401);
        }

        // Check IP whitelist
        $clientIp = $request->ip();
        if (! $key->isIpAllowed($clientIp)) {
            return response()->json([
                'success' => false,
                'message' => 'IP address not allowed',
            ], 403);
        }

        // Check rate limit (simple implementation)
        // TODO: Implement proper rate limiting with cache

        // Attach API Key to request
        $request->merge(['api_key_model' => $key]);

        // Mark as used
        $key->markAsUsed();

        return $next($request);
    }
}
