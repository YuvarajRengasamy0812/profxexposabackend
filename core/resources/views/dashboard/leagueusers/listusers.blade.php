@extends('dashboard.layouts.master')
@section('title', 'League Users')
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
            background: #c19d38;
        }

        .profx-admin-table thead th {
            color: #fff;
            padding: 14px 12px;
            font-size: 13px;
            text-align: left;
            white-space: nowrap;
            vertical-align: middle;
        }

        .profx-admin-table tbody td {
            padding: 13px 12px;
            font-size: 13px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
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
            background: #fce4ec;
            color: #e91e63;
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
            background: #c19d38;
            color: #fff;
            border: none;
            box-shadow: 0 4px 15px rgba(233, 30, 99, 0.4);
        }

        .btn-primary:hover {
            background: #c19d38;
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
            border-color: #e91e63;
            box-shadow: 0 0 8px rgba(233, 30, 99, 0.2);
            outline: none;
        }

        .profx-page-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .profx-export-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-success {
            background: #15803d;
            color: #fff;
            border: none;
        }

        .btn-success:hover {
            background: #166534;
            color: #fff;
        }

        .btn-danger {
            background: #b91c1c;
            color: #fff;
            border: none;
        }

        .btn-danger:hover {
            background: #991b1b;
            color: #fff;
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
            border: 1px solid #e91e63;
            color: #e91e63;
            text-decoration: none;
            font-size: 13px;
        }

        .profx-pagination li.active span {
            background: linear-gradient(90deg, #e91e63, #ff4081);
            color: #fff;
            border-color: #e91e63;
        }

        .profx-pagination li.disabled span {
            background: #f0f0f0;
            border-color: #ddd;
            color: #aaa;
            cursor: not-allowed;
        }


        .copy-link-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 8px;
            border: 0;
            background: #0f766e;
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }

        .copy-link-btn:hover {
            background: #115e59;
            color: #fff;
        }

        .ref-code-badge {
            display: inline-block;
            min-width: 104px;
            padding: 6px 10px;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #0f172a;
            font-weight: 700;
            text-align: center;
            white-space: nowrap;
        }
        /* ---------------- Modal ---------------- */
        .modal-content {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            background: linear-gradient(90deg, #e91e63, #ff4081);
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
        <div class="profx-page-head">
            <h4 class="mb-0">Total User: {{ $stats->total }}</h4>
            <div class="profx-export-actions">
                <a href="{{ route('leagueusersExport', array_merge(request()->query(), ['format' => 'excel'])) }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-excel"></i> Excel Download
                </a>
                <a href="{{ route('leagueusersExport', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="btn btn-danger">
                    <i class="bi bi-file-earmark-pdf"></i> PDF Download
                </a>
            </div>
        </div>

        <!-- Search Form -->
        <form method="GET" class="profx-search-form">
            <input type="text" name="email" placeholder="Search by Email" value="{{ request('email') }}"
                class="form-control" style="flex: 1 1 220px;">
            <input type="text" name="name" placeholder="Search by Name Title" value="{{ request('name') }}"
                class="form-control" style="flex: 1 1 220px;">
            <button type="submit" class="btn btn-primary" style="flex: 0 0 auto;">Search</button>
        </form>

        <!-- Table -->
        <table class="profx-admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                     <th>Action</th>
                    <th>Name</th>
                     <th>Role</th>
                    <th>Email</th>
                    <th>Company</th>
                    <th>Phone</th>
                    <th>Country</th>
                    <th>Referral Code</th>
                    <th>Referral Link</th>
                   <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leagueusers as $f)
                    <tr>
                        <td>{{ $f->id }}</td>
                        <td>
                            <a href="{{ route('leagueusersView', $f->id) }}" class="btn"><i class="bi bi-eye"></i></a>
                           
                        </td>
                        <td>{{ $f->name }}</td>
                         <td>{{ $f->role }}</td>
                        <td>{{ $f->email }}</td>
                        <td>{{ $f->company}}</td>
                        <td>{{ $f->phone }}</td>
                        <td>
                           {{ $f->country }}
                        </td>
                        <td><span class="ref-code-badge">{{ $f->own_referral_code ?? "-" }}</span></td>
                        <td>
                            @if(!empty($f->own_referral_link))
                                <button type="button" class="copy-link-btn" data-copy-link="{{ $f->own_referral_link }}">
                                    <i class="bi bi-clipboard"></i> Copy Link
                                </button>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{$f->created_at}}</td>
                        
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center">No league users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="mt-3">
            {{ $leagueusers->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <!-- Approve Modal -->
  


    <script>
        document.querySelectorAll('[data-copy-link]').forEach(function (button) {
            button.addEventListener('click', function () {
                const link = this.getAttribute('data-copy-link');
                const done = () => Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Referral link copied',
                    showConfirmButton: false,
                    timer: 1600,
                    timerProgressBar: true
                });

                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(link).then(done);
                    return;
                }

                const input = document.createElement('input');
                input.value = link;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);
                done();
            });
        });
    </script>
@endsection




