<?php

declare(strict_types=1);

// A peer that reads each request's head, writes it down, and answers every one
// with the same answer. Given the status 0 it holds the connection open and never
// answers; given the status 1 it hangs up without answering. Started as:
//
//   answering-listener.php <tcp|tls> <host> <heard-file> <status> <location or -> <body>
//
// It writes the port it listens on, and the digest of the certificate it
// presents or "-", on one line for the test that started it.

$given = $_SERVER['argv'] ?? null;

if (! is_array($given) || count($given) !== 7) {
    throw new RuntimeException('started without the six things it answers with');
}

[, $mode, $host, $heard, $status, $location, $body] = array_map(static fn(mixed $one): string => is_string($one) ? $one : '', $given);

$context = [];
$digest = '-';

if ($mode === 'tls') {
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

    $pem = tempnam(sys_get_temp_dir(), 'answering-listener');

    if ($pem === false || ! is_string($certificatePem) || ! is_string($keyPem)) {
        throw new RuntimeException('nowhere to keep the certificate');
    }

    file_put_contents($pem, $certificatePem . $keyPem);
    $context = ['ssl' => ['local_cert' => $pem, 'verify_peer' => false]];
    $digest = (string) openssl_x509_fingerprint($certificate, 'sha256');
}

$listening = stream_socket_server(
    sprintf('%s://%s:0', $mode, $host),
    $errorNumber,
    $error,
    STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
    stream_context_create($context),
);

if ($listening === false) {
    throw new RuntimeException('nothing could listen');
}

$name = (string) stream_socket_get_name($listening, false);

fwrite(STDOUT, substr($name, (int) strrpos($name, ':') + 1) . ' ' . $digest . "\n");

set_error_handler(static fn(): bool => true);

$answer = sprintf("HTTP/1.1 %s Answered\r\n", $status)
    . ($location === '-' ? '' : sprintf("Location: %s\r\n", $location))
    . "Content-Type: application/json\r\n"
    . sprintf("Content-Length: %d\r\n", strlen($body))
    . "Connection: close\r\n\r\n"
    . $body;

// Connections it heard and will not answer, held open until the test ends it.
$held = [];

// Until the test that started it ends it.
for (;;) {
    $accepted = stream_socket_accept($listening, 60);

    if ($accepted === false) {
        continue;
    }

    stream_set_timeout($accepted, 5);
    $head = '';

    while (($line = fgets($accepted)) !== false && $line !== "\r\n") {
        $head .= $line;
    }

    if ($head !== '') {
        file_put_contents($heard, $head . "\n", FILE_APPEND);
    }

    if ($status === '0') {
        $held[] = $accepted;

        continue;
    }

    if ($status === '1') {
        fclose($accepted);

        continue;
    }

    if ($head !== '') {
        fwrite($accepted, $answer);
    }

    fclose($accepted);
}
