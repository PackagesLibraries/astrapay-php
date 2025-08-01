<?php

require __DIR__ . '/vendor/autoload.php';

use Astrapay\AstraMpesa;

$client = new AstraMpesa([
    'consumerKey' => 'YOUR_KEY',
    'consumerSecret' => 'YOUR_SECRET',
    'shortcode' => '174379',
    'passkey' => 'YOUR_PASSKEY',
    'callbackUrl' => 'https://yourdomain.com/callback'
]);

$response = $client->pay('254712345678', 10);
print_r($response);
