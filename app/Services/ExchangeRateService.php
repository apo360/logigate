<?php

namespace App\Services;

use App\Models\Cambio;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    protected $client;
    protected $appId;

    public function __construct()
    {
        $this->client = new Client();
        $this->appId = 'c7fb26049f164fec9a76651964087f66';
    }

    public function getExchangeRates()
    {
        $cacheKey = 'exchange_rates';
        $cachedData = Cache::get($cacheKey);

        if ($cachedData) {
            return $cachedData;
        }

        try {
            $response = $this->client->get("https://openexchangerates.org/api/latest.json?app_id={$this->appId}");
            $data = json_decode($response->getBody(), true);

            if (isset($data['rates'])) {
                Cache::put($cacheKey, $data, now()->addDay());
                $this->saveExchangeRatesToDatabase($data);
                return $data;
            } else {
                throw new \Exception('Invalid response from exchange rate API.');
            }
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error fetching exchange rates: ' . $e->getMessage());

            if ($cachedData) {
                return $cachedData;
            }

            throw new \Exception('Unable to retrieve exchange rates at this time.');
        }
    }

    private function saveExchangeRatesToDatabase($data)
    {
        try {
            Cambio::create([
                'dataactual' => now(),
                'GBP' => 1*($data['rates']['AOA']/$data['rates']['GBP']) ?? null,
                'EUR' => 1*($data['rates']['AOA']/$data['rates']['EUR']) ?? null,
                'USD' => $data['rates']['AOA'] ?? null,
                'ZAR' => 1*($data['rates']['AOA']/$data['rates']['ZAR']) ?? null,
                'CHF' => 1*($data['rates']['AOA']/$data['rates']['CHF']) ?? null,
                'CAD' => 1*($data['rates']['AOA']/$data['rates']['CAD']) ?? null,
                'CNY' => 1*($data['rates']['AOA']/$data['rates']['CNY']) ?? null,
                'DKK' => 1*($data['rates']['AOA']/$data['rates']['DKK']) ?? null,
                'JPY' => 1*($data['rates']['AOA']/$data['rates']['JPY']) ?? null,
                'MZN' => 1*($data['rates']['AOA']/$data['rates']['MZN']) ?? null,
                'NAD' => 1*($data['rates']['AOA']/$data['rates']['NAD']) ?? null,
                'AED' => 1*($data['rates']['AOA']/$data['rates']['AED']) ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving exchange rates to database: ' . $e->getMessage());
            throw new \Exception('Error saving exchange rates to database.');
        }
    }

    public function convertCurrency($amount, $fromCurrency, $toCurrency)
    {
        $exchangeRates = $this->getExchangeRates();

        if (!isset($exchangeRates['rates'][$fromCurrency]) || !isset($exchangeRates['rates'][$toCurrency])) {
            throw new \Exception('Invalid source or target currency.');
        }

        $convertedAmount = $amount * ($exchangeRates['rates'][$toCurrency] / $exchangeRates['rates'][$fromCurrency]);

        return number_format($convertedAmount, 2, '.', '');
    }

    public function getAvailableCurrencies()
    {
        $exchangeRates = $this->getExchangeRates();
        return array_keys($exchangeRates['rates']);
    }

    public function getLastUpdateDate()
    {
        $exchangeRates = $this->getExchangeRates();
        return $exchangeRates['timestamp'];
    }

    public function getSpecificExchangeRate($fromCurrency, $toCurrency)
    {
        $exchangeRates = $this->getExchangeRates();

        if (!isset($exchangeRates['rates'][$fromCurrency]) || !isset($exchangeRates['rates'][$toCurrency])) {
            throw new \Exception('Invalid source or target currency.');
        }

        return $exchangeRates['rates'][$toCurrency] / $exchangeRates['rates'][$fromCurrency];
    }
}
