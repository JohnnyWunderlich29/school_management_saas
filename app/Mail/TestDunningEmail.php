<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestDunningEmail extends Mailable
{
    use Queueable, SerializesModels;

    public int $schoolId;

    public function __construct(int $schoolId)
    {
        $this->schoolId = $schoolId;
    }

    public function build()
    {
        return $this
            ->subject('Teste de Cobrança - Escola SaaS')
            ->view('emails.finance.test_dunning')
            ->with([
                'schoolId' => $this->schoolId,
                'sentAt' => now(),
            ]);
    }
}