@extends('dashboard.layouts.master')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">
@section('content')
    <style>
        tr.inactive {
            background-color: #f8f9fa;
            color: #6c757d;
            opacity: 0.6;
        }
        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
        
        .note-editor.note-frame {
            height: 300px !important;
            display: flex;
            flex-direction: column;
        }

        .note-editor .note-editable {
            flex: 1;
            overflow-y: auto !important;
            max-height: 100% !important;
        }
    </style>
    <div class="modal fade" id="addTemplateModal" tabindex="-1" aria-labelledby="addTemplateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="emailTemplateModelLabel">Add Template</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" id="emailTemplateForm">
                    @csrf
                    <input type="hidden" name="id" id="template_id">
                    <input type="hidden" name="emailTemplate_update" value="1">
                    <div class="modal-body">
                        <div class="row gy-2">
                            <div class="row">
                                <div class="col-6">
                                    <label for="input-label" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="template_name" name="name" required>
                                </div>
                                <div class="col-6">
                                    <label for="input-file" class="form-label">Status</label>
                                    <select class="form-select" required name="is_active">
                                        <option value="" selected disabled hidden></option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="input-file" class="form-label">Template</label>
                                <textarea id="summernote" name="template"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="submitBtn">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="previewTemplateModal" tabindex="-1">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Preview Template</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe id="templatePreviewFrame" style="width:100%; height:500px; border:0;"></iframe>
                </div>
            </div>
        </div>
    </div>
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="page-title">Email Template</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Email Templates</li>
                </ol>
            </div>
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTemplateModal">
                    Add Template
                </button>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tableEmailtemplates" class="ajaxDataTable table table-bordered text-nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#summernote').summernote({
                height: 300,
                maxHeight: 300,
                codeviewFilter: false,
                codeviewIframeFilter: false,
                disableDragAndDrop: true, // ✅ IMPORTANT

                toolbar: [
                    ['view', ['codeview']]
                ],

                callbacks: {
                    onInit: function () {
                        let editor = $('.note-editable');

                        // Disable typing
                        editor.attr('contenteditable', false);

                        // Disable drag & drop (editor level)
                        editor.on('dragenter dragover dragleave drop', function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            return false;
                        });

                        // Disable paste
                        editor.on('paste', function (e) {
                            e.preventDefault();
                            return false;
                        });
                    },

                    // Block image upload completely
                    onImageUpload: function () {
                        return false;
                    },

                    // Extra safety: block drop from Summernote API
                    onDrop: function (e) {
                        e.preventDefault();
                        return false;
                    },

                    onChange: function (contents) {
                        let clean = contents.replace(/<p>(\s|&nbsp;|<br>)*<\/p>/gi, '').trim();

                        if (!clean) {
                            $('#summernote').summernote('code', '');
                        }
                    }
                }
            });

            // 🔒 GLOBAL fallback (prevents browser-level drop)
            $(document).on('drop dragover', function (e) {
                e.preventDefault();
            });
        });

        $('#addTemplateModal').on('hidden.bs.modal', function () {
            $("#template_id").val('');
            $("#template_name").val('');
            $("#addTemplateModal input:not([name='_token']),#addTemplateModal select").val("").trigger("change");
            $("#emailTemplateModelLabel").text('Add Template');
            $('#summernote').summernote('reset');
            $("#submitBtn").text("Submit");
        });

        $('#addTemplateModal').on('shown.bs.modal', function () {
            if($("#template_id").val() == "")
            {
                $("#template_id").val('');
                $("#template_name").val('');
                $("#addTemplateModal input:not([name='_token']),#addTemplateModal select").val("").trigger("change");
                $("#emailTemplateModelLabel").text('Add Template');
                $('#summernote').summernote('reset');
                $("#submitBtn").text("Submit");
            }
        });
        $(document).ready(function() {
            window.dTtable = $('#tableEmailtemplates').DataTable({
                // order: [[0, "desc"]],
                "ajax": {
                    "url": "/admin/ajax",
                    "type": "GET",
                    data: {
                        action: 'getEmailTemplate',
                    },
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        render: function (data, type, row) {
                            return `
                                <span class="badge ${row.is_active == 1 ? 'bg-success' : 'bg-danger'}">
                                    ${row.is_active == 1 ? 'Active' : 'Inactive'}
                                </span>
                            `;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (row) {
                            return `
                            <span class="view-template" style="cursor:pointer;" data-modal-label="Email template" data-id="${row.id}" data-enc="' + row_data.enc_id +
                            '"><span class="badge text-danger" data-bs-toggle="tooltip" title="View Template"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-eye"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg></span></span>

                            <span class="edit-template" style="cursor:pointer;" data-modal-label="Edit Email template" data-id="${row.id}" data-enc="' + row_data.enc +
                                '"><span class="badge text-danger" data-bs-toggle="tooltip" title="Edit template"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-edit text-secondary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg></span></span>
                            
                            <span class="delete-template" style="cursor:pointer;"data-id="${row.id}">
                                <span class="badge text-danger"
                                    data-bs-toggle="tooltip"
                                    title="Delete template">
                                    
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        width="16" height="16"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-trash text-danger">
                                        
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M4 7h16"/>
                                        <path d="M10 11v6"/>
                                        <path d="M14 11v6"/>
                                        <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/>
                                        <path d="M9 7v-3h6v3"/>
                                    </svg>
                                    
                                </span>
                            </span>  
                            `;
                        }
                    }
                ],
                "createdRow": function(row, data, dataIndex) {
                    if (data.is_active == 0) {
                        $(row).addClass('inactive');
                    }
                }
            });
        });

        $("#emailTemplateForm").submit(function(e) {
            e.preventDefault();
            $('#summernote').summernote('code', $('#summernote').summernote('code'));
            $.ajax({
                url: "/admin/api/ajax",
                type: "POST",
                data: $(this).serialize(),
                success: function(data) {
                    if (data.status == "true" || data.trim() == "true") {
                        swal.fire({
                            icon: "success",
                            title: data.message ?? "Email Template Successfully Updated"
                        }).then((val) => {
                            location.reload();
                        });
                    } else {
                        swal.fire({
                            icon: "error",
                            title: "Error:",
                            text: data.error
                        })
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    swal.fire({
                        icon: "error",
                        title: "Error:",
                        text: response.error
                    });
                }
            });
        });

        $(document).on("click", ".edit-template", function (e) {
            e.preventDefault();

            let id = $(this).data("id");
            
            let modalLabel = $(this).data("modal-label");

            $.ajax({
                url: "/admin/api/ajax",
                type: "GET",
                data: {
                    get_emailtemplate: true,
                    id: id
                },
                success: function(data) {
                    if (data == "false") {
                        swal.fire({
                            icon: "error",
                            title: "Something went wrong",
                            text: "Please try again later or contact support.",
                        });
                    } else {
                        $("#emailTemplateForm #template_id").val(data.id);
                        $("#emailTemplateForm #template_name").val(data.name);
                        $("#emailTemplateForm #summernote").summernote("code", data.template);
                        $("#emailTemplateForm [name='is_active']").val(data.is_active).trigger("change");
                        $("#emailTemplateModelLabel").text(modalLabel);
                        $("#submitBtn").text("Update");
                        $("#addTemplateModal").modal("show");
                    }
                }
            });
        });

        $('#addTemplateModal').on('hidden.bs.modal', function () {
            $("#template_id").val('');
            $("#template_name").val('');
            $("#is_active").val('');
            $('#summernote').summernote('code', '');
            $('#summernote').summernote('reset');
        });

        $(document).on("click", ".view-template", function () {
            let id = $(this).data("id");

            $.ajax({
                url: "/admin/api/ajax",
                type: "GET",
                data: {
                    get_emailtemplate: true,
                    id: id
                },
                success: function (data) {

                    if (data == "false") {
                        swal.fire("Error", "Unable to load template", "error");
                        return;
                    }

                    // Inject HTML into iframe
                    let iframe = document.getElementById("templatePreviewFrame");
                    let doc = iframe.contentDocument || iframe.contentWindow.document;

                    doc.open();
                    doc.write(data.template); // 👈 your HTML template
                    doc.close();

                    $("#previewTemplateModal").modal("show");
                }
            });
        });

        $(document).on('click', '.delete-template', function () {
            let id = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                        $.ajax({
                                url: '/admin/delete-template/' + id,
                                type: 'DELETE',
                                data: {
                                    _method: 'DELETE',
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function (data) {
                                    if (data.status == "true" || data.trim() == "true") {
                                        Swal.fire(
                                            'Deleted!',
                                            'Your template has been deleted.',
                                            'success'
                                        ).then(() => {
                                            location.reload();
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: "error",
                                            title: "Error",
                                            text: data.error ?? 'Something went wrong'
                                        });
                                    }
                                },
                                error: function (xhr) {
                                    Swal.fire({
                                        icon: "error",
                                        title: "Error",
                                        text: xhr.responseJSON?.error ?? 'Server error'
                            });
                        }
                    });

                }
            });
        });
    </script>
@endsection
