<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * SECURITY PATCH H-08: Safe API Logging
 *
 * Logs API requests and responses while sanitizing sensitive data
 * to prevent credential leakage in log files.
 */
class APILog
{
    /**
     * Fields that should never be logged
     */
    private function getSensitiveFields()
    {
        return [
            'password', 'password_confirmation', 'current_password', 'new_password',
            'card_number', 'cvv', 'cvc', 'expiry_date', 'card_holder',
            'credit_card', 'debit_card', 'bank_account', 'routing_number',
            'ssn', 'social_security', 'passport', 'id_number',
            'token', 'access_token', 'refresh_token', 'secret', 'api_key',
            'authorization', 'bearer',
        ];
    }

    /**
     * Sanitize request data by removing sensitive fields
     */
    private function sanitizeRequest($request)
    {
        $sanitized = [];

        // URL and method are safe
        $sanitized['url'] = $request->url();
        $sanitized['method'] = $request->method();
        $sanitized['ip'] = $request->ip();

        // Sanitize headers - remove authorization
        $headers = $request->headers->all();
        foreach ($headers as $key => $values) {
            if (in_array(strtolower($key), ['authorization', 'cookie', 'php-auth-user', 'php-auth-pw'])) {
                $sanitized['headers'][$key] = '[REDACTED]';
            } else {
                $sanitized['headers'][$key] = $values;
            }
        }

        // Sanitize body data
        $all = $request->except($this->getSensitiveFields());
        $sanitized['body'] = $all;

        return $sanitized;
    }

    /**
     * Sanitize response data
     */
    private function sanitizeResponse($response)
    {
        $content = $response->getContent();

        // Try to decode as JSON to sanitize specific fields
        $decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $this->removeSensitiveFromNested($decoded);
            return json_encode($decoded);
        }

        // For non-JSON, just log status and size
        return sprintf('[Response: %d, Size: %d bytes]', $response->getStatusCode(), strlen($content));
    }

    /**
     * Recursively remove sensitive fields from nested arrays
     */
    private function removeSensitiveFromNested(&$data)
    {
        $sensitiveFields = array_merge($this->getSensitiveFields(), [
            'token', 'plain_text_token', 'abilities',
        ]);

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $this->removeSensitiveFromNested($value);
            }
        }
    }

    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {
        $sanitizedRequest = $this->sanitizeRequest($request);
        $sanitizedResponse = $this->sanitizeResponse($response);

        Log::channel('API_log')->info(' *********************************** API START *********************************** ');
        Log::channel('API_log')->info('URL: ' . $sanitizedRequest['url']);
        Log::channel('API_log')->info('Method: ' . $sanitizedRequest['method']);
        Log::channel('API_log')->info('IP: ' . ($sanitizedRequest['ip'] ?? 'unknown'));
        Log::channel('API_log')->info('Request Body', $sanitizedRequest['body'] ?? []);
        Log::channel('API_log')->info(PHP_EOL);
        Log::channel('API_log')->info('Response: ' . $sanitizedResponse);
        Log::channel('API_log')->info(' *********************************** API END *********************************** ');
    }
}
