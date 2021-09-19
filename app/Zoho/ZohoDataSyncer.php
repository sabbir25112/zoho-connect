<?php

namespace App\Zoho;

use App\Logger;
use App\Models\Settings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class ZohoDataSyncer
{
    const GET_REQUEST = 'get';
    const POST_REQUEST = 'post';

    protected $API,
        $method,
        $params = [],
        $token,
        $refresh_token,
        $with_call_count,
        $with_output,
        $skip_prediction = true;

    abstract function parseResponse($response);

    abstract function processResponse($response);

    abstract function isCallable(): bool;

    public function call($with_call_count = false, $with_output = false)
    {
        $settings = Settings::first();
        $this->token = $settings->access_token;
        $this->refresh_token = $settings->refresh_token;
        $this->with_call_count = $with_call_count;
        $this->with_output = $with_output;

        if ($this->isCallable()) return $this->getResponse();

        return false;
    }

    private function getResponse(): array
    {
        $max_request_per_min = config('zoho.queue.max_request_per_min');
        $sleep_after_max_request = config('zoho.queue.sleep_after_max_request');
        $range = config('zoho.queue.range');

        $response_collection = [];
        $has_page = true;
        $index = 0;
        $method = $this->method;
        $call_count = 0;
        $output_call_count = 0;

        do {
            try {
                if ($call_count >= $max_request_per_min) {
                    $call_count = 0;

                    Logger::verbose("Sleeping for ". $sleep_after_max_request . " sec after ". $max_request_per_min . " calls");
                    sleep($sleep_after_max_request);
                    continue;
                }

                $response = Http::withToken($this->token)->$method($this->API, $this->params + [
                        'index' => $index,
                        'range' => $range,
                    ]);
                $call_count += 1;
                $output_call_count += 1;

                $json_response = $response->json();

                if ($response->successful()) {
                    if ($response->status() == 204) {
                        Logger::info("No Content Found for ". $this->API . " on index: $index");
                        break;
                    } else {
                        Logger::verbose("Response parsing for ". $this->API. " on index: $index");
                        $parsed_response = $this->parseResponse($json_response);

                        if (count($parsed_response)) {
                            Logger::verbose("Response processing for ". $this->API. " on index: $index");
                            $this->processResponse($parsed_response);

                            if ($this->with_output) $response_collection = array_merge($response_collection, $parsed_response);

                            if ($this->skip_prediction && count($parsed_response) < $range) {
                                Logger::verbose("Skipping Next Call (Predicted No Content) for ". $this->API. " on index: $index");
                                break;
                            } else {
                                $index += 200;
                                continue;
                            }
                        } else {
                            Logger::verbose("Skipping Response (empty) for ". $this->API. " on index: $index");
                            continue;
                        }
                    }
                }
                if ($response->status() == 401 && str_contains($json_response['error']['message'], "Invalid OAuth access token")) {
                    Logger::verbose("API token expired. Creating new API Token");

                    $token = $this->getAccessToken($this->refresh_token);
                    if ($token == '') {
                        Logger::error('Could not generate API token');
                        break;
                    } else {
                        Logger::verbose("New Token: $token");

                        Settings::first()->update([
                            'access_token' => $token
                        ]);
                        $this->token = $token;
                        continue;
                    }
                }
                if ($response->failed()) {
                    Logger::error("Call FAILED for ". $this->API . " on index $index");
                    Log::error(['api' => $this->API, 'response' => json_encode($response->json()), 'message' => 'FAILED']);

                    $has_page = false;
                    continue;
                }
            } catch (\Exception $exception) {
                Logger::verbose("Unhandled error occurred for ". $this->API . "on index $index");

                Log::error($exception);
                continue;
            }
        } while ($has_page);

        $process_output = [];
        if ($this->with_output) $process_output['response_collection'] = $response_collection;
        if ($this->with_call_count) $process_output['call_count'] = $output_call_count;

        return $process_output;
    }

    public function getAccessToken($refresh_token)
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

    public function disableSkipPrediction(): ZohoDataSyncer
    {
        $this->skip_prediction = false;
        return $this;
    }
}
