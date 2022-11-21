<?php

namespace App\Http\Controllers\Getnet;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\GetnetService;
use DateTime;
use DateTimeZone;
use Getnet\API\Card;
use Getnet\API\Cofre;
use Getnet\API\Environment;
use Getnet\API\Getnet;
use Getnet\API\Token;
use Getnet\API\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class GetnetController extends Controller
{
    /**
     * CREDIT:
     *    barId
     *    type Ex.: "debit" ou "credit"
     *    brand Ex.: "Mastercard","Visa", "Amex", "Elo" ou "Hipercard"
     *    amount
     *    orderId
     *    cardNumber
     *    cardHolderName
     *    expirationMonth
     *    expirationYear
     *    securityCode
     *    softDescriptor Ex.: "MUQUIRANAS*ST*ANDRE"
     *    clientId
     *    clientFirstName
     *    clientLastName
     *    clientCpfCnpj
     *    clientDocType
     *    clientEmail
     *    clientPhone
     *    clientStreet
     *    clientNumber
     *    clientComplement
     *    clientDistrict
     *    ClientCity
     *    clientUF
     *    clientCEP
     */

    protected $genetService;
    protected $brands = [
        1 => "Mastercard",
        2 => "Visa",
        3 => "Amex",
        4 => "Elo",
        5 => "Hipercard",
    ];

    public function __construct()
    {
    }

    public function processPayment(Request $request)
    {
        $this->genetService = new GetnetService;
        $validator = $this->validateRequest($request);

        if ($validator->errors()->count() > 0) {
            return response()->json([
                "success" => false,
                "message" => "Campo(s) não validado(s)",
                "data" => $validator->errors()->getMessages()
            ], 412);
        }

        $response = $this->genetService->payment($request->all());

        if ($response["status_code"] >= 300) {
            $message = "Pagamento não processado.";

            if ($response["status_code"] >= 500) {
                $message = "Erro na operadora do cartão. Tente novamente em alguns minutos.";
            }

            $insertedId = $this->saveTransaction($request->all(), $response["response"], "error");
            $response["response"]->inserted = $insertedId;

            return response()->json([
                "success" => false,
                "message" => $message,
                "data" => $response["response"]
            ], $response["status_code"]);
        }

        $insertedId = $this->saveTransaction($request->all(), $response["response"]);
        $response["response"]->inserted = $insertedId;

        return response()->json([
            "success" => true,
            "message" => "Pagamento processado.",
            "data" => $response["response"]
        ], $response["status_code"]);
    }

    public function saveTransaction($dataSource, $result, $type = "success")
    {
        $result = (array) $result;

        if ($type != "success") {
            $resultType = (array) $result['details'][0];
            if (array_key_exists('status', $resultType)) {
                $status = $resultType['status'];
            } else {
                $status = "OTHER ERRORS";
                $resultType['error_code'] = null;
                $resultType['description'] = "others errors";
                $resultType['description_detail'] = "check log";
            }

            $received_at = null;
            $authorized_at = null;
        } else {
            $resultType = (array) $result[(string) $dataSource['type']];

            $received_at = new DateTime($result['received_at']);
            $received_at->setTimezone(new DateTimeZone(config('app.timezone', 'America/Sao_Paulo')));
            $received_at = $received_at->format("Y-m-d H:i:s");
            $authorized_at = new DateTime($resultType['authorized_at']);
            $authorized_at->setTimezone(new DateTimeZone(config('app.timezone', 'America/Sao_Paulo')));
            $authorized_at = $authorized_at->format("Y-m-d H:i:s");
        }

        $data = [
            'bar_id' => $dataSource['barId'],
            'seller_id' => config('payment.getnet.seller_id'),
            'soft_descriptor' => $dataSource['softDescriptor'],
            'client_id' => $dataSource['clientId'],
            'order_id' => $dataSource['orderId'],
            'brand' => $dataSource['brand'],
            'final_numbers' => substr($dataSource['cardNumber'], -4),
            'type' => Str::upper(substr($dataSource['type'], 0, 1)),
            'amount' => $dataSource['amount'],
            'delayed' => ($type != "success")
                ? false
                : (($dataSource['type'] == "credit")
                    ? $resultType['delayed']
                    : false),
            'payment_id' => ($type != "success")
                ?  $resultType['payment_id']
                : $result['payment_id'],
            'status' => ($type != "success")
                ? $status
                : $result['status'],
            'received_at' => $received_at,
            'authorization_code' => $resultType['authorization_code'] ?? null,
            'authorized_at' => $authorized_at,
            'reason_code' => ($type != "success")
                ? $resultType['error_code']
                : $resultType['reason_code'],
            'reason_message' => ($type != "success")
                ? ($resultType['description'] . " | " . $resultType['description_detail'])
                : $resultType['reason_message'],
            'acquirer' => ($type != "success")
                ? null
                : $resultType['acquirer'],
            'terminal_nsu' => $resultType['terminal_nsu'] ?? null,
            'acquirer_transaction_id' => $resultType['acquirer_transaction_id'] ?? null,
            'transaction_id' => ($type != "success")
                ? null
                : $resultType['transaction_id'],
            'created_at' => now(config('app.timezone', 'America/Sao_Paulo')),
        ];

        return ['id' => Payment::create($data)->id];
    }

    public function saveCard(Request $request)
    {
        Log::channel('getnet')->error("request: " . print_r($request->all(), true));

        $environment = Environment::production();
        if (config('payment.getnet.environment') != "production") {
            $environment = Environment::homolog();
        }

        $client_id = config('payment.getnet.client_id');
        $client_secret = config('payment.getnet.client_secret');
        $seller_id = config('payment.getnet.seller_id');

        //Autenticação da API
        $getnet = new Getnet($client_id, $client_secret, $environment);

        // Inicia uma transação
        $transaction = new Transaction();
        $transaction->setSellerId($this->seller_id);

        // Gera token do cartão - Obrigatório
        $tokenCard = new Token(
            $request->cardNumber,
            $request->clientId,
            $getnet
        );

        $card = new Card($tokenCard);
        $card->setBrand($request->brand)
            ->setExpirationMonth($request->expirationMonth)
            ->setExpirationYear($request->expirationYear)
            ->setCardholderName($request->cardHolderName)
            ->setSecurityCode($request->securityCode);

        // set card info
        $cofre = new Cofre();
        $cofre->setCardInfo($card)
            ->setIdentification($request->cpf)
            ->setCustomerId($request->clientId);

        // Processa a Transação
        $transaction->cofre($cofre);
        $response = $getnet->cofre($transaction);

        return $response->getResponseJSON();
    }

    public function listCardsByCustomerId(Request $request)
    {
        //Autenticação da API
        $getnet = new Getnet($this->client_id, $this->client_secret, $this->environment);

        // Processa a Transação
        $response = $getnet->getCofreByCustomerId($request->customer_id);

        // Resultado da transação - Consultar tabela abaixo
        return $response->getResponseJSON();
    }

    public function getCardByCardId(Request $request)
    {
        //Autenticação da API
        $getnet = new Getnet($this->client_id, $this->client_secret, $this->environment);

        // Processa a Transação
        $response = $getnet->getCardByCardId($request->card_id);

        // Resultado da transação - Consultar tabela abaixo
        return $response->getResponseJSON();
    }

    public function removeCardByCardId(Request $request)
    {
        //Autenticação da API
        $getnet = new Getnet($this->client_id, $this->client_secret, $this->environment);

        // Processa a Transação
        $response = $getnet->removeCardByCardId($request->card_id);

        // Resultado da transação - Consultar tabela abaixo
        return $response->getResponseJSON();
    }

    public function validateRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'type' => ['required', Rule::in(['debit', 'credit'])],
            'brand' => ['required', Rule::in(['Mastercard', 'Visa', 'Amex', 'Elo', 'Hipercard'])],
            'amount' => ['required', 'gt:0'],
            'orderId' => ['required', 'integer', 'gt:0'],
            'cardNumber' => ['required', 'string', 'size:16'],
            'cardHolderName' => ['required', 'string', 'min:3'],
            'expirationMonth' => ['required', 'integer', 'between:1,12'],
            'expirationYear' => ['required', 'integer', 'gte:' . date('y')],
            'securityCode' => ['required', 'string', 'size:3'],
            'softDescriptor' => ['required', 'string', 'min:3'],
            'clientFirstName' => ['required', 'string', 'min:3'],
            'clientLastName' => ['required', 'string', 'min:3'],
            'clientCpfCnpj' => ['required', 'string', 'min:11', 'max:14'],
            'clientDocType' => ['required', Rule::in(['CPF', 'CNPJ'])],
            'clientEmail' => ['required', 'email:rfc,dns', 'max:255'],
            'clientPhone' => ['required', 'string', 'min:12', 'max:13'], // ex.: 5511987654321
            'clientStreet' => ['required', 'string', 'min:3'],
            'clientNumber' => ['required', 'integer', 'gt:0'],
            'clientDistrict' => ['required', 'string', 'min:3'],
            'clientCity' => ['required', 'string', 'min:3'],
            'clientUF' => ['required', 'string', 'size:2'],
            'clientCEP' => ['required', 'string', 'size:8'],
        ]);
    }

    public function getBrands()
    {
        return response()->json([
            "success" => true,
            "message" => "Bandeiras Aceitas",
            "data" => $this->brands
        ], 200);
    }
}
