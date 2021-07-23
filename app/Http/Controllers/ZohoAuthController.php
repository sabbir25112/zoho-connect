<?php namespace App\Http\Controllers;

use App\Http\Constants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class ZohoAuthController extends Controller
{
    private $client_id, $client_secret, $redirect_uri;

    public function __construct()
    {
        $this->client_id = env('ZOHO_CLIENT_ID');
        $this->client_secret = env('ZOHO_CLIENT_SECRET');
        $this->redirect_uri = env('ZOHO_REDIRECT_URI');
    }

    public function init()
    {
        $auth_url       = config('zoho.authentication.url');
        $scopes         = implode(',', config('zoho.authentication.auth_scopes'));
        $client_id      = $this->client_id;
        $client_secret  = $this->client_secret;
        $redirect_uri   = $this->redirect_uri;

        $prepared_auth_url = $auth_url . "?scope=$scopes&client_id=$client_id&client_secret=$client_secret&response_type=code&access_type=offline&redirect_uri=$redirect_uri&prompt=consent";
        return Redirect::to($prepared_auth_url);
    }

    public function callback(Request $request)
    {
        $code = $request->has('code') ? $request->get('code') : '';
        $account_server = $request->has('accounts-server') ? $request->get('accounts-server') : 'https://accounts.zoho.com';

        $token_uri = "$account_server/oauth/v2/token";

        $client_id = $this->client_id;
        $client_secret = $this->client_secret;
        $redirect_uri = $this->redirect_uri;

        $prepared_uri = $token_uri . "?code=$code&redirect_uri=$redirect_uri&client_id=$client_id&client_secret=$client_secret&grant_type=authorization_code";
        $response = Http::post($prepared_uri);
        $json_response = $response->json();

        if (!isset($json_response['access_token'])) {
            Log::error("ZOHO AUTH ERROR", $json_response);
        } else {
            $this->storeAuthInfoIntoCache($json_response);
        }

        return redirect()->route('welcome');
    }

    private function storeAuthInfoIntoCache($response)
    {
        $expires_in = $response['expires_in'] - 10;

        Cache::put(Constants::ZOHO_ACCESS_KEY_CACHE, $response['access_token'], $expires_in);
        Cache::put(Constants::ZOHO_REFRESH_KEY_CACHE, $response['refresh_token'], $expires_in);

        // this api domain doesn't work, that's why base api is in config
        // Cache::put('zoho_auth_api_domain', $response['api_domain'], $expires_in);
    }
}
