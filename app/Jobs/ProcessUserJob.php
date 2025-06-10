<?php

namespace App\Jobs;

use App\Models\UserData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessUserJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected UserData $user;

    public function __construct(UserData $user)
    {
        $this->user = $user;
    }

    public function handle(): void
    {
        Log::info('[Job] Iniciando análise de risco', ['cpf' => $this->user->cpf]);

        // Lógica de risco
        $risk = $this->user->cpf_status === 'negativado' &&
                str_starts_with($this->user->cep, '064') ? 'high_risk' : 'low_risk';

        // Geração de PDF com DomPDF
        $pdf = Pdf::loadView('pdf.report', [
            'user' => $this->user,
            'risk' => $risk,
        ]);

        $filename = "relatorios/user_{$this->user->cpf}.pdf";
        Storage::put("public/{$filename}", $pdf->output());

        Log::info('[Job] PDF gerado e simulado envio', [
            'cpf' => $this->user->cpf,
            'risk' => $risk,
            'pdf_path' => "storage/{$filename}"
        ]);
    }
}
