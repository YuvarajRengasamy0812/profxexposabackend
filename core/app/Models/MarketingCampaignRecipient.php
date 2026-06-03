<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingCampaignRecipient extends Model
{
    protected $table = 'marketing_campaign_recipients';

    public $timestamps = false;

    protected $fillable = [
        'campaign_id',
        'email',
        'name',
        'status',
        'error_message',
        'sent_at',
    ];
}
