@extends('dashboard.layouts.master')
@section('title', 'Floorplan List')
@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">


    <style>
        /* ---------------- Modern Premium Table Wrapper ---------------- */
        .profx-admin-table-wrapper {
            /* background: linear-gradient(135deg, #ffffff, #ffe6f0); */
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            font-family: 'Inter', sans-serif;
            width: 100%;
            overflow-x: auto;
        }

        /* ---------------- Table Styling ---------------- */
        .profx-admin-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .profx-admin-table thead {
            background: linear-gradient(90deg, #c19d38, #d4b452);
        }

        .profx-admin-table thead th {
            color: #fff;
            padding: 16px;
            font-size: 14px;
            text-align: left;
        }

        .profx-admin-table tbody td {
            padding: 14px 16px;
            font-size: 14px;
            border-bottom: 1px solid #f0f0f0;
        }

        .profx-admin-table tbody tr:hover {
            background: #fff0f6;
            transition: 0.3s;
        }

        /* ---------------- Status ---------------- */
        .profx-admin-status {
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            text-transform: uppercase;
        }

        .profx-admin-status.active {
            background: #fdf8ea;
            color: #c19d38;
        }

        .profx-admin-status.inactive {
            background: #ffeaea;
            color: #d32f2f;
        }

        /* ---------------- Buttons ---------------- */
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
            transition: 0.3s;
            font-weight: 500;
        }

        .btn-primary {
            background: linear-gradient(90deg, #c19d38, #d4b452);
            color: #fff;
            border: none;
            box-shadow: 0 4px 15px rgba(233, 30, 99, 0.4);
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, #d4b452, #c19d38);
        }

        .btn-info {
            background: #1d4ed8;
            color: #fff;
            border: none;
        }

        .btn-info:hover {
            background: #2563eb;
        }

        /* ---------------- Form Inputs ---------------- */
        .form-control {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 13px;
            transition: 0.3s;
        }

        .form-control:focus {
            border-color: #c19d38;
            box-shadow: 0 0 8px rgba(233, 30, 99, 0.2);
            outline: none;
        }

        /* ---------------- Search Form ---------------- */
        .profx-search-form {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        /* ---------------- Pagination ---------------- */
        .profx-pagination {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
            list-style: none;
            gap: 6px;
        }

        .profx-pagination li a,
        .profx-pagination li span {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 8px;
            border: 1px solid #c19d38;
            color: #c19d38;
            text-decoration: none;
            font-size: 13px;
        }

        .profx-pagination li.active span {
            background: linear-gradient(90deg, #c19d38, #d4b452);
            color: #fff;
            border-color: #c19d38;
        }

        .profx-pagination li.disabled span {
            background: #f0f0f0;
            border-color: #ddd;
            color: #aaa;
            cursor: not-allowed;
        }

        /* ---------------- Modal ---------------- */
        .modal-content {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            background: linear-gradient(90deg, #c19d38, #d4b452);
            color: #fff;
            border-bottom: none;
        }

        .modal-footer {
            border-top: none;
            gap: 10px;
        }

        /* Adjust input inside modal */
        .modal-body .form-label {
            font-weight: 500;
        }
    </style>

    <div class="profx-admin-table-wrapper">
        <h4 class="mb-3">Total Floorplans: {{ $stats->total }}</h4>

        <!-- Search Form -->
        <form method="GET" class="profx-search-form">
            <input type="text" name="email" placeholder="Search by Email" value="{{ request('email') }}"
                class="form-control" style="flex: 1 1 220px;">
            <input type="text" name="boothtitle" placeholder="Search by Booth Title" value="{{ request('boothtitle') }}"
                class="form-control" style="flex: 1 1 220px;">
            <button type="submit" class="btn btn-primary" style="flex: 0 0 auto;">Search</button>
        </form>

        <!-- Table -->
        <table class="profx-admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Booth Title</th>
                    <th>Booth No</th>
                    <th>Payment Proof</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($floorplans as $f)
                    <tr>
                        <td>{{ $f->id }}</td>
                        <td>{{ $f->name }}</td>
                        <td>{{ $f->email }}</td>
                        <td>{{ $f->boothtitle }}</td>
                        <td>{{ $f->boothno }}</td>
                        <td><a href="{{ $f->file ? url('uploads/topics/'.$f->file) : '' }}"
   class="btn btn-sm btn-primary view-file-btn"
   >
    <i class="bi bi-eye"></i>
</a>
</td>
                        <td>
                            <span class="profx-admin-status {{ $f->boothammount > 0 ? 'active' : 'inactive' }}">
                                {{ $f->boothammount > 0 ? 'Paid' : 'Pending' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('floorplansView', $f->id) }}" class="btn"><i class="bi bi-eye"></i></a>
                            @if($f->status !== 'approved')
                                <button type="button" class="btn btn-primary btn-sm approveBtn" data-id="{{ $f->id }}"
                                    data-url="{{ route('floorplans.approve', $f->id) }}">
                                    Verify
                                </button>
                            @else
                                <button type="button" class="btn btn-success btn-sm approveBtn" data-id="{{ $f->id }}"
                                    data-url="{{ route('floorplans.approve', $f->id) }}">
                                    Verified
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No floorplans found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="mt-3">
            {{ $floorplans->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <!-- Approve Modal -->
    <div id="approveFloorplanModal" class="modal fade" data-backdrop="true">
        <div class="modal-dialog" id="animate">
            <form method="POST" action="" id="approveFloorplanForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Approve Floorplan</h5>
                        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center p-lg">
                        <p>Are you sure you want to approve this floorplan?</p>

                        <div class="mb-3 text-left">
                            <label for="companyLogo" class="form-label">Company Logo</label>
                            <input type="file" name="company_logo" id="companyLogo" class="form-control">
                        </div>

                        <div class="mb-3 text-left">
                            <label for="companyUrl" class="form-label">Company URL</label>
                            <input type="url" name="company_url" id="companyUrl" class="form-control"
                                placeholder="https://example.com">
                        </div>

                        <div class="mb-3 text-left">
                            <label for="approveMessage" class="form-label">Message</label>
                            <textarea name="message" id="approveMessage" class="form-control" rows="4"
                                placeholder="Enter a message..."></textarea>
                        </div>

                        <div class="mb-3 text-left">
                            <label for="profileName" class="form-label">Profile Name</label>
                            <input type="text" name="profile_name" id="profileName" class="form-control"
                                value="{{ auth()->user()->name ?? '' }}">
                        </div>
                        <div class="mb-3 text-left">
                            <label for="floorplanStatus" class="form-label">Status</label>
                            <select name="status" id="floorplanStatus" class="form-control profx-status-select">
                                <option value="pending">Pending</option>
                                <option value="approved">Approve</option>
                                <option value="rejected">Reject</option>
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn dark-white p-x-md" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary p-x-md">Approve</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('after-scripts')
    <script>
$(document).ready(function () {
    $('.approveBtn').click(function () {
        var url = $(this).data('url');
        $('#approveFloorplanForm').attr('action', url);
        $('#approveMessage').val('');
        $('#companyLogo').val('');
        $('#companyUrl').val('');
        $('#profileName').val('{{ auth()->user()->name ?? "" }}');
        $('#floorplanStatus').val('pending');
        $('#approveFloorplanModal').modal('show');
    });

    // Success message for approved
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: "Successfully verified payments",
            timer: 2500,
            showConfirmButton: false
        });
    @endif

    // Danger message for rejected
    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Rejected',
            text: "Rejected the Payment",
            timer: 2500,
            showConfirmButton: false
        });
    @endif
});
</script>

@endpush