@extends('dashboard.layouts.master')

@section('title', 'Email Templates')

@push('after-styles')
    <link rel="stylesheet" href="{{ asset('assets/dashboard/js/datatables/DataTables-1.10.18/css/dataTables.bootstrap4.min.css') }}">
    <link href="{{ asset('assets/dashboard/js/summernote/dist/summernote.css') }}" rel="stylesheet">
    <style>
        .template-page .template-hero-actions { position: relative; z-index: 1; }
        .template-page .template-hero-actions .btn { border-radius: 10px; font-weight: 600; }
        .template-page .template-table th { color: #334155; font-size: .78rem; }
        .template-page .template-table td { vertical-align: middle; }
        .template-page .template-actions { display: inline-flex; justify-content: center; gap: .35rem; }
        .template-page .template-actions .btn { align-items: center; border-radius: 8px; display: inline-flex; height: 34px; justify-content: center; padding: 0; width: 36px; }
        .template-page .template-status { border-radius: 999px; display: inline-flex; font-size: .72rem; font-weight: 700; padding: 5px 10px; }
        .template-page .template-status.active { background: #dcfce7; color: #166534; }
        .template-page .template-status.inactive { background: #fee2e2; color: #991b1b; }
        .template-page .template-empty { align-items: center; color: #94a3b8; display: flex; flex-direction: column; min-height: 230px; justify-content: center; padding: 2rem 1rem; text-align: center; }
        .template-page .template-empty i { color: #cbd5e1; font-size: 2.8rem; margin-bottom: .8rem; }
        .template-page .template-empty-title { color: #0f172a; font-size: 1rem; font-weight: 700; margin-bottom: .35rem; }
        .template-modal .modal-content { border: 0; border-radius: 14px; box-shadow: 0 24px 60px rgba(15, 23, 42, .22); overflow: hidden; }
        .template-modal .modal-header { background: #f8fafc; border-bottom: 1px solid #e8eef8; }
        .template-modal .modal-footer { border-top: 1px solid #e8eef8; }
        .template-modal .form-control { border-color: #d8e0ef; border-radius: 9px; min-height: 38px; }
        .template-modal .form-control:focus { border-color: #c19d38; box-shadow: 0 0 0 2px rgba(193, 157, 56, .14); }
        .template-modal .note-editor.note-frame { border-color: #d8e0ef; border-radius: 10px; overflow: hidden; }
        .template-modal .note-editable { font-size: 14px; min-height: 320px; }
        .template-preview-frame { background: #fff; border: 0; height: 560px; width: 100%; }
        .template-page .mkt-stat-card .card-body { min-height: 74px; }
        @media (max-width: 767px) {
            .template-page .mkt-hero { padding: 1.4rem; }
            .template-page .template-hero-actions { width: 100%; }
            .template-page .template-hero-actions .btn { width: 100%; }
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

        <div class="mkt-hero">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div style="position:relative;z-index:1;">
                    <div class="mkt-hero-title"><i class="fe fe-file-text me-2"></i>Email Template Builder</div>
                    <div class="mkt-hero-sub">Create reusable HTML templates for campaigns and bulk email sends.</div>
                    <div class="mkt-hero-stats">
                        <div>
                            <div class="mkt-hero-stat-val">{{ $stats['total'] ?? 0 }}</div>
                            <div class="mkt-hero-stat-lbl">Total</div>
                        </div>
                        <div>
                            <div class="mkt-hero-stat-val">{{ $stats['active'] ?? 0 }}</div>
                            <div class="mkt-hero-stat-lbl">Active</div>
                        </div>
                        <div>
                            <div class="mkt-hero-stat-val">{{ $stats['inactive'] ?? 0 }}</div>
                            <div class="mkt-hero-stat-lbl">Inactive</div>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 align-items-center flex-wrap template-hero-actions">
                    <a href="{{ route('marketingCampaigns') }}" class="btn btn-sm" style="background:rgba(255,255,255,.18);color:#fff;border:1px solid rgba(255,255,255,.35);">
                        <i class="fe fe-send me-1"></i> Campaigns
                    </a>
                    <button type="button" class="btn btn-sm js-create-template" style="background:#fff;color:#0f766e;border:none;">
                        <i class="fe fe-plus me-1"></i> New Template
                    </button>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-4">
                <div class="card mkt-stat-card border-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="mkt-stat-icon mkt-stat-icon-primary"><i class="fe fe-layers"></i></div>
                        <div>
                            <div class="mkt-stat-val">{{ $stats['total'] ?? 0 }}</div>
                            <div class="mkt-stat-lbl">Total Templates</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-4">
                <div class="card mkt-stat-card border-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="mkt-stat-icon mkt-stat-icon-success"><i class="fe fe-check-circle"></i></div>
                        <div>
                            <div class="mkt-stat-val">{{ $stats['active'] ?? 0 }}</div>
                            <div class="mkt-stat-lbl">Active</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card mkt-stat-card border-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="mkt-stat-icon mkt-stat-icon-danger"><i class="fe fe-slash"></i></div>
                        <div>
                            <div class="mkt-stat-val">{{ $stats['inactive'] ?? 0 }}</div>
                            <div class="mkt-stat-lbl">Inactive</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mkt-table-card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="mb-0 fw-semibold"><i class="fe fe-list me-2 text-primary"></i>Templates</h6>
                <button type="button" class="btn btn-primary btn-sm js-create-template" style="border-radius:8px;">
                    <i class="fe fe-plus me-1"></i> Add Template
                </button>
            </div>
            <div class="card-body p-0">
                @if($templates->isNotEmpty())
                    <div class="table-responsive">
                        <table id="templatesTable" class="table mkt-table template-table table-hover mb-0 text-nowrap">
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
                                @foreach($templates as $template)
                                    <tr>
                                        <td class="ps-4 text-muted fs-12">{{ $template->id }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $template->name }}</div>
                                            <div class="text-muted fs-12">Template ID: {{ $template->id }}</div>
                                        </td>
                                        <td>
                                            <span class="template-status {{ $template->is_active ? 'active' : 'inactive' }}">
                                                {{ $template->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-muted fs-12">
                                            {{ $template->created_at ? date('d M Y, H:i', strtotime($template->created_at)) : '-' }}
                                        </td>
                                        <td class="text-center">
                                            <span class="template-actions">
                                                <button type="button" class="btn btn-sm btn-outline-primary js-view-template" data-id="{{ $template->id }}" title="Preview">
                                                    <i class="fe fe-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary js-edit-template" data-id="{{ $template->id }}" title="Edit">
                                                    <i class="fe fe-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger js-delete-template" data-id="{{ $template->id }}" title="Delete">
                                                    <i class="fe fe-trash"></i>
                                                </button>
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="template-empty">
                        <i class="fe fe-file-text"></i>
                        <div class="template-empty-title">No email templates yet</div>
                        <div class="fs-13 mb-3">Create your first template to use it in campaigns.</div>
                        <button type="button" class="btn btn-primary btn-sm js-create-template" style="border-radius:8px;">
                            <i class="fe fe-plus me-1"></i> Create Template
                        </button>
                    </div>
                @endif
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
                    <div class="row g-3">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-semibold">Template Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="template_name" name="name" required maxlength="255">
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
                        <textarea id="template_editor" name="template" class="form-control" rows="14"></textarea>
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
            const hasTemplates = @json($templates->isNotEmpty());
            const storeUrl = @json(route('email-templates.store'));
            const showUrl = @json(route('email-templates.show', ['id' => '__ID__']));
            const deleteUrl = @json(route('email-templates.destroy', ['id' => '__ID__']));
            const csrfToken = $('meta[name="csrf-token"]').attr('content') || @json(csrf_token());

            function notify(type, title, text) {
                if (window.Swal && Swal.fire) {
                    Swal.fire({ icon: type, title: title, text: text || '', confirmButtonColor: '#c19d38' });
                    return;
                }
                if (window.swal) {
                    swal(title, text || '', type);
                    return;
                }
                alert(title + (text ? '\n' + text : ''));
            }

            function showModal(selector) {
                const modal = $(selector);
                if ($.fn.modal) {
                    modal.modal('show');
                    return;
                }
                modal.show().addClass('show in').attr('aria-hidden', 'false');
                $('body').addClass('modal-open').append('<div class="modal-backdrop fade show in template-fallback-backdrop"></div>');
            }

            function hideModal(selector) {
                const modal = $(selector);
                if ($.fn.modal) {
                    modal.modal('hide');
                    return;
                }
                modal.hide().removeClass('show in').attr('aria-hidden', 'true');
                $('.template-fallback-backdrop').remove();
                $('body').removeClass('modal-open');
            }

            function initEditor() {
                if (!$.fn.summernote) return;
                const editor = $('#template_editor');
                if (editor.next('.note-editor').length) return;
                editor.summernote({
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
            }

            function setEditorCode(html) {
                if ($.fn.summernote && $('#template_editor').next('.note-editor').length) {
                    $('#template_editor').summernote('code', html || '');
                } else {
                    $('#template_editor').val(html || '');
                }
            }

            function getEditorCode() {
                if ($.fn.summernote && $('#template_editor').next('.note-editor').length) {
                    return $('#template_editor').summernote('code');
                }
                return $('#template_editor').val();
            }

            function resetForm() {
                const form = $('#templateForm')[0];
                if (form) form.reset();
                $('#template_id').val('');
                $('#template_status').val('1');
                setEditorCode('');
                $('#templateModalTitle').text('Add Template');
                $('#templateSubmitBtn').prop('disabled', false).html('<i class="fe fe-save me-1"></i> Save Template');
            }

            function openEditor(template) {
                initEditor();
                resetForm();
                if (template) {
                    $('#template_id').val(template.id);
                    $('#template_name').val(template.name || '');
                    $('#template_status').val(String(template.is_active ? 1 : 0));
                    setEditorCode(template.template || '');
                    $('#templateModalTitle').text('Edit Template');
                    $('#templateSubmitBtn').html('<i class="fe fe-save me-1"></i> Update Template');
                }
                showModal('#templateModal');
                setTimeout(function () { $('#template_name').trigger('focus'); }, 150);
            }

            function writePreview(html) {
                const iframe = document.getElementById('templatePreviewFrame');
                const doc = iframe.contentDocument || iframe.contentWindow.document;
                doc.open();
                doc.write(html || '<div style="font-family:Arial;padding:32px;color:#64748b;">No preview content.</div>');
                doc.close();
                showModal('#templatePreviewModal');
            }

            function validationMessage(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    const firstKey = Object.keys(errors)[0];
                    if (firstKey && errors[firstKey] && errors[firstKey][0]) return errors[firstKey][0];
                }
                return xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Please try again.';
            }

            $(function () {
                initEditor();

                if (hasTemplates && $.fn.DataTable) {
                    $('#templatesTable').DataTable({
                        order: [[0, 'desc']],
                        pageLength: 10,
                        columnDefs: [{ orderable: false, targets: [4] }],
                        language: { search: '', searchPlaceholder: 'Search templates...' }
                    });
                }

                $(document).on('click', '.js-create-template', function () {
                    openEditor(null);
                });

                $(document).on('click', '.js-edit-template, .js-view-template', function () {
                    const id = $(this).data('id');
                    const isPreview = $(this).hasClass('js-view-template');

                    $.get(showUrl.replace('__ID__', id))
                        .done(function (template) {
                            if (isPreview) writePreview(template.template);
                            else openEditor(template);
                        })
                        .fail(function (xhr) {
                            notify('error', 'Unable to load template', validationMessage(xhr));
                        });
                });

                $('#templateForm').on('submit', function (e) {
                    e.preventDefault();

                    const html = getEditorCode();
                    const clean = (html || '').replace(/<p>(\s|&nbsp;|<br>)*<\/p>/gi, '').replace(/<[^>]*>/g, '').replace(/&nbsp;/g, '').trim();

                    if (!$('#template_name').val().trim()) {
                        notify('warning', 'Template name required', 'Please enter a template name.');
                        return;
                    }

                    if (!clean && !(html || '').trim()) {
                        notify('warning', 'Template content required', 'Please add email template HTML before saving.');
                        return;
                    }

                    $('#template_editor').val(html);
                    $('#templateSubmitBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Saving...');

                    $.ajax({
                        url: storeUrl,
                        type: 'POST',
                        data: $(this).serialize(),
                        headers: { 'X-CSRF-TOKEN': csrfToken }
                    }).done(function (response) {
                        notify('success', response.message || 'Email template saved');
                        setTimeout(function () { window.location.reload(); }, 650);
                    }).fail(function (xhr) {
                        notify('error', 'Save failed', validationMessage(xhr));
                    }).always(function () {
                        $('#templateSubmitBtn').prop('disabled', false).html('<i class="fe fe-save me-1"></i> Save Template');
                    });
                });

                $(document).on('click', '.js-delete-template', function () {
                    const id = $(this).data('id');
                    const runDelete = function () {
                        $.ajax({
                            url: deleteUrl.replace('__ID__', id),
                            type: 'DELETE',
                            data: { _token: csrfToken }
                        }).done(function (response) {
                            notify('success', response.message || 'Email template deleted');
                            setTimeout(function () { window.location.reload(); }, 650);
                        }).fail(function (xhr) {
                            notify('error', 'Delete failed', validationMessage(xhr));
                        });
                    };

                    if (window.Swal && Swal.fire) {
                        Swal.fire({
                            title: 'Delete template?',
                            text: 'This template will be removed permanently.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'Delete'
                        }).then(function (result) { if (result.isConfirmed) runDelete(); });
                        return;
                    }

                    if (confirm('Delete this template?')) runDelete();
                });

                $(document).on('click', '.template-modal [data-dismiss="modal"], .template-modal .close', function () {
                    hideModal('#' + $(this).closest('.modal').attr('id'));
                });

                $('#templateModal').on('hidden.bs.modal', resetForm);
            });
        })(jQuery);
    </script>
@endpush