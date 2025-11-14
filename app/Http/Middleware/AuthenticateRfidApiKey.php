<?php

namespace App\Http\Middleware;

use App\Models\RfidReader;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateRfidApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        // Handle CORS preflight requests
        if ($request->isMethod('OPTIONS')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-API-Key, Accept');
        }

        $apiKey = $request->header('X-API-Key') 
            ?? $request->header('Authorization') 
            ?? $request->input('api_key');

        // Remove "Bearer " prefix if present
        if ($apiKey && str_starts_with($apiKey, 'Bearer ')) {
            $apiKey = substr($apiKey, 7);
        }

        if (!$apiKey) {
            return response()->json([
                'message' => 'API key is required.',
            ], Response::HTTP_UNAUTHORIZED)
                ->header('Access-Control-Allow-Origin', '*');
        }

        // Find reader by API key
        $reader = RfidReader::where('api_key', $apiKey)->first();

        if (!$reader) {
            return response()->json([
                'message' => 'Invalid API key.',
            ], Response::HTTP_UNAUTHORIZED)
                ->header('Access-Control-Allow-Origin', '*');
        }

        if (!$reader->enabled) {
            return response()->json([
                'message' => 'Reader is disabled.',
            ], Response::HTTP_FORBIDDEN)
                ->header('Access-Control-Allow-Origin', '*');
        }

        // Attach reader to request for controller use
        $request->attributes->set('rfid_reader', $reader);

        $response = $next($request);

        // Add CORS headers to response
        if (method_exists($response, 'header')) {
            return $response->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-API-Key, Accept');
        }

        return $response;
    }
}

