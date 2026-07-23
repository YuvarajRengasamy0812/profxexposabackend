@extends('dashboard.layouts.master')
@section('title', 'PROFX Users')
@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        .profx-admin-table-wrapper {
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
            font-family: 'Inter', sans-serif;
            width: 100%;
            overflow-x: auto;
            background: #fff;
        }

        .profx-page-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .profx-page-title {
            margin: 0;
            color: #0f172a;
            font-size: 20px;
            font-weight: 700;
        }

        .profx-page-count {
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
        }

        .profx-page-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .profx-ref-code {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            background: #fff7df;
            color: #8a6815;
            font-weight: 800;
            font-size: 12px;
        }

        .profx-ref-link {
            max-width: 240px;
            display: inline-block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            vertical-align: middle;
            color: #2563eb;
        }

        .profx-modal-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .profx-modal-grid .full {
            grid-column: 1 / -1;
        }

        .profx-help-text {
            color: #64748b;
            font-size: 12px;
            margin-top: 5px;
        }

        .profx-search-form {
            display: grid;
            grid-template-columns: repeat(3, minmax(180px, 1fr)) auto auto;
            gap: 12px;
            align-items: end;
            padding: 14px;
            margin-bottom: 18px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
        }

        .profx-filter-field label {
            display: block;
            margin-bottom: 6px;
            color: #334155;
            font-size: 12px;
            font-weight: 700;
        }

        .form-control,
        .form-select {
            width: 100%;
            min-height: 40px;
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            color: #0f172a;
            font-size: 13px;
            background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #c19d38;
            box-shadow: 0 0 0 3px rgba(193, 157, 56, 0.16);
            outline: none;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 40px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
            transition: 0.2s;
            font-weight: 600;
            border: none;
            white-space: nowrap;
        }

        .btn-primary {
            background: #c19d38;
            color: #fff;
            box-shadow: 0 4px 12px rgba(193, 157, 56, 0.24);
        }

        .btn-primary:hover {
            background: #ad8a28;
            color: #fff;
        }

        .btn-light {
            background: #fff;
            color: #475569;
            border: 1px solid #cbd5e1;
        }

        .btn-light:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .btn-info {
            width: 40px;
            padding: 0;
            background: #1d4ed8;
            color: #fff;
        }

        .btn-info:hover {
            background: #2563eb;
            color: #fff;
        }

        .btn-secondary {
            background: #475569;
            color: #fff;
        }

        .btn-secondary:hover {
            background: #334155;
            color: #fff;
        }

        .btn-secondary:disabled {
            background: #dbe3ee;
            color: #fff;
            cursor: not-allowed;
            box-shadow: none;
        }

        .profx-bulk-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 14px;
            padding: 12px 0;
            border-top: 1px solid #eef2f7;
            border-bottom: 1px solid #eef2f7;
        }

        .profx-selected-count {
            color: #334155;
            font-size: 14px;
            font-weight: 700;
        }

        .profx-select-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #c19d38;
        }

        .profx-admin-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(15, 23, 42, 0.05);
        }

        .profx-admin-table thead {
            background: linear-gradient(90deg, #c19d38, #d4b452);
        }

        .profx-admin-table thead th {
            color: #fff;
            padding: 14px;
            font-size: 13px;
            text-align: left;
            white-space: nowrap;
        }

        .profx-admin-table tbody td {
            padding: 13px 14px;
            color: #334155;
            font-size: 13px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .profx-admin-table tbody tr:hover {
            background: #fffaf0;
        }

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
            background: #c19d38;
            color: #fff;
            border-color: #c19d38;
        }

        .profx-pagination li.disabled span {
            background: #f0f0f0;
            border-color: #ddd;
            color: #aaa;
            cursor: not-allowed;
        }

        @media (max-width: 992px) {
            .profx-search-form {
                grid-template-columns: repeat(2, minmax(180px, 1fr));
            }
        }

        @media (max-width: 640px) {
            .profx-admin-table-wrapper {
                padding: 16px;
            }

            .profx-search-form,
            .profx-modal-grid {
                grid-template-columns: 1fr;
            }

            .btn {
                width: 100%;
            }
        }
    </style>

    <div class="profx-admin-table-wrapper">
        <div class="profx-page-head">
            <div>
                <h4 class="profx-page-title">PROFX Users</h4>
                <div class="profx-page-count">Total User: {{ $stats->total }}</div>
            </div>
            <div class="profx-page-actions">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#referralAccountModal">
                    <i class="bi bi-person-plus"></i> Add Referral Account
                </button>
            </div>
        </div>

        <form method="GET" class="profx-search-form" id="profxSearchForm">
            <div class="profx-filter-field">
                <label for="searchEmail">Email</label>
                <input type="text" id="searchEmail" name="email" placeholder="Search email" value="{{ request('email') }}" class="form-control">
            </div>
            <div class="profx-filter-field">
                <label for="searchName">User Name</label>
                <input type="text" id="searchName" name="full_name" placeholder="Search name" value="{{ request('full_name') }}" class="form-control">
            </div>
            <div class="profx-filter-field">
                <label for="searchUserType">User Type</label>
                <select id="searchUserType" name="user_type" class="form-select">
                    <option value="">All User Types</option>
                    @foreach($userTypes as $type)
                        <option value="{{ $type }}" {{ request('user_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
            <a href="{{ route('profxusers') }}" class="btn btn-light"><i class="bi bi-x-circle"></i> Clear</a>
        </form>

        <form method="POST" action="{{ route('profxusersSendRegistrationEmails') }}" id="registrationEmailForm">
            @csrf
            <div class="profx-bulk-actions">
                <span class="profx-selected-count"><span id="selectedUsersCount">0</span> user(s) selected</span>
                <button type="submit" class="btn btn-secondary" id="sendRegistrationEmailBtn" disabled>
                    <i class="bi bi-envelope-paper"></i> Send Registration Email
                </button>
            </div>

            <table class="profx-admin-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" class="profx-select-checkbox" id="selectAllUsers" title="Select all users on this page"></th>
                        <th>ID</th>
                        <th>Action</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>Referral Code</th>
                        <th>Referral Link</th>
                        <th>Company Name</th>
                        <th>Phone</th>
                        <th>Country</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($profxusers as $f)
                        <tr>
                            <td><input type="checkbox" class="profx-select-checkbox user-select-checkbox" name="user_ids[]" value="{{ $f->id }}"></td>
                            <td>{{ $f->id }}</td>
                            <td>
                                <a href="{{ route('profxusersView', $f->id) }}" class="btn btn-info" title="View"><i class="bi bi-eye"></i></a>
                            </td>
                            <td>{{ $f->full_name }}</td>
                            <td>{{ $f->user_type }}</td>
                            <td>{{ $f->email }}</td>
                            <td><span class="profx-ref-code">{{ $f->referral_code ?? '-' }}</span></td>
                            <td>
                                @if(!empty($f->referral_link))
                                    <a href="{{ $f->referral_link }}" target="_blank" class="profx-ref-link" title="{{ $f->referral_link }}">{{ $f->referral_link }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $f->company_name }}</td>
                            <td>{{ $f->phone }}</td>
                            <td>{{ $f->nationality }}</td>
                            <td>{{ $f->created_at }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </form>

        <div class="mt-3">
            {{ $profxusers->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>
    <div class="modal fade" id="referralAccountModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('profxusersReferralAccountStore') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Create Community / Influencer Referral Account</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="profx-modal-grid">
                            <div>
                                <label>Name</label>
                                <input type="text" name="full_name" class="form-control" required>
                            </div>
                            <div>
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div>
                                <label>Phone Number</label>
                                <input type="text" name="phone" class="form-control" required>
                            </div>
                            <div>
                                <label>Country</label>
                                <input type="text" name="nationality" class="form-control" required>
                            </div>
                            <div>
                                <label>Role</label>
                                <select name="user_type" class="form-select" required>
                                    <option value="Influencer">Influencer</option>
                                    <option value="Community">Community</option>
                                </select>
                            </div>
                            <div>
                                <label>Password</label>
                                <input type="text" name="password" class="form-control" placeholder="Optional auto password">
                                <div class="profx-help-text">Leave empty to auto-generate a password.</div>
                            </div>
                            <div class="full">
                                <label>Referral Code</label>
                                <div class="input-group">
                                    <input type="text" name="referral_code" id="newReferralCode" class="form-control" placeholder="Auto generate if empty">
                                    <button type="button" class="btn btn-light" id="generateReferralCodeBtn">Generate</button>
                                </div>
                                <div class="profx-help-text">Referral link will be created automatically from this code.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @push('after-scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('registrationEmailForm');
                const selectAll = document.getElementById('selectAllUsers');
                const checkboxes = Array.from(document.querySelectorAll('.user-select-checkbox'));
                const sendBtn = document.getElementById('sendRegistrationEmailBtn');
                const countLabel = document.getElementById('selectedUsersCount');
                const generateReferralCodeBtn = document.getElementById('generateReferralCodeBtn');
                const newReferralCode = document.getElementById('newReferralCode');

                function showToast(icon, title) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: icon,
                        title: title,
                        showConfirmButton: false,
                        timer: 2800,
                        timerProgressBar: true
                    });
                }

                function refreshBulkState() {
                    const selectedCount = checkboxes.filter(function (checkbox) { return checkbox.checked; }).length;
                    countLabel.textContent = selectedCount;
                    sendBtn.disabled = selectedCount === 0;
                    if (selectAll) {
                        selectAll.checked = selectedCount > 0 && selectedCount === checkboxes.length;
                        selectAll.indeterminate = selectedCount > 0 && selectedCount < checkboxes.length;
                    }
                }

                if (selectAll) {
                    selectAll.addEventListener('change', function () {
                        checkboxes.forEach(function (checkbox) {
                            checkbox.checked = selectAll.checked;
                        });
                        refreshBulkState();
                    });
                }

                checkboxes.forEach(function (checkbox) {
                    checkbox.addEventListener('change', refreshBulkState);
                });

                if (generateReferralCodeBtn && newReferralCode) {
                    generateReferralCodeBtn.addEventListener('click', function () {
                        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                        let code = 'PFX';
                        for (let i = 0; i < 6; i++) {
                            code += chars.charAt(Math.floor(Math.random() * chars.length));
                        }
                        newReferralCode.value = code;
                    });
                }

                if (form) {
                    form.addEventListener('submit', function (event) {
                        const selectedCount = checkboxes.filter(function (checkbox) { return checkbox.checked; }).length;

                        if (form.dataset.confirmed === '1') {
                            return;
                        }

                        event.preventDefault();

                        if (selectedCount === 0) {
                            showToast('warning', 'Please select at least one user.');
                            return;
                        }

                        Swal.fire({
                            icon: 'question',
                            title: 'Send registration email?',
                            text: 'This will send email to ' + selectedCount + ' selected user(s).',
                            showCancelButton: true,
                            confirmButtonText: 'Send Email',
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#c19d38'
                        }).then(function (result) {
                            if (result.isConfirmed) {
                                form.dataset.confirmed = '1';
                                form.submit();
                            }
                        });
                    });
                }

                @if(session('profxSwalSuccess'))
                    showToast('success', @json(session('profxSwalSuccess')));
                @endif

                @if(session('profxSwalWarning'))
                    showToast('warning', @json(session('profxSwalWarning')));
                @endif

                @if(session('profxSwalError'))
                    showToast('error', @json(session('profxSwalError')));
                @endif

                refreshBulkState();
            });
        </script>
    @endpush

@endsection


