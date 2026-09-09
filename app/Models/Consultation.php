<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Consultation extends Model
{
    use Auditable;

    public const STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'meeting_scheduled' => 'Meeting Scheduled',
        'proposal_sent' => 'Proposal Sent',
        'negotiation' => 'Negotiation',
        'won' => 'Won',
        'lost' => 'Lost',
        'archived' => 'Archived',
    ];

    public const PRIORITIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
    ];

    protected $fillable = [
        'reference_no', 'full_name', 'company_name', 'email', 'phone', 'country', 'city', 'language',
        'service', 'project_type', 'description', 'budget', 'timeline',
        'contact_method', 'meeting_date', 'meeting_time', 'timezone', 'attachment',
        'status', 'priority', 'assigned_to', 'notes',
        'source', 'ip_address', 'browser',
    ];

    protected function casts(): array
    {
        return [
            'meeting_date' => 'date',
        ];
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        return $this->attachment ? Storage::disk('public')->url($this->attachment) : null;
    }
}
