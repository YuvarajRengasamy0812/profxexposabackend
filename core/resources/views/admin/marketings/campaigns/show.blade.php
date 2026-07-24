@extends('dashboard.layouts.master')

@section('title', 'Campaign Details')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <div class="page-header">
            <h1 class="page-title">Campaign Details</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('adminHome') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('marketingCampaigns') }}">Campaigns</a></li>
                <li class="breadcrumb-item active">{{ $campaign->campaign_name }}</li>
            </ol>
        </div>

        @php
            $deliveryPct = $campaign->total_recipients > 0
                ? round(($campaign->sent_count / $campaign->total_recipients) * 100)
                : 0;
        @endphp

        {{-- Stat Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card mkt-stat-card border-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="mkt-stat-icon mkt-stat-icon-primary"><i class="fe fe-users"></i></div>
                        <div>
                            <div class="mkt-stat-val">{{ number_format($campaign->total_recipients) }}</div>
                            <div class="mkt-stat-lbl">Total Recipients</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card mkt-stat-card border-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="mkt-stat-icon mkt-stat-icon-success"><i class="fe fe-check-circle"></i></div>
                        <div>
                            <div class="mkt-stat-val">{{ number_format($campaign->sent_count) }}</div>
                            <div class="mkt-stat-lbl">Delivered</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card mkt-stat-card border-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="mkt-stat-icon mkt-stat-icon-danger"><i class="fe fe-alert-circle"></i></div>
                        <div>
                            <div class="mkt-stat-val">{{ number_format($campaign->failed_count) }}</div>
                            <div class="mkt-stat-lbl">Failed</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card mkt-stat-card border-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="mkt-stat-icon mkt-stat-icon-info"><i class="fe fe-percent"></i></div>
                        <div>
                            <div class="mkt-stat-val">{{ $deliveryPct }}%</div>
                            <div class="mkt-stat-lbl">Delivery Rate</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">

            {{-- Left: Campaign Info --}}
            <div class="col-lg-4">

                {{-- Info Card --}}
                <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
                    <div class="card-header bg-white border-bottom" style="border-radius:14px 14px 0 0;">
                        <h6 class="mb-0 fw-semibold"><i class="fe fe-info me-2 text-primary"></i>Campaign Info</h6>
                    </div>
                    <div class="card-body pb-2">
                        <div class="mkt-info-row">
                            <span class="mkt-info-label">Campaign</span>
                            <span class="mkt-info-val">{{ $campaign->campaign_name }}</span>
                        </div>
                        <div class="mkt-info-row">
                            <span class="mkt-info-label">Template</span>
                            <span class="mkt-info-val">{{ $campaign->template->name ?? '—' }}</span>
                        </div>
                        <div class="mkt-info-row">
                            <span class="mkt-info-label">Recipient Type</span>
                            <span class="mkt-info-val">
                                @if($campaign->recipient_type==='all_clients') All Clients
                                @elseif($campaign->recipient_type==='specific_clients') Specific Clients
                                @else External List @endif
                            </span>
                        </div>
                        <div class="mkt-info-row">
                            <span class="mkt-info-label">Status</span>
                            <span class="mkt-info-val">
                                @if($campaign->status==='sent')      <span class="mkt-badge mkt-badge-sent">Sent</span>
                                @elseif($campaign->status==='scheduled') <span class="mkt-badge mkt-badge-scheduled">Scheduled</span>
                                @elseif($campaign->status==='sending')   <span class="mkt-badge mkt-badge-sending">Sending</span>
                                @elseif($campaign->status==='failed')    <span class="mkt-badge mkt-badge-failed">Failed</span>
                                @else                                     <span class="mkt-badge mkt-badge-draft">Draft</span>
                                @endif
                            </span>
                        </div>
                        @if($campaign->scheduled_at)
                        <div class="mkt-info-row">
                            <span class="mkt-info-label">Scheduled At</span>
                            <span class="mkt-info-val fs-12">{{ date('d M Y, H:i', strtotime($campaign->scheduled_at)) }}</span>
                        </div>
                        @endif
                        <div class="mkt-info-row">
                            <span class="mkt-info-label">Sent By</span>
                            <span class="mkt-info-val fs-12">{{ $campaign->created_by ?? '—' }}</span>
                        </div>
                        <div class="mkt-info-row">
                            <span class="mkt-info-label">Created</span>
                            <span class="mkt-info-val fs-12">{{ date('d M Y, H:i', strtotime($campaign->created_at)) }}</span>
                        </div>

                        {{-- Delivery progress --}}
                        <div class="mt-3 mb-1">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fs-12 text-muted">Delivery Progress</span>
                                <span class="fs-12 fw-semibold">{{ $deliveryPct }}%</span>
                            </div>
                            <div class="mkt-progress">
                                <div class="mkt-progress-fill" style="width:{{ $deliveryPct }}%"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="card-footer bg-white d-grid gap-2" style="border-radius:0 0 14px 14px;">
                        @if($campaign->status === 'scheduled')
                        <button type="button" class="btn btn-success btn-sm fw-semibold" id="sendNowBtn" style="border-radius:9px;">
                            <i class="fe fe-zap me-1"></i> Send Now
                        </button>
                        @endif
                        <a href="{{ route('marketingCampaigns') }}"" class="btn btn-outline-secondary btn-sm" style="border-radius:9px;">
                            <i class="fe fe-arrow-left me-1"></i> Back to Campaigns
                        </a>
                        <a href="{{ route('marketingCampaignsHistory') }}" class="btn btn-outline-primary btn-sm" style="border-radius:9px;">
                            <i class="fe fe-clock me-1"></i> View History
                        </a>
                    </div>
                </div>

            </div>

            {{-- Right: Recipients Table --}}
            <div class="col-lg-8">
                <div class="card mkt-table-card border-0 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-semibold"><i class="fe fe-mail me-2 text-primary"></i>Recipients</h6>
                        <span class="mkt-badge mkt-badge-sent">{{ $recipients->total() }} total</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mkt-table table-hover mb-0 text-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4 fw-semibold fs-12">#</th>
                                        <th class="fw-semibold fs-12">Name</th>
                                        <th class="fw-semibold fs-12">Email</th>
                                        <th class="text-center fw-semibold fs-12">Status</th>
                                        <th class="fw-semibold fs-12">Sent At</th>
                                        <th class="fw-semibold fs-12">Error</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recipients as $r)
                                    <tr>
                                        <td class="ps-4 text-muted fs-12">{{ $r->id }}</td>
                                        <td class="fs-13">{{ $r->name ?: '—' }}</td>
                                        <td class="fs-13">{{ $r->email }}</td>
                                        <td class="text-center">
                                            @if($r->status === 'sent')
                                                <span class="mkt-badge mkt-badge-sent">Sent</span>
                                            @else
                                                <span class="mkt-badge mkt-badge-failed">Failed</span>
                                            @endif
                                        </td>
                                        <td class="text-muted fs-12">{{ $r->sent_at ? date('d M Y, H:i', strtotime($r->sent_at)) : '—' }}</td>
                                        <td class="text-danger fs-12" style="max-width:200px;white-space:normal;">{{ $r->error_message ?: '' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <i class="fe fe-mail" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.35;"></i>
                                            No recipient records.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($recipients->hasPages())
                    <div class="card-footer bg-white">{{ $recipients->links() }}</div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('after-scripts')
<script>
const csrfToken = $('meta[name="csrf-token"]').attr('content');
if (typeof window.Swal === 'undefined') {
    window.Swal = {
        mixin() {
            return { fire(options) { alert(options.title || options.text || ''); } };
        },
        fire(options) {
            const title = typeof options === 'string' ? options : (options.title || '');
            const text = typeof options === 'string' ? '' : (options.text || '');
            if (options && options.showCancelButton) {
                const ok = confirm((title ? title + '\n' : '') + text);
                return { then(callback) { callback({ isConfirmed: ok }); } };
            }
            if (window.swal) swal(title, text, options.icon || options.type || '');
            else alert((title ? title + '\n' : '') + text);
            return { then(callback) { if (callback) callback({ isConfirmed: true }); } };
        },
        close() {},
        showLoading() {}
    };
}
const Toast = Swal.mixin({ toast:true, position:'top-end', showConfirmButton:false, timer:2500, timerProgressBar:true });
function toast(type, msg) { Toast.fire({ icon:type, title:msg }); }

@if($campaign->status === 'scheduled')
$('#sendNowBtn').on('click', function () {
    Swal.fire({
        title: 'Send Now?',
        text: 'This will immediately send "{{ $campaign->campaign_name }}" to all recipients.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, send now',
        confirmButtonColor: '#198754',
    }).then(result => {
        if (!result.isConfirmed) return;
        Swal.fire({ title: 'Sending...', allowOutsideClick: false, allowEscapeKey: false, didOpen: () => Swal.showLoading() });

        $.post(@json(route('marketingCampaignsSendNow')), {
            _token: csrfToken,
            campaign_id: {{ $campaign->id }}
        }, function (res) {
            Swal.close();
            if (res.status === 1) {
                Swal.fire({ icon:'success', title:'Sent!', text:`${res.count} email(s) sent.`, confirmButtonColor:'#ffbe00' })
                    .then(() => location.reload());
            } else {
                Swal.fire({ icon:'error', title:'Failed', text: res.message || 'Something went wrong' });
            }
        }).fail(() => { Swal.close(); Swal.fire({ icon:'error', title:'Request Failed' }); });
    });
});
@endif
</script>
@endpush
