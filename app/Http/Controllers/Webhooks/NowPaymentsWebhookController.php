<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\NowPaymentsService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NowPaymentsWebhookController extends Controller
{
    public function __invoke(Request $request, NowPaymentsService $nowPayments): Response
    {
        $nowPayments->handleIpn(
            $request->getContent(),
            $request->header('x-nowpayments-sig'),
        );

        return response()->noContent();
    }
}
