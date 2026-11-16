<?php

namespace App\Cms;

use App\Models\CmsEnquiry;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class PublicEnquiryRecorder
{
    public const JSONL_PATH = 'public_enquiries.jsonl';

    /**
     * @param  array{name: string, email: string, organisation?: string|null, subject: string, message: string}  $validated
     */
    public function record(array $validated, ?string $ip): void
    {
        $receivedAt = now();
        $hash = $this->sourceHash($validated);

        $line = json_encode([
            'received_at' => $receivedAt->toIso8601String(),
            'ip' => $ip,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'organisation' => $validated['organisation'] ?? null,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
        ], JSON_UNESCAPED_UNICODE);

        if ($line === false) {
            throw new RuntimeException('The enquiry could not be stored.');
        }

        Storage::disk('local')->append(self::JSONL_PATH, $line);

        try {
            $this->storeInboxRecord($validated, $hash, $ip, $receivedAt);
            Log::info('Public website enquiry received');
        } catch (Throwable) {
            if (CmsEnquiry::query()->where('source_hash', $hash)->exists()) {
                Log::info('Public website enquiry received');

                return;
            }

            Log::error('Public enquiry JSONL stored but CMS inbox write failed.');

            throw new RuntimeException('The enquiry could not be fully recorded.');
        }
    }

    /**
     * @param  array{name: string, email: string, organisation?: string|null, subject: string, message: string}  $validated
     */
    private function sourceHash(array $validated): string
    {
        return hash('sha256', implode("\n", [
            mb_strtolower(trim($validated['email'])),
            trim($validated['name']),
            trim((string) ($validated['organisation'] ?? '')),
            trim($validated['subject']),
            trim($validated['message']),
        ]));
    }

    /**
     * @param  array{name: string, email: string, organisation?: string|null, subject: string, message: string}  $validated
     */
    private function storeInboxRecord(array $validated, string $hash, ?string $ip, \DateTimeInterface $receivedAt): void
    {
        if (CmsEnquiry::query()->where('source_hash', $hash)->exists()) {
            return;
        }

        $enquiry = new CmsEnquiry;
        $enquiry->received_at = $receivedAt;
        $enquiry->name = $validated['name'];
        $enquiry->email = $validated['email'];
        $enquiry->organisation = $validated['organisation'] ?? null;
        $enquiry->subject = $validated['subject'];
        $enquiry->message = $validated['message'];
        $enquiry->source_ip = $ip;
        $enquiry->source_file = self::JSONL_PATH;
        $enquiry->source_hash = $hash;
        $enquiry->status = CmsEnquiry::STATUS_NEW;
        $enquiry->save();
    }
}
