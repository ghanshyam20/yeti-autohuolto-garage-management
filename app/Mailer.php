<?php

declare(strict_types=1);

namespace Yeti;

interface Mailer
{
    public function send(string $to, string $subject, string $body, ?string $replyTo = null): void;
}

final class LogMailer implements Mailer
{
    public function __construct(private readonly string $path)
    {
    }

    public function send(string $to, string $subject, string $body, ?string $replyTo = null): void
    {
        $directory = dirname($this->path);
        if (!is_dir($directory) && !mkdir($directory, 0770, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to create the mail log directory.');
        }

        $entry = sprintf(
            "[%s]\nTo: %s\nReply-To: %s\nSubject: %s\n\n%s\n\n%s\n",
            now(),
            $to,
            $replyTo ?? '-',
            $subject,
            $body,
            str_repeat('-', 72)
        );

        if (file_put_contents($this->path, $entry, FILE_APPEND | LOCK_EX) === false) {
            throw new \RuntimeException('Unable to write the mail log.');
        }
    }
}

final class SmtpMailer implements Mailer
{
    /** @param array<string, mixed> $config */
    public function __construct(private readonly array $config)
    {
    }

    public function send(string $to, string $subject, string $body, ?string $replyTo = null): void
    {
        $this->assertEmail($to);
        if ($replyTo !== null) {
            $this->assertEmail($replyTo);
        }

        $host = (string) $this->config['host'];
        $port = (int) $this->config['port'];
        $encryption = strtolower((string) ($this->config['encryption'] ?? 'tls'));
        $transport = $encryption === 'ssl' ? 'ssl://' : '';
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false,
            ],
        ]);

        $socket = @stream_socket_client(
            $transport . $host . ':' . $port,
            $errorNumber,
            $errorMessage,
            20,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if ($socket === false) {
            throw new \RuntimeException("SMTP connection failed: {$errorMessage} ({$errorNumber}).");
        }

        stream_set_timeout($socket, 20);

        try {
            $this->expect($socket, [220]);
            $this->command($socket, 'EHLO yetiautohuolto.fi', [250]);

            if ($encryption === 'tls') {
                $this->command($socket, 'STARTTLS', [220]);
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new \RuntimeException('Unable to establish SMTP TLS encryption.');
                }
                $this->command($socket, 'EHLO yetiautohuolto.fi', [250]);
            }

            $username = (string) ($this->config['username'] ?? '');
            $password = (string) ($this->config['password'] ?? '');
            if ($username !== '') {
                $this->command($socket, 'AUTH LOGIN', [334]);
                $this->command($socket, base64_encode($username), [334]);
                $this->command($socket, base64_encode($password), [235]);
            }

            $from = (string) $this->config['from_address'];
            $this->assertEmail($from);
            $this->command($socket, "MAIL FROM:<{$from}>", [250]);
            $this->command($socket, "RCPT TO:<{$to}>", [250, 251]);
            $this->command($socket, 'DATA', [354]);

            $message = $this->buildMessage($to, $subject, $body, $replyTo);
            fwrite($socket, $message . "\r\n.\r\n");
            $this->expect($socket, [250]);
            $this->command($socket, 'QUIT', [221]);
        } finally {
            fclose($socket);
        }
    }

    private function buildMessage(string $to, string $subject, string $body, ?string $replyTo): string
    {
        $from = (string) $this->config['from_address'];
        $fromName = $this->cleanHeader((string) ($this->config['from_name'] ?? 'Yeti Autohuolto'));
        $subject = $this->cleanHeader($subject);
        $normalizedBody = preg_replace("~\r\n?|\n~", "\r\n", $body) ?? $body;
        $normalizedBody = preg_replace('/^\./m', '..', $normalizedBody) ?? $normalizedBody;

        $headers = [
            'Date: ' . date(DATE_RFC2822),
            'Message-ID: <' . bin2hex(random_bytes(16)) . '@yetiautohuolto.fi>',
            "From: {$fromName} <{$from}>",
            "To: <{$to}>",
            'Subject: ' . $subject,
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ];

        if ($replyTo !== null) {
            $headers[] = "Reply-To: <{$replyTo}>";
        }

        return implode("\r\n", $headers) . "\r\n\r\n" . $normalizedBody;
    }

    /** @param resource $socket @param list<int> $codes */
    private function command($socket, string $command, array $codes): void
    {
        if (fwrite($socket, $command . "\r\n") === false) {
            throw new \RuntimeException('Unable to write to the SMTP server.');
        }
        $this->expect($socket, $codes);
    }

    /** @param resource $socket @param list<int> $codes */
    private function expect($socket, array $codes): void
    {
        $response = '';
        do {
            $line = fgets($socket, 515);
            if ($line === false) {
                throw new \RuntimeException('The SMTP server closed the connection unexpectedly.');
            }
            $response .= $line;
        } while (strlen($line) >= 4 && $line[3] === '-');

        $code = (int) substr($response, 0, 3);
        if (!in_array($code, $codes, true)) {
            throw new \RuntimeException('SMTP error ' . $code . ': ' . trim($response));
        }
    }

    private function assertEmail(string $email): void
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || preg_match('/[\r\n]/', $email)) {
            throw new \InvalidArgumentException('Invalid email address.');
        }
    }

    private function cleanHeader(string $value): string
    {
        return trim(str_replace(["\r", "\n"], ' ', $value));
    }
}
