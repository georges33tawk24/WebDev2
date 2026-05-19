<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ExchangeRateService
{
    public function usdToLbp(float $usdAmount): ?float
    {
        $rate = $this->usdLbpRate();

        return $rate !== null ? round($usdAmount * $rate, 0) : null;
    }

    public function usdLbpRate(): ?float
    {
        return Cache::remember('exchange.usd_lbp', now()->addHours(6), function (): ?float {
            try {
                $response = Http::timeout(8)
                    ->get('https://api.frankfurter.app/latest', [
                        'from' => 'USD',
                        'to' => 'LBP',
                    ]);

                if ($response->successful()) {
                    $rate = $response->json('rates.LBP');
                    if (is_numeric($rate)) {
                        return (float) $rate;
                    }
                }
            } catch (\Throwable) {
                // fall through to configured rate
            }

            $fallback = config('services.exchange.usd_lbp_fallback');

            return is_numeric($fallback) ? (float) $fallback : null;
        });
    }

    public function formatDualPrice(float $usdAmount): string
    {
        $usd = localized_money($usdAmount);
        $lbp = $this->usdToLbp($usdAmount);

        if ($lbp === null) {
            return $usd;
        }

        return $usd.' · '.localized_digits(number_format($lbp, 0, '.', ',')).' LBP';
    }
}
