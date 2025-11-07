<?php

namespace App\Mail;

use App\Models\Entretien;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EntretienPlanifieMail extends Mailable
{
    use Queueable, SerializesModels;

    public $entretien;

    /**
     * Create a new message instance.
     */
    public function __construct(Entretien $entretien)
    {
        $this->entretien = $entretien;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $logoPath = public_path('images/unit-logo.png');
        $logoCid = null;

        if (file_exists($logoPath)) {
            $logoCid = $this->embed($logoPath);
        }

        return $this
            ->subject('Votre entretien a été planifié')
            ->markdown('emails.entretien.planifie', [
                'logoCid' => $logoCid
            ]);
    }
}
