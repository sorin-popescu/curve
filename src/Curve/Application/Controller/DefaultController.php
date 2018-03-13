<?php

namespace Curve\Application\Controller;

use Curve\Domain\Service\PrepaidCardService;
use Slim\Http\Request;
use Slim\Http\Response;

class DefaultController
{
    private $service;

    public function __construct(PrepaidCardService $service)
    {
        $this->service = $service;
    }

    public function emit(Request $request, Response $response, array $args)
    {
        $prepaidCard = $this->service->emitCard('GBP');

        return $response->withJson(["card" => $prepaidCard->getNumber()], 201);
    }

    public function lock(Request $request, Response $response, array $args)
    {
        $cardNumber = $request->getParam('card_number');

        try {
            $this->service->lockCard($cardNumber);

            return $response->withJson(['card_number' => $cardNumber, 'status' => 'locked'], 200);
        } catch (\Exception $exception) {
            return $response->withJson(
                [
                    'card_number' => $cardNumber,
                    'error' => $exception->getMessage()
                ],
                500
            );
        }
    }

    public function unlock(Request $request, Response $response, array $args)
    {
        $cardNumber = $request->getParam('card_number');

        try {
            $this->service->unlockCard($cardNumber);

            return $response->withJson(['card_number' => $cardNumber, 'status' => 'unlocked'], 200);
        } catch (\Exception $exception) {
            return $response->withJson(
                [
                    'card_number' => $cardNumber,
                    'error' => $exception->getMessage()
                ],
                500
            );
        }
    }

    public function deposit(Request $request, Response $response, array $args)
    {
        $cardNumber = (int) $request->getParam('card_number');
        $amount = (int) $request->getParam('amount');

        try {
            $this->service->makeDeposit($cardNumber, $amount);

            return $response->withJson(['card_number' => $cardNumber, 'amount' => $amount], 200);
        } catch (\Exception $exception) {
            return $response->withJson(
                [
                    'card_number' => $cardNumber,
                    'error' => $exception->getMessage()
                ],
                500
            );
        }
    }

    public function displayBalance(Request $request, Response $response, array $args)
    {
        $cardNumber = $args['card_number'];

        try {
            $card = $this->service->displayBalance($cardNumber);

            return $response->withJson([
                'card_number' => $cardNumber,
                'balance' => $card->balance(),
                'available_balance' => $card->availableBalance()
            ], 200);
        } catch (\Exception $exception) {
            return $response->withJson(
                [
                    'card_number' => $cardNumber,
                    'error' => $exception->getMessage()
                ],
                500
            );
        }
    }

    public function authorize(Request $request, Response $response, array $args)
    {
        $cardNumber = $request->getParam('card_number');
        $merchant = $request->getParam('merchant');
        $date = $request->getParam('date');
        $amount = $request->getParam('amount');
        try {
            $transaction = $this->service->makeAuthorizationRequest($merchant, $cardNumber, $amount, $date);

            return $response->withJson(
                [
                    'card_number' => $cardNumber,
                    'merchant' => $merchant,
                    'transaction_amount' => $amount,
                    'transaction_id' => $transaction->getId(),
                ],
                200
            );
        } catch (\Exception $exception) {
            return $response->withJson(
                [
                    'card_number' => $cardNumber,
                    'merchant' => $merchant,
                    'transaction_amount' => $amount,
                    'error' => $exception->getMessage()
                ],
                500
            );
        }
    }

    public function capture(Request $request, Response $response, array $args)
    {
        $transactionId = $request->getParam('transaction_id');
        $cardNumber = $request->getParam('card_number');
        $amount = $request->getParam('amount');

        try {
            $this->service->makeCapture($cardNumber, $transactionId, $amount);

            return $response->withJson(
                [
                    'card_number' => $cardNumber,
                    'transaction_id' => $transactionId,
                    'transaction_amount' => $amount,
                ],
                200
            );
        } catch (\Exception $exception) {
            return $response->withJson(
                [
                    'card_number' => $cardNumber,
                    'transaction_id' => $transactionId,
                    'transaction_amount' => $amount,
                    'error' => $exception->getMessage()
                ],
                500
            );
        }
    }

    public function reverse(Request $request, Response $response, array $args)
    {
        $transactionId = $request->getParam('transaction_id');
        $cardNumber = $request->getParam('card_number');
        $amount = $request->getParam('amount');

        try {
            $this->service->makeReverse($cardNumber, $transactionId, $amount);

            return $response->withJson(
                [
                    'card_number' => $cardNumber,
                    'transaction_id' => $transactionId,
                    'transaction_amount' => $amount,
                ],
                200
            );
        } catch (\Exception $exception) {
            return $response->withJson(
                [
                    'card_number' => $cardNumber,
                    'transaction_id' => $transactionId,
                    'transaction_amount' => $amount,
                    'error' => $exception->getMessage()
                ],
                500
            );
        }
    }

    public function refund(Request $request, Response $response, array $args)
    {
        $transactionId = $request->getParam('transaction_id');
        $cardNumber = $request->getParam('card_number');
        $amount = $request->getParam('amount');

        try {
            $this->service->makeRefund($cardNumber, $transactionId, $amount);

            return $response->withJson(
                [
                    'card_number' => $cardNumber,
                    'transaction_id' => $transactionId,
                    'transaction_amount' => $amount,
                ],
                200
            );
        } catch (\Exception $exception) {
            return $response->withJson(
                [
                    'card_number' => $cardNumber,
                    'transaction_id' => $transactionId,
                    'transaction_amount' => $amount,
                    'error' => $exception->getMessage()
                ],
                500
            );
        }
    }
}
