<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingCampaign extends Model
{
    protected $table = 'marketing_campaigns';

    protected $fillable = [
        'campaign_name',
        'template_id',
        'recipient_type',
        'total_recipients',
        'sent_count',
        'failed_count',
        'status',
        'scheduled_at',
        'created_by',
    ];

    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    public function recipients()
    {
        return $this->hasMany(MarketingCampaignRecipient::class, 'campaign_id');
    }
}
