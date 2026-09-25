<?php

declare(strict_types=1);

// A certificate signed for this run, a listener presenting it, and the port and
// digest written on one line for the test that started this process.

$key = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']);
$request = $key instanceof OpenSSLAsymmetricKey ? openssl_csr_new(['commonName' => '127.0.0.1'], $key) : false;
$certificate = $request instanceof OpenSSLCertificateSigningRequest ? openssl_csr_sign($request, null, $key, 1) : false;

if (! $certificate instanceof OpenSSLCertificate) {
    throw new RuntimeException('no certificate could be signed');
}

$certificatePem = '';
$keyPem = '';
openssl_x509_export($certificate, $certificatePem);
openssl_pkey_export($key, $keyPem);

if (! is_string($certificatePem) || ! is_string($keyPem)) {
    throw new RuntimeException('the certificate could not be written out');
}

$pem = tempnam(sys_get_temp_dir(), 'tls-listener');

if ($pem === false) {
    throw new RuntimeException('nowhere to keep the certificate');
}

file_put_contents($pem, $certificatePem . $keyPem);

$listening = stream_socket_server(
    'tls://127.0.0.1:0',
    $errorNumber,
    $error,
    STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
    stream_context_create(['ssl' => ['local_cert' => $pem, 'verify_peer' => false]]),
);

if ($listening === false) {
    throw new RuntimeException('nothing could listen');
}

$name = (string) stream_socket_get_name($listening, false);

fwrite(STDOUT, substr($name, (int) strrpos($name, ':') + 1) . ' ' . openssl_x509_fingerprint($certificate, 'sha256') . "\n");

set_error_handler(static fn(): bool => true);

// Until the test that started it ends it.
for (;;) {
    $accepted = stream_socket_accept($listening, 60);

    if ($accepted !== false) {
        fclose($accepted);
    }
}
