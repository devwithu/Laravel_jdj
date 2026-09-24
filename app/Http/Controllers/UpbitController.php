<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class UpbitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function profit()
    {
        try {

            $payload = [
                'access_key' => config('services.upbit.access_key'),
                'nonce' => (string) Str::uuid(),
            ];

            $jwt = JWT::encode(
                $payload,
                config('services.upbit.secret_key'),
                'HS256'
            );

            // 업비트 계좌 조회
            $accounts = Http::withHeaders([
                'Authorization' => 'Bearer ' . $jwt,
            ])->get('https://api.upbit.com/v1/accounts')
                ->json();

            $btc = collect($accounts)
                ->firstWhere('currency', 'BTC');

            if (!$btc) {
                return response()->json([
                    'success' => false,
                    'message' => 'BTC를 보유하고 있지 않습니다.'
                ]);
            }

            // 현재 BTC 시세 조회
            $ticker = Http::get(
                'https://api.upbit.com/v1/ticker',
                [
                    'markets' => 'KRW-BTC'
                ]
            )->json();

            $balance = (float) $btc['balance'];
            $avgBuyPrice = (float) $btc['avg_buy_price'];
            $currentPrice = (float) $ticker[0]['trade_price'];

            $cost = $balance * $avgBuyPrice;
            $valuation = $balance * $currentPrice;

            $profit = $valuation - $cost;

            $profitRate = $cost > 0
                ? ($profit / $cost) * 100
                : 0;

            return response()->json([
                'success' => true,
                'coin' => 'BTC',
                'balance' => $balance,
                'avg_buy_price' => round($avgBuyPrice),
                'current_price' => round($currentPrice),
                'valuation' => round($valuation),
                'profit' => round($profit),
                'profit_rate' => round($profitRate, 2),
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
