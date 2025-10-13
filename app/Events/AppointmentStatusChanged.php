<?php

namespace App\Events;

use App\Models\Appointment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppointmentStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Appointment $appointment;
    public string $fromStatus;
    public string $toStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(Appointment $appointment, string $fromStatus, string $toStatus)
    {
        $this->appointment = $appointment;
        $this->fromStatus = $fromStatus;
        $this->toStatus = $toStatus;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('appointments'),
            new Channel('doctor.' . $this->appointment->doctor_id),
            new Channel('patient.' . $this->appointment->patient_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'status.changed';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'doctor_id' => $this->appointment->doctor_id,
            'patient_id' => $this->appointment->patient_id,
            'from_status' => $this->fromStatus,
            'to_status' => $this->toStatus,
            'ticket_no' => $this->appointment->ticket_no,
            'slot_start' => $this->appointment->slot_start->toISOString(),
        ];
    }
}

