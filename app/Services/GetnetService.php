<?php

namespace App\Services;

use Getnet\API\Card;
use Getnet\API\Cofre;
use Getnet\API\Credit;
use Getnet\API\Customer;
use Getnet\API\Environment;
use Getnet\API\Getnet;
use Getnet\API\Order;
use Getnet\API\Token;
use Getnet\API\Transaction;
use Illuminate\Support\Facades\Log;

class GetnetService
{
    /**
     * $environment - ambientes: sandbox, homolog ou production
     * $client_id - Fornecido pela Getnet para cada ambiente
     * $client_secret - Fornecido pela Getnet para cada ambiente
     * $seller_id - Fornecido pela Getnet para cada ambiente
     */
    protected $environment;
    protected $client_id;
    protected $client_secret;
    protected $seller_id;

    protected $getnet;
    protected $transaction;
    protected $tokenCard;
    protected $params;

    public function __construct()
    {
        $this->environment = Environment::production();
        if (config('payment.getnet.environment') != "production") {
            $this->environment = Environment::homolog();
        }

        $this->client_id = config('payment.getnet.client_id');
        $this->client_secret = config('payment.getnet.client_secret');
        $this->seller_id = config('payment.getnet.seller_id');

        //Autenticação da API
        $this->getnet = new Getnet($this->client_id, $this->client_secret, $this->environment);

        // Inicia uma transação
        $this->transaction = new Transaction();
    }

    public function payment(array $params = [])
    {

        $this->params = $params;

        // Dados do pedido - Transação
        $this->transaction->setSellerId($this->seller_id);
        $this->transaction->setCurrency("BRL");
        $this->transaction->setAmount($params["amount"]);

        // Detalhes do Pedido
        $this->transaction->order($params["orderId"])
            ->setProductType(Order::PRODUCT_TYPE_SERVICE)
            ->setSalesTax(0);

        // Gera token do cartão - Obrigatório
        $this->tokenCard = new Token(
            $params["cardNumber"],
            $params["clientId"],
            $this->getnet
        );

        if ($params['type'] == 'credit') {
            $this->transaction->credit()
                ->setDelayed(false)
                ->setPreAuthorization(false)
                ->setNumberInstallments(1)
                ->setSaveCardData(false)
                ->setTransactionType(Credit::TRANSACTION_TYPE_FULL)
                ->setAuthenticated(false)
                // ->setDynamicMcc("1799")
                ->setSoftDescriptor($this->params["softDescriptor"])
                ->card($this->tokenCard)
                ->setBrand($this->params["brand"])
                ->setExpirationMonth($this->params["expirationMonth"])
                ->setExpirationYear($this->params["expirationYear"])
                ->setCardholderName($this->params["cardHolderName"])
                ->setSecurityCode($this->params["securityCode"]);
        } else {
            $this->transaction->debit()
                ->setAuthenticated(false)
                // ->setDynamicMcc("1799")
                ->setSoftDescriptor($this->params["softDescriptor"])
                ->card($this->tokenCard)
                ->setBrand($this->params["brand"])
                ->setExpirationMonth($this->params["expirationMonth"])
                ->setExpirationYear($this->params["expirationYear"])
                ->setCardholderName($this->params["cardHolderName"])
                ->setSecurityCode($this->params["securityCode"]);
        }

        // Dados pessoais do comprador
        $this->transaction->customer($params["clientId"])
            ->setDocumentType(Customer::DOCUMENT_TYPE_CPF)
            ->setEmail($params["clientEmail"])
            ->setFirstName($params["clientFirstName"])
            ->setLastName($params["clientLastName"])
            ->setName($params["clientFirstName"] . " " . $params["clientLastName"])
            ->setPhoneNumber($params["clientPhone"])
            ->setDocumentNumber($params["clientCpfCnpj"])
            ->billingAddress()
            ->setCity($params["clientCity"])
            ->setStreet($params["clientStreet"])
            ->setNumber($params["clientNumber"])
            ->setComplement($params["clientComplement"])
            ->setDistrict($params["clientDistrict"])
            ->setPostalCode($params["clientCEP"])
            ->setState($params["clientUF"])
            ->setCountry("Brasil");

        // Dados de entrega do pedido
        // $this->transaction->shipping()
        //     ->setFirstName("Jax")
        //     ->setEmail("customer@email.com.br")
        //     ->setName("Jax Teller")
        //     ->setPhoneNumber("5551999887766")
        //     ->setShippingAmount(0)
        //     ->address()
        //     ->setCity("Porto Alegre")
        //     ->setComplement("Sons of Anarchy")
        //     ->setCountry("Brasil")
        //     ->setDistrict("São Geraldo")
        //     ->setNumber("1000")
        //     ->setPostalCode("90230060")
        //     ->setState("RS")
        //     ->setStreet("Av. Brasil");

        //Ou pode adicionar entrega com os mesmos dados do customer
        // $this->transaction->addShippingByCustomer($this->transaction->getCustomer())->setShippingAmount(0);

        // FingerPrint - Antifraude
        // $this->transaction->device("device_id")->setIpAddress("127.0.0.1");

        // dd($this->transaction->toJSON());

        // Processa a Transação
        $response = $this->getnet->authorize($this->transaction);
        $status = $response->getStatus();
        $response = $response->getResponseJSON();

        Log::channel('getnet')->info("status code: " . $status);

        $response = json_decode($response);
        if ($status  != "APPROVED") {
            Log::channel('getnet')->error("PAYMENT => barID: {$params["barId"]} - clientId: {$params["clientId"]} - orderId: {$params["orderId"]} - Type: {$params["type"]} - Brand: {$params["brand"]} - Amount: {$params["amount"]}");
            Log::channel('getnet')->error("response: " . print_r($response->details, true));

            $response = [
                "status_code" => $response->status_code, "response" => $response
            ];
        } else {
            Log::channel('getnet')->info("PAYMENT => barID: {$params["barId"]} - clientId: {$params["clientId"]} - orderId: {$params["orderId"]} - Type: {$params["type"]} - Brand: {$params["brand"]} - Amount: {$params["amount"]}");
            Log::channel('getnet')->info("response: " . print_r($response, true));
            $response = [
                "status_code" => 200, "response" => $response
            ];
        }

        return $response;
    }

    public function saveCard(array $params = [])
    {

        $this->params = $params;

        // Gera token do cartão - Obrigatório
        $this->tokenCard = new Token(
            $params["cardNumber"],
            $params["clientId"],
            $this->getnet
        );

        $card = new Card($this->tokenCard);
        $card->setBrand($this->params["brand"])
            ->setExpirationMonth($this->params["expirationMonth"])
            ->setExpirationYear($this->params["expirationYear"])
            ->setCardholderName($this->params["cardHolderName"])
            ->setSecurityCode($this->params["securityCode"]);

        // set card info
        $cofre = new Cofre();
        $cofre->setCardInfo($card)
            ->setIdentification($this->params["clientCpfCnpj"])
            ->setCustomerId($this->params["clientId"]);

        // Processa a Transação
        $this->transaction->cofre($cofre);
        $response = $this->getnet->cofre($this->transaction);
        $status = $response->getStatus();
        $response = $response->getResponseJSON();
        $response = json_decode($response);

        Log::channel('getnet')->info("status: " . $status);
        
        if($status == 'ERROR'){
            Log::channel('getnet')->error("status: " . $response->status_code);
            return response()->json([
                "error" => true,
                "message" => "Erro ao salvar cartão na operadora",
                "data" => $response,
            ], $response->status_code);            
        }

        return response()->json([
            "error" => false,
            "message" => "Cartão salvo com sucesso",
            "data" => $response,
        ], 200);
    }
}
