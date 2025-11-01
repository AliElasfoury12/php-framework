<?php 

namespace core;

use Exception;

class JWT_Token {

    public function CreatToken (array $payload, string $secretKey, int $validation_time): string 
    {
        $b64Header = $this->header_encode();
        $b64Payload = $this->payload_encode($payload, $validation_time);
        $b64Signature = $this->signature("$b64Header.$b64Payload", $secretKey);

        return "$b64Header.$b64Payload.$b64Signature";
    }

    public function CheckToken (string $jwt_token, string $secretKey) 
    {
        $parts = explode('.', $jwt_token);
        if(count($parts) !== 3) 
            throw new Exception('Invalid Token'); 

        $this->check_header($parts[0]);
        $this->check_signature($parts, $secretKey);
        return $this->check_payload($parts[1]);
    }

    private function check_header (string $b64Header): void 
    {
        $headerJson = $this->base64url_decode($b64Header);
        $header = json_decode($headerJson, true);
        if (!isset($header['algo']) || $header['algo'] !== 'HS256') 
            throw new Exception('Invalid Token'); 
    }

    private function check_payload (string $b64Payload) 
    {
        $payloadJson = $this->base64url_decode($b64Payload);
        $payload = json_decode($payloadJson, true);

        if (!isset($payload['expires_at'])) 
            throw new Exception('Invalid Token'); 

        if (isset($payload['expires_at']) && time() >= $payload['expires_at']) 
            throw new Exception('Token expired');

        return $payload;
    }

    private function check_signature (array $parts, string $secretKey) 
    {
        [$b64Header, $b64Payload, $b64Signature] = $parts;

        $signature = $this->base64url_decode($b64Signature);

        $expectedSig = hash_hmac('sha256', "$b64Header.$b64Payload", $secretKey, true);

        if (!hash_equals($expectedSig, $signature)) 
            throw new Exception('Invalid Token'); 
    }

    private function header_encode (): string 
    {
        $header = ['algo' => 'HS256', 'type' => 'JWT'];
        return  $this->base64url_encode(json_encode($header));
    }

    private function payload_encode (array $payload, int $validationTime): string 
    {
        $now = time();
        $payload = array_merge([
            'created_at' => $now, 'expires_at' => $now + $validationTime
        ], $payload);
        return $this->base64url_encode(json_encode($payload));
    }

    private function signature (string $data, string $secretKey) 
    {
        $signature = hash_hmac('sha256', $data, $secretKey, true);
        return $this->base64url_encode($signature);
    }

    private function base64url_encode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64url_decode(string $data): string 
    {
        $remainder = strlen($data) % 4;
        if ($remainder) $data .= str_repeat('=', 4 - $remainder);
        return base64_decode(strtr($data, '-_', '+/'));
    }
}


// // Usage example
// $secret = 'your-very-secret-key';
// $jwt = new JWT_Token();

// $token = $jwt->CreatToken(['sub' => 123, 'role' => 'admin'], $secret, 1800);
// echo "Token: $token\n";

// try {
//     $secret = 'your-very-secret-ke';
//     $payload = $jwt->CheckToken($token, $secret);
//     print_r($payload);
// } catch (Exception $e) {
//     echo "Verify failed: " . $e->getMessage();
// }