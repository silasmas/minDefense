<?php

namespace App\Jobs;

use App\Models\Veteran;
use App\Services\SmsSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPaymentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $veteranId,
        public string $message,
    ) {}

    public function handle(SmsSender $sms): void
    {
        $veteran = Veteran::find($this->veteranId);
        if (! $veteran || ! $veteran->phone) {
            return;
        }

        $sms->send($veteran->phone, $this->message);
    }
}
