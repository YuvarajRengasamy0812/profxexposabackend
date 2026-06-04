@extends('dashboard.layouts.master')

@section('title', 'Email Templates')

@push('after-styles')
    <link rel="stylesheet" href="{{ asset('assets/dashboard/js/datatables/DataTables-1.10.18/css/dataTables.bootstrap4.min.css') }}">
    <style>
        .template-page .template-hero,
        .template-page .template-stat-row,
        .template-page .template-table-card > .card-header,
        .template-page .template-id-line {
            display: none !important;
        }

        .template-page .template-top-action {
            display: flex;
            justify-content: flex-end;
            margin: 1.5rem 0 1rem;
        }

        .template-page .template-table-card {
            border: 1px solid #e8eef8;
            border-radius: 8px;
        }

        .template-page .dataTables_wrapper {
            padding: 1rem;
        }

        .template-page table.dataTable {
            border-collapse: collapse !important;
            margin: 0 !important;
            width: 100% !important;
        }

        .template-page .dataTables_filter input,
        .template-page .dataTables_length select {
            border: 1px solid #d8e0ef;
            border-radius: 4px;
            height: 34px;
            padding: 4px 10px;
        }
    </style>
@endpush

@push('after-styles')
    <link href="{{ asset('assets/dashboard/js/summernote/dist/summernote.css') }}" rel="stylesheet">
    <style>
        .template-hero {
            background: linear-gradient(135deg, #1f2937 0%, #ffbe00 100%);
            border-radius: 8px;
            color: #fff;
            padding: 22px;
            margin-bottom: 18px;
            position: relative;
            overflow: hidden;
        }

        .template-page .gap-2,
        .template-modal .gap-2 {
            gap: .5rem;
        }

        .template-page .gap-3,
        .template-modal .gap-3 {
            gap: .75rem;
        }

        .template-page .me-1,
        .template-modal .me-1 {
            margin-right: .25rem;
        }

        .template-page .me-2,
        .template-modal .me-2 {
            margin-right: .5rem;
        }

        .template-page .ps-4 {
            padding-left: 1.5rem;
        }

        .template-page .fw-semibold,
        .template-modal .fw-semibold {
            font-weight: 600;
        }

        .template-page .fs-12 {
            font-size: .75rem;
        }

        .template-page .fs-13 {
            font-size: .8125rem;
        }

        .template-hero:after {
            content: "";
            position: absolute;
            right: -80px;
            top: -90px;
            width: 230px;
            height: 230px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .16);
        }

        .template-hero-title {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .template-hero-sub {
            opacity: .86;
            font-size: .88rem;
        }

        .template-stat {
            background: #fff;
            border: 1px solid #edf0f4;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, .05);
        }

        .template-stat-icon {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff7dc;
            color: #b77900;
            font-size: 1.05rem;
        }

        .template-table-card {
            border-radius: 8px;
            overflow: hidden;
        }

        .template-table th {
            font-size: .78rem;
            color: #475569;
            border-top: 0;
        }

        .template-table td {
            vertical-align: middle;
        }

        .template-actions .btn {
            width: 34px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 7px;
        }

        .template-status {
            border-radius: 999px;
            padding: 5px 10px;
            font-size: .72rem;
            font-weight: 700;
        }

        .template-status.active {
            background: #dcfce7;
            color: #166534;
        }

        .template-status.inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .template-modal .modal-content {
            border-radius: 8px;
            border: 0;
            overflow: hidden;
        }

        .template-modal .modal-header {
            background: #f8fafc;
            border-bottom: 1px solid #edf0f4;
        }

        .template-modal .note-editor.note-frame {
            border-radius: 8px;
            border-color: #d8dde6;
        }

        .template-modal .note-editable {
            min-height: 320px;
            font-size: 14px;
        }

        .template-preview-frame {
            width: 100%;
            height: 560px;
            border: 0;
            background: #fff;
        }
    </style>
@endpush

@section('content')
<div class="main-content app-content template-page">
    <div class="container-fluid">
        <div class="page-header">
            <h1 class="page-title">Email Templates</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('adminHome') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Email Templates</li>
            </ol>
        </div>

        <div class="template-top-action">
            <button type="button" class="btn btn-primary js-create-template" style="border-radius:4px;">
                Add Template
            </button>
        </div>

        <div class="template-hero">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3" style="position:relative;z-index:1;">
                <div>
                    <div class="template-hero-title"><i class="fe fe-file-text me-2"></i>Email Template Builder</div>
                    <div class="template-hero-sub">Create reusable HTML templates for mail campaigns.</div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('marketingCampaigns') }}" class="btn btn-sm" style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.35);border-radius:7px;">
                        <i class="fe fe-send me-1"></i> Campaigns
                    </a>
                    <button type="button" class="btn btn-sm js-create-template" style="background:#fff;color:#b77900;font-weight:700;border-radius:7px;">
                        <i class="fe fe-plus me-1"></i> New Template
                    </button>
                </div>
            </div>
        </div>

        <div class="row mb-4 template-stat-row">
            <div class="col-md-4 mb-3">
                <div class="template-stat">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="template-stat-icon"><i class="fe fe-layers"></i></span>
                        <div>
                            <div class="h5 mb-0">{{ $stats['total'] ?? 0 }}</div>
                            <div class="text-muted fs-12">Total Templates</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="template-stat">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="template-stat-icon"><i class="fe fe-check-circle"></i></span>
                        <div>
                            <div class="h5 mb-0">{{ $stats['active'] ?? 0 }}</div>
                            <div class="text-muted fs-12">Active</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="template-stat">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="template-stat-icon"><i class="fe fe-slash"></i></span>
                        <div>
                            <div class="h5 mb-0">{{ $stats['inactive'] ?? 0 }}</div>
                            <div class="text-muted fs-12">Inactive</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card template-table-card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="fe fe-list me-2 text-primary"></i>Templates</h6>
                <button type="button" class="btn btn-primary btn-sm js-create-template" style="border-radius:7px;">
                    <i class="fe fe-plus me-1"></i> Add Template
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="templatesTable" class="table template-table table-hover mb-0 text-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $template)
                                <tr>
                                    <td class="ps-4 text-muted fs-12">{{ $template->id }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $template->name }}</div>
                                        <div class="text-muted fs-12 template-id-line">Template ID: {{ $template->id }}</div>
                                    </td>
                                    <td>
                                        <span class="template-status {{ $template->is_active ? 'active' : 'inactive' }}">
                                            {{ $template->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-muted fs-12">
                                        {{ $template->created_at ? date('d M Y, H:i', strtotime($template->created_at)) : '-' }}
                                    </td>
                                    <td class="text-center template-actions">
                                        <button type="button" class="btn btn-sm btn-outline-primary js-view-template" data-id="{{ $template->id }}" title="Preview">
                                            <i class="fe fe-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary js-edit-template" data-id="{{ $template->id }}" title="Edit">
                                            <i class="fe fe-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger js-delete-template" data-id="{{ $template->id }}" title="Delete">
                                            <i class="fe fe-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="fe fe-file-text text-muted" style="font-size:2.4rem;opacity:.4;"></i>
                                        <div class="fw-semibold mt-2 mb-1">No email templates yet</div>
                                        <div class="text-muted fs-13 mb-3">Create your first template to use it in campaigns.</div>
                                        <button type="button" class="btn btn-primary btn-sm js-create-template" style="border-radius:7px;">
                                            <i class="fe fe-plus me-1"></i> Create Template
                                        </button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade template-modal" id="templateModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <form method="post" id="templateForm">
                @csrf
                <input type="hidden" name="id" id="template_id">
                <div class="modal-header">
                    <h6 class="modal-title" id="templateModalTitle">Add Template</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-semibold">Template Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="template_name" name="name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select class="form-control" id="template_status" name="is_active" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Template HTML <span class="text-danger">*</span></label>
                        <textarea id="template_editor" name="template"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-white">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="templateSubmitBtn">
                        <i class="fe fe-save me-1"></i> Save Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade template-modal" id="templatePreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Template Preview</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <iframe id="templatePreviewFrame" class="template-preview-frame"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection

@push('after-scripts')
    <script src="{{ asset('assets/dashboard/js/datatables/DataTables-1.10.18/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/datatables/DataTables-1.10.18/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/summernote/dist/summernote.js') }}"></script>
    <script>
        (function ($) {
            const storeUrl = @json(route('email-templates.store'));
            const showUrl = @json(route('email-templates.show', ['id' => '__ID__']));
            const deleteUrl = @json(route('email-templates.destroy', ['id' => '__ID__']));
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            function notify(type, title, text) {
                if (window.Swal && Swal.fire) {
                    Swal.fire({ icon: type, title: title, text: text || '' });
                    return;
                }

                if (window.swal) {
                    swal(title, text || '', type);
                    return;
                }

                alert(title + (text ? '\n' + text : ''));
            }

            function confirmDelete(callback) {
                if (window.Swal && Swal.fire) {
                    Swal.fire({
                        title: 'Delete template?',
                        text: 'This template will be removed permanently.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Delete'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            callback();
                        }
                    });
                    return;
                }

                if (confirm('Delete this template?')) {
                    callback();
                }
            }

            function resetForm() {
                $('#templateForm')[0].reset();
                $('#template_id').val('');
                $('#template_status').val('1');
                $('#template_editor').summernote('code', '');
                $('#templateModalTitle').text('Add Template');
                $('#templateSubmitBtn').html('<i class="fe fe-save me-1"></i> Save Template');
            }

            function openEditor(template) {
                resetForm();

                if (template) {
                    $('#template_id').val(template.id);
                    $('#template_name').val(template.name);
                    $('#template_status').val(String(template.is_active));
                    $('#template_editor').summernote('code', template.template || '');
                    $('#templateModalTitle').text('Edit Template');
                    $('#templateSubmitBtn').html('<i class="fe fe-save me-1"></i> Update Template');
                }

                $('#templateModal').modal('show');
            }

            function writePreview(html) {
                const iframe = document.getElementById('templatePreviewFrame');
                const doc = iframe.contentDocument || iframe.contentWindow.document;
                doc.open();
                doc.write(html || '');
                doc.close();
                $('#templatePreviewModal').modal('show');
            }

            $(function () {
                $('#templatesTable').DataTable({
                    order: [[0, 'asc']],
                    pageLength: 10,
                    columnDefs: [
                        { visible: false, targets: [3] },
                        { orderable: false, targets: [4] }
                    ]
                });

                $('#template_editor').summernote({
                    height: 360,
                    dialogsInBody: true,
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'italic', 'underline', 'clear']],
                        ['fontname', ['fontname']],
                        ['fontsize', ['fontsize']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['insert', ['link', 'table', 'hr']],
                        ['view', ['fullscreen', 'codeview']]
                    ]
                });

                $('.js-create-template').on('click', function () {
                    openEditor(null);
                });

                $('.js-edit-template, .js-view-template').on('click', function () {
                    const id = $(this).data('id');
                    const isPreview = $(this).hasClass('js-view-template');

                    $.get(showUrl.replace('__ID__', id))
                        .done(function (template) {
                            if (isPreview) {
                                writePreview(template.template);
                            } else {
                                openEditor(template);
                            }
                        })
                        .fail(function (xhr) {
                            notify('error', 'Unable to load template', xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Please try again.');
                        });
                });

                $('#templateForm').on('submit', function (e) {
                    e.preventDefault();

                    const html = $('#template_editor').summernote('code');
                    const clean = html.replace(/<p>(\s|&nbsp;|<br>)*<\/p>/gi, '').trim();

                    if (!clean) {
                        notify('warning', 'Template content required', 'Please add email template HTML before saving.');
                        return;
                    }

                    $('#template_editor').val(html);
                    $('#templateSubmitBtn').prop('disabled', true).text('Saving...');

                    $.ajax({
                        url: storeUrl,
                        type: 'POST',
                        data: $(this).serialize(),
                        headers: { 'X-CSRF-TOKEN': csrfToken }
                    }).done(function (response) {
                        notify('success', response.message || 'Email template saved');
                        setTimeout(function () {
                            window.location.reload();
                        }, 700);
                    }).fail(function (xhr) {
                        const message = xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : 'Unable to save template.';
                        notify('error', 'Save failed', message);
                    }).always(function () {
                        $('#templateSubmitBtn').prop('disabled', false).html('<i class="fe fe-save me-1"></i> Save Template');
                    });
                });

                $('.js-delete-template').on('click', function () {
                    const id = $(this).data('id');

                    confirmDelete(function () {
                        $.ajax({
                            url: deleteUrl.replace('__ID__', id),
                            type: 'DELETE',
                            data: { _token: csrfToken }
                        }).done(function (response) {
                            notify('success', response.message || 'Email template deleted');
                            setTimeout(function () {
                                window.location.reload();
                            }, 700);
                        }).fail(function (xhr) {
                            notify('error', 'Delete failed', xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to delete template.');
                        });
                    });
                });

                $('#templateModal').on('hidden.bs.modal', resetForm);
            });
        })(jQuery);
    </script>
@endpush
