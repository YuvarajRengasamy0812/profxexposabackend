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
            'timeout' => 20.0,
        ]);

        $this->apiKey = config('services.brevo.api_key');
        $this->senderName = config('services.brevo.sender_name') ?: 'PROFX EXPO';
        $this->senderEmail = config('services.brevo.sender_email') ?: 'info@profxmedia.com';
    }

    public function sendEmail($toEmail, $subject, $template = 'emails.template', $data = [])
    {
        $email = strtolower(trim((string) $toEmail));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'error' => true,
                'message' => 'Invalid recipient email',
            ];
        }

        if (empty($this->apiKey)) {
            Log::error('Brevo API key missing', [
                'email' => $email,
                'subject' => $subject,
            ]);

            return [
                'error' => true,
                'message' => 'Brevo API key is missing',
            ];
        }

        $htmlContent = view($template, array_merge($data, ['title' => $subject]))->render();

        $payload = [
            'sender' => [
                'name' => $this->senderName,
                'email' => $this->senderEmail,
            ],
            'to' => [
                ['email' => $email],
            ],
            'replyTo' => [
                'name' => $this->senderName,
                'email' => $this->senderEmail,
            ],
            'subject' => $subject,
            'htmlContent' => $htmlContent,
        ];

        try {
            $response = $this->client->post('smtp/email', [
                'headers' => $this->headers(),
                'json' => $payload,
            ]);

            $body = json_decode($response->getBody(), true) ?: [];

            Log::info('Brevo Mail Response', [
                'email' => $email,
                'sender' => $this->senderEmail,
                'subject' => $subject,
                'html_length' => strlen($htmlContent),
                'messageId' => $body['messageId'] ?? null,
            ]);

            return $body;
        } catch (\Exception $e) {
            Log::error('Brevo API Error', [
                'email' => $email,
                'subject' => $subject,
                'message' => $e->getMessage(),
            ]);

            return [
                'error' => true,
                'message' => 'Failed to send email: ' . $e->getMessage(),
            ];
        }
    }

    public function sendBulkEmail($emails, $subject, $templateFile, $data)
    {
        $emails = $this->cleanEmails((array) $emails);

        if (empty($emails)) {
            return [
                'error' => true,
                'message' => 'No valid recipient emails found',
            ];
        }

        if (empty($this->apiKey)) {
            Log::error('Brevo API key missing for bulk email', [
                'subject' => $subject,
                'recipient_count' => count($emails),
            ]);

            return [
                'error' => true,
                'message' => 'Brevo API key is missing',
            ];
        }

        $template = empty($templateFile) ? 'emails.template' : $templateFile;
        $htmlContent = view($template, array_merge($data, ['title' => $subject]))->render();

        $sent = [];
        $failed = [];

        foreach ($emails as $email) {
            $payload = [
                'sender' => [
                    'name' => $this->senderName,
                    'email' => $this->senderEmail,
                ],
                'to' => [
                    ['email' => $email],
                ],
                'replyTo' => [
                    'name' => $this->senderName,
                    'email' => $this->senderEmail,
                ],
                'subject' => $subject,
                'htmlContent' => $htmlContent,
            ];

            try {
                $response = $this->client->post('smtp/email', [
                    'headers' => $this->headers(),
                    'json' => $payload,
                ]);

                $body = json_decode($response->getBody(), true) ?: [];
                $messageId = $body['messageId'] ?? null;

                $sent[] = [
                    'email' => $email,
                    'messageId' => $messageId,
                ];

                Log::info('Brevo Bulk Mail Response', [
                    'email' => $email,
                    'sender' => $this->senderEmail,
                    'subject' => $subject,
                    'html_length' => strlen($htmlContent),
                    'messageId' => $messageId,
                ]);
            } catch (\Exception $e) {
                $failed[] = [
                    'email' => $email,
                    'message' => $e->getMessage(),
                ];

                Log::error('Brevo Bulk Email Error', [
                    'email' => $email,
                    'subject' => $subject,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        if (!empty($failed)) {
            return [
                'error' => true,
                'message' => count($failed) . ' email(s) failed to send',
                'sent' => $sent,
                'failed' => $failed,
            ];
        }

        return [
            'error' => false,
            'sent' => $sent,
            'count' => count($sent),
        ];
    }

    private function headers(): array
    {
        return [
            'api-key' => $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    private function cleanEmails(array $emails): array
    {
        $valid = [];

        foreach ($emails as $email) {
            $email = strtolower(trim((string) $email));
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $valid[] = $email;
            }
        }

        return array_values(array_unique($valid));
    }
}
