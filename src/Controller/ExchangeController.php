<?php

namespace App\Controller;

use App\Service\ExchangeRateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExchangeController extends AbstractController {
    public function __construct(
        private readonly ExchangeRateService $exchangeRateService,
    ) {
    }

    #[Route('/api/exchange/convert', name: 'app_exchange_convert', methods: ['GET', 'POST'])]
    public function convert(Request $request): Response {
        $amount = filter_var($request->query->get('amount'), FILTER_VALIDATE_FLOAT);
        $fromCurrency = strtoupper($request->query->get('from'));
        $toCurrency = strtoupper($request->query->get('to'));

        if (!$amount || !$fromCurrency || !$toCurrency) {
            return $this->json(['error' => 'Missing required parameters: amount, from, to.'], Response::HTTP_BAD_REQUEST);
        }

        if (!is_numeric($amount) || $amount <= 0) {
            return $this->json(['error' => 'Amount must be a positive number.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $convertedAmount = $this->exchangeRateService->convertCurrency((float)$amount, $fromCurrency, $toCurrency);

            return $this->json([
                'amount'          => $amount,
                'from'            => $fromCurrency,
                'to'              => $toCurrency,
                'convertedAmount' => $convertedAmount,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
