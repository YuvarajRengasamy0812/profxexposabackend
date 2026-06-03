@extends('dashboard.layouts.master')





@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <div class="page-header">
            <h1 class="page-title">Create Campaign</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/admin/marketing/campaigns">Campaigns</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </div>

        <div class="row g-4">

            {{-- ── LEFT COL: Main Form ── --}}
            <div class="col-lg-8">

                {{-- Campaign Details --}}
                <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
                    <div class="card-header bg-white border-bottom" style="border-radius:14px 14px 0 0;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="mkt-stat-icon mkt-stat-icon-primary" style="width:34px;height:34px;border-radius:9px;font-size:1rem;">
                                <i class="fe fe-edit-3"></i>
                            </div>
                            <h6 class="mb-0 fw-semibold">Campaign Details</h6>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold fs-13">Campaign Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="campaign_name" placeholder="e.g. May Promo 2026" style="border-radius:9px;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold fs-13">Email Template <span class="text-danger">*</span></label>
                                <select class="form-select" id="template_id" style="border-radius:9px;">
                                    <option value="" hidden>— Select Template —</option>
                                    @foreach($templates as $t)
                                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">
                                    <a href="{{ route('email-templates') }}" target="_blank" class="text-primary">
                                        <i class="fe fe-external-link me-1"></i>Manage Templates
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Recipients --}}
                <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
                    <div class="card-header bg-white border-bottom" style="border-radius:14px 14px 0 0;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="mkt-stat-icon mkt-stat-icon-info" style="width:34px;height:34px;border-radius:9px;font-size:1rem;">
                                <i class="fe fe-users"></i>
                            </div>
                            <h6 class="mb-0 fw-semibold">Recipients</h6>
                        </div>
                    </div>
                    <div class="card-body">

                        <div class="mkt-section-label">Select Recipient Type</div>
                        <div class="d-flex gap-3 mb-4 flex-wrap mkt-recipient-group">
                            <input type="radio" class="btn-check" name="recipient_type" id="rt_all" value="all_clients" autocomplete="off">
                            <label class="btn btn-outline-primary flex-fill" for="rt_all" style="border-radius:10px;">
                                <i class="fe fe-users me-1"></i> All Clients
                            </label>
                            <input type="radio" class="btn-check" name="recipient_type" id="rt_specific" value="specific_clients" autocomplete="off">
                            <label class="btn btn-outline-primary flex-fill" for="rt_specific" style="border-radius:10px;">
                                <i class="fe fe-user-check me-1"></i> Specific Clients
                            </label>
                            <input type="radio" class="btn-check" name="recipient_type" id="rt_external" value="external" autocomplete="off">
                            <label class="btn btn-outline-primary flex-fill" for="rt_external" style="border-radius:10px;">
                                <i class="fe fe-globe me-1"></i> External List
                            </label>
                        </div>

                        {{-- All Clients Panel --}}
                        <div id="panel_all" class="recipient-panel mkt-panel-box d-none">
                            <div class="d-flex align-items-center gap-3">
                                <div class="mkt-stat-icon mkt-stat-icon-primary" style="width:40px;height:40px;border-radius:10px;">
                                    <i class="fe fe-users"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold fs-13">All Active Registered Clients</div>
                                    <div class="text-muted fs-12">The email will be sent to every active client in the system.</div>
                                </div>
                            </div>
                        </div>

                        {{-- Specific Clients Panel --}}
                        <div id="panel_specific" class="recipient-panel mkt-panel-box d-none">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="openPickerBtn" style="border-radius:8px;">
                                    <i class="fe fe-user-plus me-1"></i> Pick Clients
                                </button>
                                <span class="fs-13 text-muted" id="selectedCount">No clients selected</span>
                            </div>
                            <div id="selectedClientsTags" class="d-flex flex-wrap gap-1"></div>
                        </div>

                        {{-- External List Panel --}}
                        <div id="panel_external" class="recipient-panel mkt-panel-box d-none">
                            <div class="mb-3">
                                <label class="form-label fw-semibold fs-13">Paste Email Addresses</label>
                                <textarea class="form-control font-monospace" id="external_emails" rows="6"
                                    placeholder="One per line or comma-separated:&#10;john@example.com&#10;Jane Doe &lt;jane@example.com&gt;"
                                    style="border-radius:9px; font-size:0.8rem;"></textarea>
                                <div class="form-text">Supports: plain email, "Name &lt;email&gt;", or comma-separated.</div>
                            </div>
                            <div class="d-flex align-items-center my-3">
                                <div class="flex-grow-1 border-top"></div>
                                <span class="text-muted fs-12 px-3">OR upload a file</span>
                                <div class="flex-grow-1 border-top"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold fs-13">Upload CSV / Excel</label>
                                <input type="file" class="form-control" id="csv_file" accept=".csv,.xlsx,.xls" style="border-radius:9px;">
                                <div class="form-text">First column must be the email address.</div>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="previewExternalBtn" style="border-radius:8px;">
                                    <i class="fe fe-eye me-1"></i> Preview Count
                                </button>
                                <span class="ms-2 fs-13 text-primary fw-semibold" id="externalCount"></span>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            {{-- ── RIGHT COL: Sending Options ── --}}
            <div class="col-lg-4">

                {{-- Send Mode --}}
                <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
                    <div class="card-header bg-white border-bottom" style="border-radius:14px 14px 0 0;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="mkt-stat-icon mkt-stat-icon-success" style="width:34px;height:34px;border-radius:9px;font-size:1rem;">
                                <i class="fe fe-zap"></i>
                            </div>
                            <h6 class="mb-0 fw-semibold">Send Options</h6>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mkt-section-label mb-2">How to send?</div>
                        <div class="d-flex gap-3 mb-3 mkt-send-mode">
                            <input type="radio" class="btn-check" name="send_mode" id="mode_instant" value="instant" autocomplete="off" checked>
                            <label class="btn btn-outline-success flex-fill" for="mode_instant" style="border-radius:10px;">
                                <i class="fe fe-zap me-1"></i> Send Instantly
                            </label>
                            <input type="radio" class="btn-check" name="send_mode" id="mode_schedule" value="schedule" autocomplete="off">
                            <label class="btn btn-outline-warning flex-fill" for="mode_schedule" style="border-radius:10px;">
                                <i class="fe fe-clock me-1"></i> Schedule
                            </label>
                        </div>

                        {{-- Instant info --}}
                        <div id="instant_info" class="alert alert-success d-flex gap-2 align-items-start py-2 mb-0" style="border-radius:10px;font-size:0.82rem;">
                            <i class="fe fe-check-circle mt-1 flex-shrink-0"></i>
                            <span>Campaign will be sent <strong>immediately</strong> after you click Send.</span>
                        </div>

                        {{-- Schedule picker --}}
                        <div id="schedule_info" class="d-none">
                            <label class="form-label fw-semibold fs-13">Schedule Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="scheduled_at" style="border-radius:9px;"
                                min="{{ date('Y-m-d\TH:i', strtotime('+5 minutes')) }}">
                            <div class="form-text">Campaign will be saved as <strong>Scheduled</strong> and can be sent from the detail page.</div>
                        </div>
                    </div>
                </div>

                {{-- Summary + Actions --}}
                <div class="card border-0 shadow-sm" style="border-radius:14px;">
                    <div class="card-header bg-white border-bottom" style="border-radius:14px 14px 0 0;">
                        <h6 class="mb-0 fw-semibold"><i class="fe fe-info me-2 text-primary"></i>Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="mkt-info-row">
                            <span class="mkt-info-label">Campaign</span>
                            <span class="mkt-info-val fs-12" id="summary_name">—</span>
                        </div>
                        <div class="mkt-info-row">
                            <span class="mkt-info-label">Template</span>
                            <span class="mkt-info-val fs-12" id="summary_template">—</span>
                        </div>
                        <div class="mkt-info-row">
                            <span class="mkt-info-label">Recipients</span>
                            <span class="mkt-info-val fs-12" id="summary_recipients">—</span>
                        </div>
                        <div class="mkt-info-row">
                            <span class="mkt-info-label">Send Mode</span>
                            <span class="mkt-info-val fs-12" id="summary_mode">Instantly</span>
                        </div>
                    </div>
                    <div class="bg-white px-3 pt-2 pb-3" style="border-top:1px solid #f1f3f5; border-radius:0 0 14px 14px;">
                        <button type="button" class="btn btn-primary fw-semibold w-100 mb-3" id="sendCampaignBtn" style="border-radius:10px; padding:10px 0;">
                            <i class="fe fe-send me-2"></i><span id="sendBtnLabel">Send Campaign</span>
                        </button>
                        <a href="{{ route('marketingCampaigns') }}" class="btn btn-outline-secondary w-100" style="border-radius:10px; padding:10px 0;">
                            <i class="fe fe-x me-1"></i> Cancel
                        </a>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

{{-- ══ Client Picker Modal ══ --}}
<div class="modal fade" id="clientPickerModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:14px;overflow:hidden;">
            <div class="modal-header border-bottom">
                <h6 class="modal-title fw-semibold"><i class="fe fe-users me-2 text-primary"></i>Pick Clients</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 border-bottom">
                    <div class="input-group" style="border-radius:9px;overflow:hidden;">
                        <span class="input-group-text bg-light border-end-0"><i class="fe fe-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" id="clientSearch" placeholder="Search by name or email...">
                    </div>
                </div>
                <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center bg-light">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllClientsBtn" style="border-radius:7px;">Select All</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clearClientsBtn" style="border-radius:7px;">Clear</button>
                    </div>
                    <span class="fs-12 text-muted" id="modalSelectedCount">0 selected</span>
                </div>
                <div id="clientListContainer" style="max-height:380px;overflow-y:auto;">
                    <table class="table table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width:44px"><input type="checkbox" id="masterCheck" class="form-check-input"></th>
                                <th class="fs-12 fw-semibold">Name</th>
                                <th class="fs-12 fw-semibold">Email</th>
                            </tr>
                        </thead>
                        <tbody id="clientTableBody">
                            <tr><td colspan="3" class="text-center text-muted py-4">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <span class="fs-13 text-primary fw-semibold" id="modalFooterCount">0 clients selected</span>
                <div>
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal" style="border-radius:8px;">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmPickerBtn" style="border-radius:8px;">
                        <i class="fe fe-check me-1"></i> Confirm Selection
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
const csrfToken = $('meta[name="csrf-token"]').attr('content');
const marketingCampaignsUrl = @json(route('marketingCampaigns'));
const marketingCampaignsStoreUrl = @json(route('marketingCampaignsStore'));
const marketingCampaignsGetClientsUrl = @json(route('marketingCampaignsGetClients'));
let selectedClientIds = [];
let allLoadedClients  = [];

const Toast = Swal.mixin({ toast:true, position:'top-end', showConfirmButton:false, timer:2500, timerProgressBar:true });
function toast(type, msg) { Toast.fire({ icon:type, title:msg }); }

// ── Summary live update ───────────────────────────────────────────────────────
function updateSummary() {
    const name     = $('#campaign_name').val().trim();
    const tmplText = $('#template_id option:selected').text();
    const type     = $('input[name="recipient_type"]:checked').val();
    const mode     = $('input[name="send_mode"]:checked').val();

    $('#summary_name').text(name || '—');
    $('#summary_template').text(tmplText || '—');
    $('#summary_mode').text(mode === 'schedule' ? 'Scheduled' : 'Instantly');

    let rec = '—';
    if (type === 'all_clients') rec = 'All Clients';
    else if (type === 'specific_clients') rec = selectedClientIds.length > 0 ? `${selectedClientIds.length} client(s)` : 'None selected';
    else if (type === 'external') { const e = parseEmails($('#external_emails').val()); rec = e.length > 0 ? `${e.length} email(s)` : 'None'; }
    $('#summary_recipients').text(rec);
}

$('#campaign_name, #template_id, #external_emails').on('input change', updateSummary);
$('input[name="recipient_type"], input[name="send_mode"]').on('change', updateSummary);

// ── Recipient type panels ─────────────────────────────────────────────────────
$('#rt_all').on('change', () => { $('.recipient-panel').addClass('d-none'); $('#panel_all').removeClass('d-none active-panel').addClass('active-panel'); updateSummary(); });
$('#rt_specific').on('change', () => { $('.recipient-panel').addClass('d-none'); $('#panel_specific').removeClass('d-none active-panel').addClass('active-panel'); updateSummary(); });
$('#rt_external').on('change', () => { $('.recipient-panel').addClass('d-none'); $('#panel_external').removeClass('d-none active-panel').addClass('active-panel'); updateSummary(); });

// ── Send mode toggle ──────────────────────────────────────────────────────────
$('#mode_instant').on('change', function () {
    $('#instant_info').removeClass('d-none');
    $('#schedule_info').addClass('d-none');
    $('#sendBtnLabel').text('Send Campaign');
    updateSummary();
});
$('#mode_schedule').on('change', function () {
    $('#instant_info').addClass('d-none');
    $('#schedule_info').removeClass('d-none');
    $('#sendBtnLabel').text('Schedule Campaign');
    updateSummary();
});

// ── Client Picker ─────────────────────────────────────────────────────────────
$('#openPickerBtn').on('click', function () {
    $('#clientPickerModal').modal('show');
    loadClients('');
});

function loadClients(search) {
    $('#clientTableBody').html('<tr><td colspan="3" class="text-center text-muted py-4">Loading...</td></tr>');
    $.get(marketingCampaignsGetClientsUrl, { search }, function (res) {
        allLoadedClients = res.data;
        renderClientTable(allLoadedClients);
    });
}

function renderClientTable(clients) {
    const tbody = $('#clientTableBody');
    if (!clients.length) {
        tbody.html('<tr><td colspan="3" class="text-center text-muted py-3">No clients found</td></tr>');
        return;
    }
    let html = '';
    clients.forEach(c => {
        const chk = selectedClientIds.includes(c.id) ? 'checked' : '';
        html += `<tr>
            <td><input type="checkbox" class="form-check-input client-check" value="${c.id}" data-email="${c.email}" data-name="${c.fullname||''}" ${chk}></td>
            <td class="fs-13">${c.fullname || '—'}</td>
            <td class="text-muted fs-13">${c.email}</td>
        </tr>`;
    });
    tbody.html(html);
    updateModalCount();
}

let searchTimer;
$('#clientSearch').on('input', function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadClients($(this).val().trim()), 350);
});

$(document).on('change', '.client-check', function () {
    const id = parseInt($(this).val());
    if ($(this).is(':checked')) { if (!selectedClientIds.includes(id)) selectedClientIds.push(id); }
    else { selectedClientIds = selectedClientIds.filter(x => x !== id); }
    updateModalCount();
});

$('#masterCheck').on('change', function () {
    $(this).is(':checked') ? $('#selectAllClientsBtn').trigger('click') : $('#clearClientsBtn').trigger('click');
});

$('#selectAllClientsBtn').on('click', () => {
    allLoadedClients.forEach(c => { if (!selectedClientIds.includes(c.id)) selectedClientIds.push(c.id); });
    $('.client-check').prop('checked', true);
    updateModalCount();
});

$('#clearClientsBtn').on('click', () => {
    selectedClientIds = [];
    $('.client-check').prop('checked', false);
    updateModalCount();
});

function updateModalCount() {
    const n = selectedClientIds.length;
    $('#modalSelectedCount, #modalFooterCount').text(`${n} client${n !== 1 ? 's' : ''} selected`);
}

$('#confirmPickerBtn').on('click', function () {
    $('#clientPickerModal').modal('hide');
    const n = selectedClientIds.length;
    $('#selectedCount').text(n > 0 ? `${n} client${n !== 1 ? 's' : ''} selected` : 'No clients selected');
    const tags = $('#selectedClientsTags').empty();
    allLoadedClients.filter(c => selectedClientIds.includes(c.id)).slice(0, 12).forEach(c => {
        tags.append(`<span class="mkt-tag">${c.fullname || c.email}</span>`);
    });
    if (n > 12) tags.append(`<span class="mkt-tag">+${n - 12} more</span>`);
    updateSummary();
});

// ── External preview ──────────────────────────────────────────────────────────
$('#previewExternalBtn').on('click', function () {
    const emails = parseEmails($('#external_emails').val());
    $('#externalCount').text(emails.length > 0 ? `${emails.length} valid email(s)` : 'No valid emails found');
    updateSummary();
});

function parseEmails(raw) {
    const emails = [], re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    raw.split(/[\r\n,]+/).forEach(line => {
        line = line.trim();
        const m = line.match(/<([^>]+)>/);
        const email = (m ? m[1] : line).toLowerCase().trim();
        if (re.test(email) && !emails.includes(email)) emails.push(email);
    });
    return emails;
}

// ── Send / Schedule ───────────────────────────────────────────────────────────
$('#sendCampaignBtn').on('click', function () {
    const name       = $('#campaign_name').val().trim();
    const templateId = $('#template_id').val();
    const type       = $('input[name="recipient_type"]:checked').val();
    const mode       = $('input[name="send_mode"]:checked').val();
    const schedAt    = $('#scheduled_at').val();

    if (!name)       { toast('warning', 'Campaign name is required'); return; }
    if (!templateId) { toast('warning', 'Please select a template'); return; }
    if (!type)       { toast('warning', 'Please select a recipient type'); return; }
    if (type === 'specific_clients' && selectedClientIds.length === 0) { toast('warning', 'Please pick at least one client'); return; }
    if (type === 'external' && parseEmails($('#external_emails').val()).length === 0) { toast('warning', 'No valid emails in the list'); return; }
    if (mode === 'schedule' && !schedAt) { toast('warning', 'Please select a schedule date and time'); return; }

    const actionLabel = mode === 'schedule' ? 'Schedule' : 'Send';

    Swal.fire({
        title: `${actionLabel} Campaign?`,
        text: `"${name}" will be ${mode === 'schedule' ? 'scheduled for ' + $('#scheduled_at').val() : 'sent now'}.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: `Yes, ${actionLabel.toLowerCase()} it`,
        confirmButtonColor: '#ffbe00',
    }).then(result => {
        if (!result.isConfirmed) return;

        Swal.fire({ title: mode === 'schedule' ? 'Saving...' : 'Sending...', text: 'Please wait', allowOutsideClick: false, allowEscapeKey: false, didOpen: () => Swal.showLoading() });

        $.post(marketingCampaignsStoreUrl, {
            _token:          csrfToken,
            campaign_name:   name,
            template_id:     templateId,
            recipient_type:  type,
            send_mode:       mode,
            scheduled_at:    mode === 'schedule' ? schedAt : '',
            client_ids:      type === 'specific_clients' ? selectedClientIds : [],
            external_emails: type === 'external' ? $('#external_emails').val() : '',
        }, function (res) {
            Swal.close();
            if (res.status === 1) {
                Swal.fire({
                    icon: 'success',
                    title: mode === 'schedule' ? 'Campaign Scheduled!' : 'Campaign Sent!',
                    text: mode === 'schedule' ? `Scheduled for ${schedAt}` : `${res.count} email(s) sent successfully.`,
                    confirmButtonColor: '#ffbe00',
                }).then(() => window.location.href = marketingCampaignsUrl + '/' + res.campaign_id);
            } else {
                Swal.fire({ icon: 'error', title: 'Failed', text: res.message || 'Something went wrong' });
            }
        }).fail(() => {
            Swal.close();
            Swal.fire({ icon: 'error', title: 'Request Failed', text: 'Server error occurred' });
        });
    });
});
</script>
@endsection
