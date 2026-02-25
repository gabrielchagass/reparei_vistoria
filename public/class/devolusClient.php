<?php

class DevolusClient
{
    private string $baseUrl;
    private ?string $token = null;
    private int $timeout = 20; // segundos

    public function __construct(string $baseUrl = 'https://api.devolusvistoria.com.br')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Faz login e guarda o Bearer para as próximas requisições.
     * @throws RuntimeException em caso de erro
     */
    public function login(string $email, string $senha): string
    {
        $url = $this->baseUrl . '/auth/login/';
        $payload = [
            'email' => $email,
            'senha' => $senha,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_HTTPHEADER     => [
                'accept: application/json, text/plain, */*',
                'content-type: application/json',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        $body   = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $err    = curl_error($ch);
        curl_close($ch);

        if ($err) {
            throw new RuntimeException("Erro cURL: $err");
        }
        if ($status < 200 || $status >= 300) {
            throw new RuntimeException("Falha no login. HTTP $status. Resposta: " . substr($body, 0, 500));
        }

        // aqui o corpo já é o token em texto puro
        $this->token = trim($body);

        return $this->token;
    }

    /**
     * Compartilha uma vistoria por WhatsApp.
     * @param int|string $vistoriaId
     * @param string $conteudo Ex.: "VISTORIA_COMPLETA"
     * @param bool $permitirContestacao
     * @throws RuntimeException em caso de erro
     */
    public function compartilharVistoriaPorWhatsapp(
        $vistoriaId,
        string $conteudo = 'VISTORIA_COMPLETA',
        bool $permitirContestacao = true
    ): array {
        $this->ensureLoggedIn();

        $url = $this->baseUrl . '/vistorias/' . rawurlencode((string)$vistoriaId) . '/whatsapp/';
        $payload = [
            'conteudo'            => $conteudo,
            'permitirContestacao' => $permitirContestacao,
        ];

        $headers = [
            'accept: application/json, text/plain, */*',
            'content-type: application/json',
            'authorization: Bearer ' . $this->token,
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',
        ];

        try {
            return $this->request('POST', $url, $payload, $headers);
        } catch (RuntimeException $e) {
            // se 401/expirado, tenta 1 relogin automático (se tiver credenciais salvas externamente você pode reaplicar)
            // aqui deixo simples: apenas repasso o erro
            throw $e;
        }
    }

    /**
     * Retorna o token atual (caso queira usar fora da classe)
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Define manualmente um token (se você já tiver um Bearer válido salvo)
     */
    public function setToken(string $token): void
    {
        $this->token = trim($token);
    }

    /**
     * Método utilitário de requisição via cURL.
     * @throws RuntimeException em caso de HTTP != 2xx ou erro de rede/JSON inválido
     */
    private function request(string $method, string $url, ?array $json = null, array $headers = []): array
    {
        $ch = curl_init();

        $opts = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
            CURLOPT_HTTPHEADER     => $headers,
        ];

        if ($json !== null) {
            $opts[CURLOPT_POSTFIELDS] = json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        curl_setopt_array($ch, $opts);

        $body = curl_exec($ch);
        $curlErrNo = curl_errno($ch);
        $curlErr   = curl_error($ch);
        $status    = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        curl_close($ch);

        if ($curlErrNo) {
            throw new RuntimeException("Erro de rede cURL ($curlErrNo): $curlErr");
        }

        // tenta decodificar JSON; se vazio, retorna array vazio
        $decoded = [];
        if ($body !== false && $body !== '' && $body !== null) {
            $decoded = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException("Resposta não é JSON válido: " . json_last_error_msg() . " | Corpo: " . substr($body, 0, 500));
            }
        }

        if ($status < 200 || $status >= 300) {
            $msg = $decoded['detail'] ?? $decoded['message'] ?? $decoded['error'] ?? 'Erro HTTP';
            throw new RuntimeException("HTTP $status: $msg");
        }

        return is_array($decoded) ? $decoded : [];
    }

    private function ensureLoggedIn(): void
    {
        if (!$this->token) {
            throw new RuntimeException('É necessário fazer login antes (token ausente).');
        }
    }
}
