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

class AppointmentReminder implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Appointment $appointment;
    public string $target;

    /**
     * Create a new event instance.
     */
    public function __construct(Appointment $appointment, string $target)
    {
        $this->appointment = $appointment;
        $this->target = $target;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        if ($this->target === 'patient') {
            return [
                new Channel('patient.' . $this->appointment->patient_id),
                new Channel('notifications.' . $this->appointment->patient_id),
            ];
        } else {
            return [
                new Channel('doctor.' . $this->appointment->doctor_id),
                new Channel('notifications.' . $this->appointment->doctor->user_id),
            ];
        }
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'appointment.reminder';
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
            'ticket_no' => $this->appointment->ticket_no,
            'slot_start' => $this->appointment->slot_start->toISOString(),
            'target' => $this->target,
            'message' => $this->target === 'patient' 
                ? "Через 10 минут ваша очередь к врачу {$this->appointment->doctor->user->name}"
                : "Через 10 минут прием пациента {$this->appointment->patient->name}",
            'doctor_name' => $this->appointment->doctor->user->name,
            'patient_name' => $this->appointment->patient->name,
        ];
    }
}
