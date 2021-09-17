<?php

namespace App\Zoho;

use Illuminate\Support\Facades\Http;

class TokenSyncer
{
    public static function getAccessToken($refresh_token)
    {
        $API = config('zoho.authentication.refresh_token');
        $client_id = env('ZOHO_CLIENT_ID');
        $client_secret = env('ZOHO_CLIENT_SECRET');
        $redirect_uri = env('ZOHO_REDIRECT_URI');

        $response = Http::asForm()->post($API, [
            'refresh_token' => $refresh_token,
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'grant_type'    => 'refresh_token',
            'redirect_uri'  => $redirect_uri
        ]);

        $json_response = $response->json();
        if ($response->successful()) {
            return $json_response['access_token'] ?? '';
        }
        return '';
    }
}
