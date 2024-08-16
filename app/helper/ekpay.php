<?php

use Illuminate\Support\Facades\Log;

function ekpayToken($trnx_id = 123456789, $trnx_amt = 0, $cust_info = [], $path = 'payment')
{
    $req_timestamp = date('Y-m-d H:i:s');

    $url = env('AKPAY_IPN_URL');
    $AKPAY_MER_REG_ID = env('AKPAY_MER_REG_ID');
    $AKPAY_MER_PASS_KEY = env('AKPAY_MER_PASS_KEY');

    if ($AKPAY_MER_REG_ID == 'tetulia_test') {
        $Apiurl = 'https://sandbox.ekpay.gov.bd/ekpaypg/v1';
        $whitelistip = '1.1.1.1';
    } else {
        $Apiurl = env('AKPAY_API_URL');
        $whitelistip = env('WHITE_LIST_IP');
    }

    $post = [
        'mer_info' => [
            'mer_reg_id' => $AKPAY_MER_REG_ID,
            'mer_pas_key' => $AKPAY_MER_PASS_KEY
        ],
        'req_timestamp' => "$req_timestamp GMT+6",
        'feed_uri' => [
            'c_uri' => "$url/$path/cancel",
            'f_uri' => "$url/$path/fail",
            's_uri' => "$url/$path/success"
        ],
        'cust_info' => $cust_info,
        'trns_info' => [
            'ord_det' => 'Payment for sonod',
            'ord_id' => $trnx_id,
            'trnx_amt' => $trnx_amt,
            'trnx_currency' => 'BDT',
            'trnx_id' => $trnx_id
        ],
        'ipn_info' => [
            'ipn_channel' => '3',
            'ipn_email' => 'freelancernishad123@gmail.com',
            'ipn_uri' => "$url/api/ipn"
        ],
        'mac_addr' => $whitelistip
    ];

    $post = json_encode($post);

    Log::info($post);

    $ch = curl_init("$Apiurl/merchant-api");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    $response = curl_exec($ch);
    curl_close($ch);

    $response = json_decode($response);

    $sToken = $response->secure_token;

    return "$Apiurl?sToken=$sToken&trnsID=$trnx_id";
}

