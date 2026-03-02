<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Certificate;

class CertificateCreatedNotification extends Notification
{
    use Queueable;

    protected $certificate;

    public function __construct(Certificate $certificate)
    {
        $this->certificate = $certificate;
    }

    public function via($notifiable)
    {
        return ['database']; // Store in database
    }

    public function toArray($notifiable)
    {
        $resident = $this->certificate->resident;
        $residentName = $resident ? $resident->first_name . ' ' . $resident->last_name : 'Unknown';

        return [
            'title' => 'New Certificate Request',
            'message' => $this->certificate->certificate_type . ' certificate requested for ' . $residentName,
            'certificate_id' => $this->certificate->id,
            'certificate_number' => $this->certificate->certificate_number,
        ];
    }
}
