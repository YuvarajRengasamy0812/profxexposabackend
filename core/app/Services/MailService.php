<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class MailService
{
    protected $client;
    protected $apiKey;
    protected $senderName;
    protected $senderEmail;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.brevo.com/v3/',
            'timeout'  => 10.0,
        ]);

        $this->apiKey       = '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';
        $this->senderName   = 'PROFX Summit';
        $this->senderEmail  = 'info@profxmedia.com';
    }

    /**
     * Send email via Brevo API
     *
     * @param string $toEmail
     * @param string $subject
     * @param string $template Blade template file name, e.g., 'emails.template'
     * @param array $data Data to pass to the template
     * @return array
     */
    public function sendEmail($toEmail, $subject, $template = 'emails.template', $data = [])
    {
        $htmlContent = view($template, $data)->render();

        $payload = [
            'sender' => [
                'name'  => $this->senderName,
                'email' => $this->senderEmail,
            ],
            'to' => [
                ['email' => $toEmail],
            ],
            'subject'     => $subject,
            'htmlContent' => $htmlContent,
        ];

        try {
            $response = $this->client->post('smtp/email', [
                'headers' => [
                    'api-key'      => $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            return json_decode($response->getBody(), true);

        } catch (\Exception $e) {
            Log::error('Brevo API Error: ' . $e->getMessage());
            return [
                'error'   => true,
                'message' => 'Failed to send email: ' . $e->getMessage(),
            ];
        }
    }
}
