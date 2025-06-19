<?php

$data = '{"ref":6,"paq":1,"task":4,"expire_date":"2025-06-19 22:41:07"}';
$cipher_algo = "AES-256-CBC";
$passphrase = "7PToGGTJ71knRd86WF39wfj619qewnbZ";
$iv = "cAbBrzdLZyTUcwhx";
$options = OPENSSL_RAW_DATA;
// $ivLength = openssl_cipher_iv_length($cipher_algo);
// $iv = openssl_random_pseudo_bytes($ivLength);
$ciphertext = openssl_encrypt(
    $data, 
    $cipher_algo,
    $passphrase, 
    $options,
    $iv
);
$encrypted = base64_encode($iv . $ciphertext);
echo $encrypted;

