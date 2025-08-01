<?php

namespace Astrapay;

class AstraMpesa {
    private $consumerKey;
    private $consumerSecret;
    private $shortcode;
    private $passkey;
    private $callbackUrl;
    private $accessToken;

    public function __construct(array $config) {
        $this->consumerKey = $config['consumerKey'];
        $this->consumerSecret = $config['consumerSecret'];
        $this->shortcode = $config['shortcode'];
        $this->passkey = $config['passkey'];
        $this->callbackUrl = $config['callbackUrl'];
    }

    private function authenticate() {
        $credentials = base64_encode("{$this->consumerKey}:{$this->consumerSecret}");
        $response = $this->httpRequest("https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials", [], [
            "Authorization: Basic {$credentials}"
        ]);
        $this->accessToken = $response['access_token'] ?? null;
    }

    public function pay($phone, $amount) {
        if (!$this->accessToken) $this->authenticate();

        $timestamp = date("YmdHis");
        $password = base64_encode("{$this->shortcode}{$this->passkey}{$timestamp}");

        $payload = [
            "BusinessShortCode" => $this->shortcode,
            "Password" => $password,
            "Timestamp" => $timestamp,
            "TransactionType" => "CustomerPayBillOnline",
            "Amount" => $amount,
            "PartyA" => $phone,
            "PartyB" => $this->shortcode,
            "PhoneNumber" => $phone,
            "CallBackURL" => $this->callbackUrl,
            "AccountReference" => "AstraPay",
            "TransactionDesc" => "AstraPay STK Push"
        ];

        return $this->httpRequest("https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest", $payload, [
            "Authorization: Bearer {$this->accessToken}",
            "Content-Type: application/json"
        ]);
    }

    private function httpRequest($url, $data = [], $headers = []) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return ['error' => curl_error($ch)];
        }

        curl_close($ch);
        return json_decode($response, true);
    }
}
