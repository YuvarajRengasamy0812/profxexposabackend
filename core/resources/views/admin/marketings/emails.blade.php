@extends('dashboard.layouts.master')
@section('title', 'Marketing Emails')

@push('after-styles')
    <link rel="stylesheet" href="{{ asset('assets/dashboard/js/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/dashboard/js/select2-bootstrap-theme/dist/select2-bootstrap.4.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/dashboard/js/datatables/DataTables-1.10.18/css/dataTables.bootstrap4.min.css') }}">
@endpush

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">Marketing Emails</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Marketing Emails</li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <!-- CARD HEADER WITH TABS -->
                        <div class="card-header bg-white border-0 pb-0">
                            <ul class="nav nav-tabs card-header-tabs" id="emailTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" data-toggle="tab" data-target="#emails" type="button">
                                        Emails
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-toggle="tab" data-target="#import" type="button">
                                        Import Email
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <!-- CARD BODY -->
                        <div class="card-body pt-3">
                            <div class="tab-content">
                                <!-- EMAILS TAB -->
                                <div class="tab-pane fade show active" id="emails">
                                    <form method="post">
                                        @csrf
                                        <div class="row g-3 align-items-end">
                                            <div class="col-lg-3 col-md-6">
                                                <label class="form-label">Select Template</label>
                                                <select class="form-select" name="template" id="template" required>
                                                    <option value="" hidden></option>
                                                    <?php foreach ($templates as $template) { ?>
                                                        <option value="<?= $template->id ?>">
                                                            <?= $template->name ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="col-lg-3 col-md-6">
                                                <label class="form-label">Client Selection</label>
                                                <select class="form-select" name="client_select_type" id="client_select_type">
                                                    <option value="" hidden></option>
                                                    <option value="all">All</option>
                                                    <option value="users">Users</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-4 col-md-8">
                                                <label class="form-label">Users</label>
                                                <select class="form-select" name="client_users[]" id="client_users" multiple></select>
                                            </div>
                                            <div class="col-lg-2 col-md-4 d-grid">
                                                <label class="form-label opacity-0">Action</label>
                                                <button type="button" id="sendBulkEmail" class="btn btn-primary">
                                                    Send Email
                                                </button>
                                            </div>
                                        </div>
                                        <hr class="my-4">
                                        <!-- TABLE -->
                                        <div class="table-responsive">
                                            <table id="ajaxDatatable" class="ajaxDataTable table table-bordered text-nowrap w-100">
                                                <thead>
                                                    <tr>
                                                        <th>
                                                        </th>
                                                        <th>#CID</th>
                                                        <th>Name/Email</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                    </form>
                                </div>
                                <!-- IMPORT TAB -->
                                <div class="tab-pane fade" id="import">
                                    <form id="importEmailForm" enctype="multipart/form-data">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-lg-3">
                                                <label class="form-label">Select Template</label>
                                                <select class="form-select" name="import_template" id="import_template" required>
                                                    <option value="" hidden></option>
                                                    <?php foreach ($templates as $template) { ?>
                                                        <option value="<?= $template->id ?>">
                                                            <?= $template->name ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="col-lg-5">
                                                <label class="form-label">Upload Excel File</label>
                                                <input type="file" class="form-control" name="email_file" id="email_file" accept=".xlsx,.xls,.csv">
                                            </div>

                                            <!-- Import Button -->
                                            <div class="col-lg-2 d-grid">
                                                <label class="form-label opacity-0">Action</label>
                                                <button type="button" id="importEmailsBtn" class="btn btn-primary">
                                                    Import Emails
                                                </button>
                                            </div>
                                            <div class="col-lg-2 col-md-4 d-grid">
                                                <label class="form-label opacity-0">Action</label>
                                                <button type="button" id="sendBulkEmailImport" class="btn btn-primary">
                                                    Send Email
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    <hr class="my-4">
                                    <!-- Preview Table -->
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="importedEmailsTable">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Email</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="2" class="text-center text-muted">
                                                        No data imported yet
                                                    </td>
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
        </div>
    </div>
@endsection

@push('after-scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('assets/dashboard/js/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/datatables/DataTables-1.10.18/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/datatables/DataTables-1.10.18/js/dataTables.bootstrap4.min.js') }}"></script>
    <script>
    const bulkEmailSendUrl = @json(route('bulk-email-send.send'));
    const importEmailsUrl = @json(route('import-emails'));
    const bulkEmailImportSendUrl = @json(route('bulk-email-import-send'));

    // $("#ibModal").modal();
    function dTSelection() {
        if (window.dTtable) {
            let tableInfo = window.dTtable.page.info();
            let totalRows = tableInfo.recordsTotal;
        }
    }
    </script>
    <script>

    $("#client_users").select2({
        placeholder: "Select Users",
        allowClear: true,
        width: '100%'
    });

    // TAB SWITCH RESET
    $('button[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        let target = $(e.target).data("target"); // active tab

        if (target === "#emails") {
            // reset Import tab
            $("#importEmailForm")[0].reset();
            $("#importedEmailsTable tbody").html(`
                <tr>
                    <td colspan="2" class="text-center text-muted">
                        No data imported yet
                    </td>
                </tr>
            `);
            importedEmailsGlobal = [];

        } else if (target === "#import") {
            // reset Emails tab
            $("#emails form")[0].reset();

            // reset select2
            $("#client_users").val(null).trigger("change");

            // uncheck all checkboxes
            $(".row-check, #selectAll").prop("checked", false);

            // clear global arrays
            selectedEmailsGlobal = [];
        }
    });


    let selectedEmailsGlobal = [];
    let importedEmailsGlobal = [];

    $(document).on("change", "select[name='client_select_type']", function () {
        let type = this.value,
        rows = window.dTtable.rows().data();

        $(".row-check, #selectAll").prop("disabled", false);

        if (type === "all") {
            $(".row-check, #selectAll").prop("checked", true);
            $("#client_users").empty();
            rows.each(r => selectedEmailsGlobal.push(r.email));
        } else {
            $(".row-check, #selectAll").prop("checked", false);
            let options = "";
            rows.each(r => {
                options += `<option value="${r.id}" data-client-email="${r.email}">
                    ${r.fullname}
                </option>`;
            });

            $("#client_users").html(options);
        }
    });

    $(document).on("change", "select[name='client_users[]']", function () {
        let users = $(this).val() || [];
        let selectedType = $("select[name='client_select_type']").val();
        if(selectedType == "users")
        {
            let rows = window.dTtable.rows().nodes();
            $(rows).find(".row-check").prop("checked", false);
            users.forEach(function (id) {
                $(rows).find(`.row-check[data-id="${id}"]`).prop("checked", true);
            });
        }
    });

    window.dTtable = $('.ajaxDataTable').on("draw.dt", dTSelection).DataTable({
        dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',
        buttons: [
            ''
        ],
        order: [
            [0, "desc"]
        ],
        "ajax": {
            "url": "/admin/ajax",
            "type": "GET",
            data: function(d) {
                d.action = 'getClientList';
            }
        },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `<input type="checkbox" class="row-check" data-id="${row.id}" value="${row.email}">`;
                }
            },
            {
                data: 'id',
                name: 'id'
            },
            {
                data: 'email',
                name: 'email',
                render: function(data, row, row_data) {
                    var return_data = "<a href='/admin/client_details?id=" + row_data.enc_id +
                        "'><div class='d-flex align-items-center'><div class='me-2'><svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 24 24' fill='none' stroke='#000000' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='28' color='#000000' class='tabler-icon tabler-icon-user-square-rounded'><path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path><path d='M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z'></path><path d='M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05'></path></svg></div><div><div class='lh-1'><span>" +
                        row_data.fullname +
                        "</span></div><div class='lh-1'><span class='fs-11 text-muted'>" + row_data
                        .email + "</span></div></div></div></a>";
                    return return_data;
                }
            },
        ],
    });

    $(document).on("change", ".row-check", function () {
        let selectedType = $("select[name='client_select_type']").val();

        if (selectedType === "users") {
            let id = $(this).data("id");
            let $select = $("select[name='client_users[]']");

            let values = $select.val() || [];

            if ($(this).is(":checked")) {
                // add if not exists
                if (!values.includes(id.toString())) {
                    values.push(id.toString());
                }
            } else {
                // remove when unchecked
                values = values.filter(v => v != id);
            }

            $select.val(values).trigger("change"); // important for select2
        } else {
            let email = $(this).val();

            if ($(this).is(":checked")) {
                if (!selectedEmailsGlobal.includes(email)) {
                    selectedEmailsGlobal.push(email);
                }
            } else {
                selectedEmailsGlobal = selectedEmailsGlobal.filter(e => e !== email);
            }
        }
    });

    window.dTtable.on('draw', function () {
        $(".row-check").each(function () {
            let email = $(this).val();

            if (selectedEmailsGlobal.includes(email)) {
                $(this).prop("checked", true);
            }
        });
    });

    $(document).on("click", "#sendBulkEmail", function () {
        let type = $("select[name='client_select_type']").val();
        let template = $("#template").val();

        if (type.length === 0 || !template) {
            Swal.fire({
                icon: 'warning',
                title: 'No client type or Template Selected',
                text: 'Please select at least one',
                confirmButtonText: 'OK'
            });
            return;
        }

        if (type === "all") 
        {
            selectedEmails = selectedEmailsGlobal;
        } 
        else 
        {
            selectedEmails = [];
            $("#client_users option:selected").each(function () {
                selectedEmails.push($(this).data("client-email"));
            });
        }

        if (selectedEmails.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Email Selected',
                text: 'Please select at least one email',
                confirmButtonText: 'OK'
            });
            return;
        }

        // ✅ LOADER
        Swal.fire({
            title: 'Sending Emails...',
            text: 'Please wait while emails are being sent',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: bulkEmailSendUrl,
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                emails: selectedEmails,
                template: template
            },
            success: function (response) {

                Swal.close();

                if (response.status == 1) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Email Sent Successfully',
                        text: 'Total Sent: ' + response.count,
                        confirmButtonColor: '#ff7400',
                    }).then((val) => {
                            location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to Send',
                        text: response.message || 'Something went wrong',
                        confirmButtonColor: '#d33',
                    });
                }
            },
            error: function () {

                Swal.close();

                Swal.fire({
                    icon: 'error',
                    title: 'Request Failed',
                    text: 'Server error occurred',
                    confirmButtonColor: '#d33',
                });
            }
        });

    });

    $(document).on("click", "#importEmailsBtn", function () {

            let file = $("#email_file")[0].files[0];

            if (!file) {
                alert("Please select a file");
                return;
            }

            let formData = new FormData();

            formData.append("email_file", file);
            
            $.ajax({
                url: importEmailsUrl,
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },

                success: function (res) {
                    let tbody = $("#importedEmailsTable tbody");
                    tbody.html("");
                    
                    importedEmailsGlobal = []; // reset

                    if (!res.emails || res.emails.length === 0) {
                        tbody.html(`
                            <tr>
                                <td colspan="2" class="text-center text-muted">
                                    No emails found
                                </td>
                            </tr>
                        `);
                        return;
                    }
                    $.each(res.emails, function (i, email) {
                        importedEmailsGlobal.push(email);
                        tbody.append(`
                            <tr>
                                <td>${i + 1}</td>
                                <td>${email}</td>
                            </tr>
                        `);
                    });
                },

                error: function (xhr) {
                    console.log(xhr.responseText);
                    alert("File import failed");
                }
            });

        });

        $(document).on("click", "#sendBulkEmailImport", function () {
            let importTemplate = $("#import_template option:selected").val();

            if (importedEmailsGlobal.length === 0) {
                console.log("No imported emails found");
                return;
            }
            Swal.fire({
                title: 'Sending Emails...',
                text: 'Please wait while emails are being sent',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: bulkEmailImportSendUrl,
                type: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    emails: importedEmailsGlobal,
                    import_template:importTemplate
                },
                success: function (response) {

                    Swal.close();

                    if (response.status == 1) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Email Sent Successfully',
                            text: 'Total Sent: ' + response.count,
                            confirmButtonColor: '#ff7400',
                        }).then((val) => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed to Send',
                            text: response.message || 'Something went wrong',
                            confirmButtonColor: '#d33',
                        });
                    }
                },
                error: function () {

                    Swal.close();

                    Swal.fire({
                        icon: 'error',
                        title: 'Request Failed',
                        text: 'Server error occurred',
                        confirmButtonColor: '#d33',
                    });
                }
            });

        });
    </script>
@endpush
