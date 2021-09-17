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
        $max_request_per_min = 50,
        $sleep_after_max_request = 30;

    abstract function parseResponse($response);

    abstract function processResponse($response);

    abstract function isCallable(): bool;

    public function call()
    {
        $settings = Settings::first();
        $this->token = $settings->access_token;
        $this->refresh_token = $settings->refresh_token;

        if ($this->isCallable()) return $this->getResponse();

        return false;
    }

    private function getResponse(): array
    {
        $output = [];
        $has_page = true;
        $index = 0;
        $method = $this->method;
        $call_count = 0;

        do {
            try {
                if ($call_count >= $this->max_request_per_min) {
                    Logger::verbose("Sleeping for ". $this->sleep_after_max_request . " sec after ". $this->max_request_per_min . " calls");
                    sleep($this->sleep_after_max_request);
                    continue;
                }

                $response = Http::withToken($this->token)->$method($this->API, $this->params + [
                        'index' => $index,
                        'range' => 200
                    ]);
                $call_count += 1;

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
                            $output = array_merge($output, $parsed_response);
                            $index += 200;
                            continue;
                        } else {
                            Logger::verbose("Skipping Response (empty) for ". $this->API. " on index: $index");
                            continue;
                        }
                    }
                }
                if ($response->status() == 401 && str_contains($json_response['error']['message'], "Invalid OAuth access token")) {
                    Logger::verbose("API token expired. Creating new API Token");

                    $token = TokenSyncer::getAccessToken($this->refresh_token);
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

        return $output;
    }
}
