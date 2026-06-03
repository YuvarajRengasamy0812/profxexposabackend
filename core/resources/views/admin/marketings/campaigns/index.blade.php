@extends('dashboard.layouts.master')

@section('styles')
<link href="/admin_assets/assets/css/marketing.css" rel="stylesheet">
@endsection

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <div class="page-header">
            <h1 class="page-title">Marketing Campaigns</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('adminHome') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Marketing Campaigns</li>
            </ol>
        </div>

        {{-- Hero Banner --}}
        <div class="mkt-hero">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <div class="mkt-hero-title"><i class="fe fe-send me-2"></i>Email Campaigns</div>
                    <div class="mkt-hero-sub">Send targeted emails to your clients — all, specific, or external lists.</div>
                    <div class="mkt-hero-stats">
                        <div>
                            <div class="mkt-hero-stat-val">{{ $stats['total'] }}</div>
                            <div class="mkt-hero-stat-lbl">Total</div>
                        </div>
                        <div>
                            <div class="mkt-hero-stat-val">{{ $stats['sent'] }}</div>
                            <div class="mkt-hero-stat-lbl">Sent</div>
                        </div>
                        <div>
                            <div class="mkt-hero-stat-val">{{ $stats['scheduled'] }}</div>
                            <div class="mkt-hero-stat-lbl">Scheduled</div>
                        </div>
                        <div>
                            <div class="mkt-hero-stat-val">{{ $stats['failed'] }}</div>
                            <div class="mkt-hero-stat-lbl">Failed</div>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 align-items-center" style="z-index:1">
                    <a href="{{ route('marketingCampaignsHistory') }}" class="btn btn-sm" style="background:rgba(255,255,255,0.2);color:#fff;border:1.5px solid rgba(255,255,255,0.35);border-radius:10px;">
                        <i class="fe fe-clock me-1"></i> History
                    </a>
                    <a href="{{ url(config('smartend.backend_path').'/email-templates') }}" class="btn btn-sm" style="background:rgba(255,255,255,0.2);color:#fff;border:1.5px solid rgba(255,255,255,0.35);border-radius:10px;">
                        <i class="fe fe-file-text me-1"></i> Templates
                    </a>
                    <a href="{{ route('marketingCampaignsCreate') }}" class="btn btn-sm" style="background:#fff;color:var(--primary-color,#ffbe00);font-weight:600;border-radius:10px;border:none;">
                        <i class="fe fe-plus me-1"></i> New Campaign
                    </a>
                </div>
            </div>
        </div>

        {{-- Stat Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card mkt-stat-card border-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="mkt-stat-icon mkt-stat-icon-primary"><i class="fe fe-layers"></i></div>
                        <div>
                            <div class="mkt-stat-val">{{ $stats['total'] }}</div>
                            <div class="mkt-stat-lbl">Total Campaigns</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card mkt-stat-card border-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="mkt-stat-icon mkt-stat-icon-success"><i class="fe fe-check-circle"></i></div>
                        <div>
                            <div class="mkt-stat-val">{{ $stats['sent'] }}</div>
                            <div class="mkt-stat-lbl">Sent</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card mkt-stat-card border-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="mkt-stat-icon mkt-stat-icon-warning"><i class="fe fe-clock"></i></div>
                        <div>
                            <div class="mkt-stat-val">{{ $stats['scheduled'] }}</div>
                            <div class="mkt-stat-lbl">Scheduled</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card mkt-stat-card border-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="mkt-stat-icon mkt-stat-icon-danger"><i class="fe fe-alert-circle"></i></div>
                        <div>
                            <div class="mkt-stat-val">{{ $stats['failed'] }}</div>
                            <div class="mkt-stat-lbl">Failed</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Campaigns Table --}}
        <div class="card mkt-table-card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="fe fe-list me-2 text-primary"></i>All Campaigns</h6>
                <a href="{{ route('marketingCampaignsCreate') }}" class="btn btn-primary btn-sm" style="border-radius:8px;">
                    <i class="fe fe-plus me-1"></i> Create Campaign
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mkt-table table-hover mb-0 text-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 text-muted fw-normal fs-12">#</th>
                                <th class="fw-semibold">Campaign</th>
                                <th class="fw-semibold">Template</th>
                                <th class="fw-semibold">Recipients</th>
                                <th class="text-center fw-semibold">Sent</th>
                                <th class="text-center fw-semibold">Failed</th>
                                <th class="text-center fw-semibold">Status</th>
                                <th class="fw-semibold">Scheduled At</th>
                                <th class="fw-semibold">Created</th>
                                <th class="text-center fw-semibold">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($campaigns as $c)
                            <tr>
                                <td class="ps-4 text-muted fs-12">{{ $c->id }}</td>
                                <td>
                                    <div class="fw-semibold fs-13">{{ $c->campaign_name }}</div>
                                    <div class="fs-11 text-muted">
                                        @if($c->recipient_type === 'all_clients') <i class="fe fe-users me-1"></i>All Clients
                                        @elseif($c->recipient_type === 'specific_clients') <i class="fe fe-user-check me-1"></i>Specific Clients
                                        @else <i class="fe fe-globe me-1"></i>External List @endif
                                    </div>
                                </td>
                                <td class="text-muted fs-13">{{ $c->template->name ?? '—' }}</td>
                                <td class="fs-13">{{ number_format($c->total_recipients) }}</td>
                                <td class="text-center fs-13 fw-semibold text-success">{{ number_format($c->sent_count) }}</td>
                                <td class="text-center fs-13 fw-semibold {{ $c->failed_count > 0 ? 'text-danger' : 'text-muted' }}">{{ number_format($c->failed_count) }}</td>
                                <td class="text-center">
                                    @if($c->status==='sent')      <span class="mkt-badge mkt-badge-sent">Sent</span>
                                    @elseif($c->status==='scheduled') <span class="mkt-badge mkt-badge-scheduled">Scheduled</span>
                                    @elseif($c->status==='sending')   <span class="mkt-badge mkt-badge-sending">Sending</span>
                                    @elseif($c->status==='failed')    <span class="mkt-badge mkt-badge-failed">Failed</span>
                                    @else                              <span class="mkt-badge mkt-badge-draft">Draft</span>
                                    @endif
                                </td>
                                <td class="text-muted fs-12">
                                    {{ $c->scheduled_at ? date('d M Y, H:i', strtotime($c->scheduled_at)) : '—' }}
                                </td>
                                <td class="text-muted fs-12">{{ date('d M Y, H:i', strtotime($c->created_at)) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('marketingCampaignsShow', $c->id) }}" class="btn btn-sm btn-outline-primary" style="border-radius:8px;" title="View">
                                        <i class="fe fe-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fe fe-send" style="font-size:2.5rem;display:block;margin-bottom:.75rem;opacity:.35;"></i>
                                        <div class="fw-semibold mb-1">No campaigns yet</div>
                                        <div class="fs-13 mb-3">Start by creating your first campaign.</div>
                                        <a href="{{ route('marketingCampaignsCreate') }}" class="btn btn-primary btn-sm" style="border-radius:8px;">
                                            <i class="fe fe-plus me-1"></i> Create Campaign
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($campaigns->hasPages())
            <div class="card-footer bg-white border-top">{{ $campaigns->links() }}</div>
            @endif
        </div>

    </div>
</div>
@endsection
