@extends('dashboard.layouts.master')

@section('title', 'Campaign History')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <div class="page-header">
            <h1 class="page-title">Campaign History</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/admin/marketing/campaigns">Campaigns</a></li>
                <li class="breadcrumb-item active">History</li>
            </ol>
        </div>

        {{-- Hero --}}
        <div class="mkt-hero">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <div class="mkt-hero-title"><i class="fe fe-clock me-2"></i>Campaign History</div>
                    <div class="mkt-hero-sub">Full audit trail of all email campaigns sent from this system.</div>
                </div>
                <div style="z-index:1;">
                    <a href="/admin/marketing/campaigns/create" class="btn btn-sm" style="background:#fff;color:#0f766e;font-weight:600;border-radius:10px;border:none;">
                        <i class="fe fe-plus me-1"></i> New Campaign
                    </a>
                </div>
            </div>
        </div>

        <div class="card mkt-table-card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="fe fe-list me-2 text-primary"></i>All Records</h6>
                <span class="mkt-badge mkt-badge-sent">{{ $campaigns->total() }} total</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="historyTable" class="table mkt-table table-hover mb-0 text-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 fw-normal text-muted fs-12">#</th>
                                <th class="fw-semibold">Campaign</th>
                                <th class="fw-semibold">Template</th>
                                <th class="fw-semibold">Recipient Type</th>
                                <th class="text-center fw-semibold">Total</th>
                                <th class="text-center fw-semibold">Sent</th>
                                <th class="text-center fw-semibold">Failed</th>
                                <th class="text-center fw-semibold">Status</th>
                                <th class="fw-semibold">Sent By</th>
                                <th class="fw-semibold">Scheduled At</th>
                                <th class="fw-semibold">Date</th>
                                <th class="text-center fw-semibold">View</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($campaigns as $c)
                            <tr>
                                <td class="ps-4 text-muted fs-12">{{ $c->id }}</td>
                                <td>
                                    <div class="fw-semibold fs-13">{{ $c->campaign_name }}</div>
                                </td>
                                <td class="text-muted fs-13">{{ $c->template->name ?? '—' }}</td>
                                <td>
                                    @if($c->recipient_type==='all_clients')      <span class="mkt-badge mkt-badge-sent"><i class="fe fe-users me-1"></i>All Clients</span>
                                    @elseif($c->recipient_type==='specific_clients') <span class="mkt-badge mkt-badge-scheduled"><i class="fe fe-user-check me-1"></i>Specific</span>
                                    @else <span class="mkt-badge mkt-badge-draft"><i class="fe fe-globe me-1"></i>External</span>
                                    @endif
                                </td>
                                <td class="text-center fs-13">{{ number_format($c->total_recipients) }}</td>
                                <td class="text-center fs-13 fw-semibold text-success">{{ number_format($c->sent_count) }}</td>
                                <td class="text-center fs-13 fw-semibold {{ $c->failed_count > 0 ? 'text-danger' : 'text-muted' }}">{{ number_format($c->failed_count) }}</td>
                                <td class="text-center">
                                    @if($c->status==='sent')          <span class="mkt-badge mkt-badge-sent">Sent</span>
                                    @elseif($c->status==='scheduled') <span class="mkt-badge mkt-badge-scheduled">Scheduled</span>
                                    @elseif($c->status==='sending')   <span class="mkt-badge mkt-badge-sending">Sending</span>
                                    @elseif($c->status==='failed')    <span class="mkt-badge mkt-badge-failed">Failed</span>
                                    @else                              <span class="mkt-badge mkt-badge-draft">Draft</span>
                                    @endif
                                </td>
                                <td class="text-muted fs-12">{{ $c->created_by ?? '—' }}</td>
                                <td class="text-muted fs-12">{{ $c->scheduled_at ? date('d M Y, H:i', strtotime($c->scheduled_at)) : '—' }}</td>
                                <td class="text-muted fs-12">{{ date('d M Y, H:i', strtotime($c->created_at)) }}</td>
                                <td class="text-center">
                                    <a href="/admin/marketing/campaigns/{{ $c->id }}" class="btn btn-sm btn-outline-primary" style="border-radius:8px;" title="View">
                                        <i class="fe fe-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="text-center py-5">
                                    <i class="fe fe-clock" style="font-size:2.5rem;display:block;margin-bottom:.75rem;opacity:.35;"></i>
                                    <div class="fw-semibold text-muted mb-1">No campaign history</div>
                                    <div class="fs-13 text-muted">Campaigns you send will appear here.</div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($campaigns->hasPages())
            <div class="card-footer bg-white">{{ $campaigns->links() }}</div>
            @endif
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
$('#historyTable').DataTable({
    dom: '<"row mb-2"<"col-md-6"f><"col-md-6 text-end"l>><"row"<"col"t>><"row mt-2"<"col-md-6"i><"col-md-6"p>>',
    order: [[0, 'desc']],
    pageLength: 25,
    columnDefs: [{ orderable: false, targets: [11] }],
});
</script>
@endsection
