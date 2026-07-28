@extends('dashboard.layouts.master')
@section('title', 'Marketing Emails')

@push('after-styles')
    <link rel="stylesheet" href="{{ asset('assets/dashboard/js/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/dashboard/js/select2-bootstrap-theme/dist/select2-bootstrap.4.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/dashboard/js/datatables/DataTables-1.10.18/css/dataTables.bootstrap4.min.css') }}">
    <style>
        .bulk-email-page .mail-tabs { border-bottom: 1px solid #e8eef8; gap: .5rem; }
        .bulk-email-page .mail-tabs .nav-link { border: 0; border-radius: 8px 8px 0 0; color: #64748b; font-weight: 600; padding: .8rem 1rem; }
        .bulk-email-page .mail-tabs .nav-link.active { background: #fff7dc; color: #0f766e; }
        .bulk-email-page .form-control,
        .bulk-email-page .form-select { border-color: #d8e0ef; border-radius: 9px; min-height: 38px; }
        .bulk-email-page .select2-container--bootstrap .select2-selection { border-color: #d8e0ef; border-radius: 9px; min-height: 38px; }
        .bulk-email-page .mail-action-row { display: grid; gap: 14px; grid-template-columns: minmax(180px, 240px) minmax(170px, 220px) minmax(260px, 1fr) auto; }
        .bulk-email-page .import-action-row { display: grid; gap: 14px; grid-template-columns: minmax(180px, 240px) minmax(260px, 1fr) auto auto; }
        .bulk-email-page label { color: #001b3f; font-size: .8125rem; font-weight: 600; margin-bottom: .4rem; }
        .bulk-email-page .empty-import { color: #94a3b8; padding: 2rem; text-align: center; }
        @media (max-width: 991px) {
            .bulk-email-page .mail-action-row,
            .bulk-email-page .import-action-row { grid-template-columns: 1fr; }
        }
    </style>
@endpush

@section('content')
<div class="main-content app-content bulk-email-page">
    <div class="container-fluid">
        <div class="page-header">
            <h1 class="page-title">Marketing Emails</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('adminHome') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Marketing Emails</li>
            </ol>
        </div>

        <div class="mkt-hero">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div style="position:relative;z-index:1;">
                    <div class="mkt-hero-title"><i class="fe fe-mail me-2"></i>Bulk Email Sender</div>
                    <div class="mkt-hero-sub">Send active templates to registered clients or imported email lists.</div>
                </div>
                <div class="d-flex gap-2 align-items-center flex-wrap" style="position:relative;z-index:1;">
                    <a href="{{ route('email-templates') }}" class="btn btn-sm" style="background:rgba(255,255,255,.18);color:#fff;border:1px solid rgba(255,255,255,.35);border-radius:10px;font-weight:600;">
                        <i class="fe fe-file-text me-1"></i> Templates
                    </a>
                    <a href="{{ route('marketingCampaigns') }}" class="btn btn-sm" style="background:#fff;color:#0f766e;border:none;border-radius:10px;font-weight:600;">
                        <i class="fe fe-send me-1"></i> Campaigns
                    </a>
                </div>
            </div>
        </div>

        <div class="card mkt-table-card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
                <ul class="nav nav-tabs card-header-tabs mail-tabs" id="emailTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-toggle="tab" data-target="#emails" type="button">
                            <i class="fe fe-users me-1"></i> Registered Clients
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-toggle="tab" data-target="#import" type="button">
                            <i class="fe fe-file-text me-1"></i> Import Email
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="emails">
                        <form method="post">
                            @csrf
                            <div class="mail-action-row align-items-end mb-4">
                                <div>
                                    <label class="form-label">Select Template</label>
                                    <select class="form-select" name="template" id="template" required>
                                        <option value="" hidden>Select template</option>
                                        @foreach($templates as $template)
                                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Client Selection</label>
                                    <select class="form-select" name="client_select_type" id="client_select_type">
                                        <option value="" hidden>Select clients</option>
                                        <option value="all">All</option>
                                        <option value="users">Specific Users</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Users</label>
                                    <select class="form-select" name="client_users[]" id="client_users" multiple></select>
                                </div>
                                <div class="d-grid">
                                    <button type="button" id="sendBulkEmail" class="btn btn-primary" style="border-radius:9px;">
                                        <i class="fe fe-send me-1"></i> Send Email
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="ajaxDatatable" class="ajaxDataTable table mkt-table table-hover text-nowrap w-100">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:46px;"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                                            <th>#CID</th>
                                            <th>Name / Email</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="import">
                        <form id="importEmailForm" enctype="multipart/form-data">
                            <div class="import-action-row align-items-end mb-4">
                                <div>
                                    <label class="form-label">Select Template</label>
                                    <select class="form-select" name="import_template" id="import_template" required>
                                        <option value="" hidden>Select template</option>
                                        @foreach($templates as $template)
                                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Upload Excel / CSV File</label>
                                    <input type="file" class="form-control" name="email_file" id="email_file" accept=".xlsx,.xls,.csv">
                                </div>
                                <div class="d-grid">
                                    <button type="button" id="importEmailsBtn" class="btn btn-outline-primary" style="border-radius:9px;">
                                        <i class="fe fe-file-text me-1"></i> Import
                                    </button>
                                </div>
                                <div class="d-grid">
                                    <button type="button" id="sendBulkEmailImport" class="btn btn-primary" style="border-radius:9px;">
                                        <i class="fe fe-send me-1"></i> Send Email
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table mkt-table table-hover" id="importedEmailsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:90px;">#</th>
                                        <th>Email</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="2" class="empty-import">No data imported yet</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('after-scripts')
    <script src="{{ asset('assets/dashboard/js/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/datatables/DataTables-1.10.18/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/datatables/DataTables-1.10.18/js/dataTables.bootstrap4.min.js') }}"></script>
    <script>
    if (typeof window.Swal === 'undefined') {
        window.Swal = {
            fire(options) {
                const title = typeof options === 'string' ? options : (options.title || '');
                const text = typeof options === 'string' ? '' : (options.text || '');
                if (window.swal) swal(title, text, options.icon || options.type || '');
                else alert((title ? title + '\n' : '') + text);
                return { then(callback) { if (callback) callback({ isConfirmed: true }); } };
            },
            close() {},
            showLoading() {}
        };
    }

    const csrfToken = $('meta[name="csrf-token"]').attr('content') || @json(csrf_token());
    const bulkEmailClientsUrl = @json(route('marketingCampaignsGetClients'));
    const bulkEmailSendUrl = @json(route('bulk-email-send.send'));
    const importEmailsUrl = @json(route('import-emails'));
    const bulkEmailImportSendUrl = @json(route('bulk-email-import-send'));
    let selectedEmailsGlobal = [];
    let importedEmailsGlobal = [];
    let clientsById = {};

    function unique(values) {
        return Array.from(new Set(values.filter(Boolean)));
    }

    function toast(type, title, text) {
        Swal.fire({ icon: type, title: title, text: text || '', confirmButtonColor: '#c19d38' });
    }

    function escapeHtml(value) {
        return $('<div>').text(value || '').html();
    }

    function setSelectedEmails(emails) {
        selectedEmailsGlobal = unique(emails);
    }

    function resetImportedTable(message) {
        $('#importedEmailsTable tbody').html(`<tr><td colspan="2" class="empty-import">${escapeHtml(message || 'No data imported yet')}</td></tr>`);
    }

    if ($.fn.select2) {
        $('#client_users').select2({ placeholder: 'Select Users', allowClear: true, width: '100%', theme: 'bootstrap' });
    }

    window.dTtable = $('.ajaxDataTable').DataTable({
        dom: '<"row mb-2"<"col-md-6"l><"col-md-6"f>><"row"<"col"t>><"row mt-2"<"col-md-6"i><"col-md-6"p>>',
        order: [[1, 'desc']],
        pageLength: 25,
        ajax: {
            url: bulkEmailClientsUrl,
            type: 'GET',
            data: { limit: 'all' },
            dataSrc: function (json) {
                clientsById = {};
                (json.data || []).forEach(function (row) { clientsById[row.id] = row; });
                return json.data || [];
            }
        },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    const checked = selectedEmailsGlobal.includes(row.email) ? 'checked' : '';
                    return `<input type="checkbox" class="row-check form-check-input" data-id="${row.id}" value="${escapeHtml(row.email)}" ${checked}>`;
                }
            },
            { data: 'id', name: 'id' },
            {
                data: 'email',
                name: 'email',
                render: function (data, type, row) {
                    return `<div class="d-flex align-items-center gap-2"><div class="mkt-stat-icon mkt-stat-icon-info" style="width:32px;height:32px;border-radius:8px;font-size:.9rem;"><i class="fe fe-users"></i></div><div><div class="fw-semibold fs-13">${escapeHtml(row.fullname || 'Unnamed Client')}</div><div class="fs-12 text-muted">${escapeHtml(row.email)}</div></div></div>`;
                }
            }
        ]
    });

    $('button[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        const target = $(e.target).data('target');
        if (target === '#emails') {
            $('#importEmailForm')[0].reset();
            resetImportedTable();
            importedEmailsGlobal = [];
        } else if (target === '#import') {
            $('#emails form')[0].reset();
            $('#client_users').val(null).trigger('change');
            $('.row-check, #selectAll').prop('checked', false);
            setSelectedEmails([]);
        }
    });

    $(document).on('change', '#client_select_type', function () {
        const type = this.value || '';
        const rows = window.dTtable.rows().data().toArray();

        if (type === 'all') {
            const emails = rows.map(row => row.email);
            setSelectedEmails(emails);
            $('.row-check, #selectAll').prop('checked', true);
            $('#client_users').empty().val(null).trigger('change');
            return;
        }

        $('.row-check, #selectAll').prop('checked', false);
        setSelectedEmails([]);

        if (type === 'users') {
            const options = rows.map(row => `<option value="${row.id}" data-client-email="${escapeHtml(row.email)}">${escapeHtml(row.fullname || row.email)}</option>`).join('');
            $('#client_users').html(options).trigger('change');
        } else {
            $('#client_users').empty().trigger('change');
        }
    });

    $(document).on('change', '#client_users', function () {
        const users = $(this).val() || [];
        const selectedType = $('#client_select_type').val();
        if (selectedType !== 'users') return;

        const emails = users.map(id => clientsById[id] ? clientsById[id].email : '').filter(Boolean);
        setSelectedEmails(emails);
        $('.row-check').prop('checked', false);
        users.forEach(function (id) {
            $(`.row-check[data-id="${id}"]`).prop('checked', true);
        });
    });

    $(document).on('change', '.row-check', function () {
        const selectedType = $('#client_select_type').val();
        const email = $(this).val();
        const id = String($(this).data('id'));

        if ($(this).is(':checked')) {
            setSelectedEmails(selectedEmailsGlobal.concat([email]));
        } else {
            setSelectedEmails(selectedEmailsGlobal.filter(item => item !== email));
        }

        if (selectedType === 'users') {
            let values = $('#client_users').val() || [];
            values = $(this).is(':checked') ? unique(values.concat([id])) : values.filter(value => value !== id);
            $('#client_users').val(values).trigger('change.select2');
        }
    });

    $(document).on('change', '#selectAll', function () {
        const rows = window.dTtable.rows({ search: 'applied' }).data().toArray();
        if ($(this).is(':checked')) {
            setSelectedEmails(selectedEmailsGlobal.concat(rows.map(row => row.email)));
            $('.row-check').prop('checked', true);
            return;
        }
        const visibleEmails = rows.map(row => row.email);
        setSelectedEmails(selectedEmailsGlobal.filter(email => !visibleEmails.includes(email)));
        $('.row-check').prop('checked', false);
    });

    window.dTtable.on('draw', function () {
        $('.row-check').each(function () {
            $(this).prop('checked', selectedEmailsGlobal.includes($(this).val()));
        });
    });

    $(document).on('click', '#sendBulkEmail', function () {
        const type = $('#client_select_type').val();
        const template = $('#template').val();

        if (!template) { toast('warning', 'No template selected', 'Please select an active template.'); return; }
        if (!type) { toast('warning', 'No client type selected', 'Please select all or specific users.'); return; }
        if (selectedEmailsGlobal.length === 0) { toast('warning', 'No email selected', 'Please select at least one email.'); return; }

        Swal.fire({ title: 'Sending Emails...', text: 'Please wait while emails are being sent', allowOutsideClick: false, allowEscapeKey: false, didOpen: () => Swal.showLoading() });

        $.ajax({
            url: bulkEmailSendUrl,
            type: 'POST',
            data: { _token: csrfToken, emails: selectedEmailsGlobal, template: template },
            success: function (response) {
                Swal.close();
                if (response.status == 1) {
                    Swal.fire({ icon: 'success', title: 'Email Sent Successfully', text: 'Total Sent: ' + response.count, confirmButtonColor: '#c19d38' }).then(() => location.reload());
                } else {
                    toast('error', 'Failed to Send', response.message || 'Something went wrong');
                }
            },
            error: function (xhr) {
                Swal.close();
                toast('error', 'Request Failed', xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Server error occurred');
            }
        });
    });

    $(document).on('click', '#importEmailsBtn', function () {
        const file = $('#email_file')[0].files[0];
        if (!file) { toast('warning', 'No file selected', 'Please select an Excel or CSV file.'); return; }

        const formData = new FormData();
        formData.append('email_file', file);

        $.ajax({
            url: importEmailsUrl,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (res) {
                const tbody = $('#importedEmailsTable tbody').empty();
                importedEmailsGlobal = unique(res.emails || []);

                if (importedEmailsGlobal.length === 0) {
                    resetImportedTable('No emails found');
                    return;
                }

                importedEmailsGlobal.forEach(function (email, index) {
                    tbody.append(`<tr><td>${index + 1}</td><td>${escapeHtml(email)}</td></tr>`);
                });
            },
            error: function (xhr) {
                toast('error', 'File import failed', xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Please check the file and try again.');
            }
        });
    });

    $(document).on('click', '#sendBulkEmailImport', function () {
        const importTemplate = $('#import_template').val();
        if (!importTemplate) { toast('warning', 'No template selected', 'Please select an active template.'); return; }
        if (importedEmailsGlobal.length === 0) { toast('warning', 'No imported emails found', 'Import a file before sending.'); return; }

        Swal.fire({ title: 'Sending Emails...', text: 'Please wait while emails are being sent', allowOutsideClick: false, allowEscapeKey: false, didOpen: () => Swal.showLoading() });

        $.ajax({
            url: bulkEmailImportSendUrl,
            type: 'POST',
            data: { _token: csrfToken, emails: importedEmailsGlobal, import_template: importTemplate },
            success: function (response) {
                Swal.close();
                if (response.status == 1) {
                    Swal.fire({ icon: 'success', title: 'Email Sent Successfully', text: 'Total Sent: ' + response.count, confirmButtonColor: '#c19d38' }).then(() => location.reload());
                } else {
                    toast('error', 'Failed to Send', response.message || 'Something went wrong');
                }
            },
            error: function (xhr) {
                Swal.close();
                toast('error', 'Request Failed', xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Server error occurred');
            }
        });
    });
    </script>
@endpush