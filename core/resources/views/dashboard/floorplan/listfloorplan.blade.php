@extends('dashboard.layouts.master')
@section('title', 'Floorplan List')
@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        .profx-admin-table-wrapper { padding:25px; border-radius:16px; box-shadow:0 10px 25px rgba(0,0,0,.1); font-family:'Inter',sans-serif; width:100%; overflow-x:auto; }
        .profx-admin-table { width:100%; border-collapse:collapse; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 5px 15px rgba(0,0,0,.05); }
        .profx-admin-table thead { background:linear-gradient(90deg,#c19d38,#d4b452); }
        .profx-admin-table thead th { color:#fff; padding:16px; font-size:14px; text-align:left; white-space:nowrap; }
        .profx-admin-table tbody td { padding:14px 16px; font-size:14px; border-bottom:1px solid #f0f0f0; vertical-align:middle; }
        .profx-admin-table tbody tr:hover { background:#fff8e6; transition:.25s; }
        .profx-admin-status { padding:6px 14px; border-radius:50px; font-size:12px; font-weight:700; display:inline-block; text-transform:uppercase; }
        .profx-admin-status.active { background:#fdf8ea; color:#c19d38; }
        .profx-admin-status.success { background:#e7f7eb; color:#12843b; }
        .profx-admin-status.inactive { background:#ffeaea; color:#d32f2f; }
        .profx-admin-status.pending { background:#fff4d5; color:#b37a00; }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:6px; padding:8px 12px; border-radius:8px; font-size:13px; text-decoration:none; cursor:pointer; transition:.25s; font-weight:600; border:0; }
        .btn-primary { background:linear-gradient(90deg,#c19d38,#d4b452); color:#fff; box-shadow:0 4px 15px rgba(193,157,56,.28); }
        .btn-primary:hover { background:linear-gradient(90deg,#d4b452,#c19d38); color:#fff; }
        .btn-success { background:#52b357; color:#fff; }
        .btn-info { background:#1d4ed8; color:#fff; }
        .btn-danger { background:#dc3545; color:#fff; }
        .btn-light-action { background:#fff; color:#162b3c; border:1px solid #d9dee6; }
        .action-group { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
        .form-control { padding:9px 12px; border:1px solid #ccc; border-radius:8px; font-size:13px; transition:.3s; width:100%; }
        .form-control:focus { border-color:#c19d38; box-shadow:0 0 8px rgba(193,157,56,.2); outline:none; }
        .profx-search-form { display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:20px; }
        .modal-content { border-radius:12px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,.15); }
        .modal-header { background:linear-gradient(90deg,#c19d38,#d4b452); color:#fff; border-bottom:none; }
        .modal-footer { border-top:none; gap:10px; }
        .modal-body .form-label { font-weight:600; text-align:left; display:block; }
        .modal-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; text-align:left; }
        .modal-grid .full { grid-column:1 / -1; }
        .current-file-preview { margin-top:8px; min-height:58px; border:1px dashed #d7dce4; border-radius:10px; background:#fbfcfd; padding:8px; display:flex; align-items:center; gap:10px; color:#1f2937; font-size:12px; }
        .current-file-preview:empty { display:none; }
        .current-file-preview img { width:86px; height:50px; object-fit:contain; border-radius:8px; background:#fff; border:1px solid #edf0f3; }
        .current-file-preview video { width:96px; height:54px; object-fit:cover; border-radius:8px; background:#111827; border:1px solid #edf0f3; }
        .current-file-preview .file-badge { width:64px; height:44px; border-radius:8px; background:#fff7df; color:#b37a00; display:grid; place-items:center; font-weight:800; border:1px solid #ead083; }
        .current-file-preview a { color:#1d4ed8; font-weight:700; text-decoration:none; overflow-wrap:anywhere; }
        .btn-icon-only { width:44px; height:44px; padding:0; font-size:18px; }
        @media(max-width:768px){ .modal-grid { grid-template-columns:1fr; } }
    </style>

    <div class="profx-admin-table-wrapper">
        <h4 class="mb-3">Total Floorplans: {{ $stats->total }}</h4>

        <form method="GET" class="profx-search-form">
            <input type="text" name="email" placeholder="Search by Email" value="{{ request('email') }}" class="form-control" style="flex:1 1 220px;">
            <input type="text" name="boothtitle" placeholder="Search by Booth Title" value="{{ request('boothtitle') }}" class="form-control" style="flex:1 1 220px;">
            <button type="submit" class="btn btn-primary" style="flex:0 0 auto;">Search</button>
        </form>

        <table class="profx-admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Booth Title</th>
                    <th>Booth No</th>
                    <th>Payment Proof</th>
                    <th>Payment Status</th>
                    <th>Company Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($floorplans as $f)
                    @php
                        $status = $f->status ?? 'pending';
                        $statusClass = $status === 'approved' ? 'success' : ($status === 'rejected' ? 'inactive' : 'pending');
                        $companyLogoUrl = !empty($f->company_logo) ? url('uploads/settings/'.$f->company_logo) : '';
                        $boothDesignImageUrl = !empty($f->booth_design_image) ? url('uploads/settings/'.$f->booth_design_image) : '';
                    @endphp
                    <tr>
                        <td>{{ $f->id }}</td>
                        <td>{{ $f->name }}</td>
                        <td>{{ $f->email }}</td>
                        <td>{{ $f->boothtitle }}</td>
                        <td>{{ $f->boothno }}</td>
                        <td>
                            @if(!empty($f->file))
                                <a href="{{ url('uploads/topics/'.$f->file) }}" class="btn btn-primary btn-sm view-file-btn" target="_blank"><i class="bi bi-eye"></i></a>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <span class="profx-admin-status {{ $f->boothammount > 0 ? 'active' : 'inactive' }}">
                                {{ $f->boothammount > 0 ? 'Paid' : 'Pending' }}
                            </span>
                        </td>
                        <td>
                            <span class="profx-admin-status {{ $statusClass }}">{{ ucfirst($status) }}</span>
                            @if(!empty($f->company_profile_name) || !empty($f->company_logo) || !empty($f->company_url))
                                <div style="font-size:12px; margin-top:6px; color:#555;">{{ $f->company_profile_name ?: $f->company }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="action-group">
                                <a href="{{ route('floorplansView', $f->id) }}" class="btn btn-light-action" title="View"><i class="bi bi-eye"></i></a>
                                <button type="button" class="btn btn-info btn-sm editBtn"
                                    data-url="{{ route('floorplans.update', $f->id) }}"
                                    data-name="{{ e($f->name) }}"
                                    data-email="{{ e($f->email) }}"
                                    data-phone="{{ e($f->phone) }}"
                                    data-company="{{ e($f->company) }}"
                                    data-referal_code="{{ e($f->referal_code) }}"
                                    data-boothno="{{ e($f->boothno) }}"
                                    data-boothtitle="{{ e($f->boothtitle) }}"
                                    data-boothsize="{{ e($f->boothsize) }}"
                                    data-boothammount="{{ e($f->boothammount) }}"
                                    data-paymenttype="{{ e($f->paymenttype) }}"
                                    data-networktype="{{ e($f->networktype) }}"
                                    data-company_profile_name="{{ e($f->company_profile_name) }}"
                                    data-company_url="{{ e($f->company_url) }}"
                                    data-company_details="{{ e($f->company_details) }}"
                                    data-booth_design="{{ e($f->booth_design ?? '') }}"
                                    data-company_logo_url="{{ $companyLogoUrl }}"
                                    data-booth_design_image_url="{{ $boothDesignImageUrl }}"
                                    data-status="{{ e($status) }}"
                                    data-approval_message="{{ e($f->approval_message) }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn {{ $status === 'approved' ? 'btn-success' : 'btn-primary' }} btn-sm btn-icon-only approveBtn" title="{{ $status === 'approved' ? 'Verified' : 'Verify' }}"
                                    data-url="{{ route('floorplans.approve', $f->id) }}"
                                    data-company_url="{{ e($f->company_url) }}"
                                    data-message="{{ e($f->approval_message) }}"
                                    data-profile_name="{{ e($f->approved_by ?: (auth()->user()->name ?? '')) }}"
                                    data-status="{{ e($status) }}"
                                    data-booth_design="{{ e($f->booth_design ?? '') }}"
                                    data-company_logo_url="{{ $companyLogoUrl }}"
                                    data-booth_design_image_url="{{ $boothDesignImageUrl }}">
                                    <i class="bi {{ $status === 'approved' ? 'bi-patch-check-fill' : 'bi-patch-check' }}"></i>
                                </button>
                                <form method="POST" action="{{ route('floorplans.destroy', $f->id) }}" onsubmit="return confirm('Delete this floorplan booking? This booth will become available.');" style="display:inline-flex; margin:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center">No floorplans found.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3">{{ $floorplans->withQueryString()->links('pagination::bootstrap-5') }}</div>
    </div>

    <div id="approveFloorplanModal" class="modal fade" data-backdrop="true">
        <div class="modal-dialog" id="animate">
            <form method="POST" action="" id="approveFloorplanForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Approve Floorplan</h5>
                        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-lg">
                        <p class="text-center">Approve, reject, or keep this floorplan pending.</p>
                        <div class="mb-3 text-left">
                            <label for="companyLogo" class="form-label">Company Logo</label>
                            <input type="file" name="company_logo" id="companyLogo" class="form-control" accept="image/*">
                            <div id="approveCompanyLogoPreview" class="current-file-preview"></div>
                        </div>
                        <div class="mb-3 text-left">
                            <label for="companyUrl" class="form-label">Company URL</label>
                            <input type="url" name="company_url" id="companyUrl" class="form-control" placeholder="https://example.com">
                        </div>
                        <div class="mb-3 text-left">
                            <label for="boothDesign" class="form-label">Booth Design</label>
                            <select name="booth_design" id="boothDesign" class="form-control">
                                <option value="">Select Booth Design</option>
                                <option value="Standard Design">Standard Design</option>
                                <option value="Premium Design">Premium Design</option>
                                <option value="Custom Design">Custom Design</option>
                            </select>
                        </div>
                        <div class="mb-3 text-left">
                            <label for="boothDesignImage" class="form-label">Booth Design Image</label>
                            <input type="file" name="booth_design_image" id="boothDesignImage" class="form-control" accept="image/*,application/pdf,video/*">
                            <div id="approveBoothDesignImagePreview" class="current-file-preview"></div>
                        </div>
                        <div class="mb-3 text-left">
                            <label for="approveMessage" class="form-label">Message</label>
                            <textarea name="message" id="approveMessage" class="form-control" rows="4" placeholder="Enter a message..."></textarea>
                        </div>
                        <div class="mb-3 text-left">
                            <label for="profileName" class="form-label">Profile Name</label>
                            <input type="text" name="profile_name" id="profileName" class="form-control" value="{{ auth()->user()->name ?? '' }}">
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
                        <button type="submit" class="btn btn-primary p-x-md">Save Status</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="editFloorplanModal" class="modal fade" data-backdrop="true">
        <div class="modal-dialog modal-lg" id="animateEdit">
            <form method="POST" action="" id="editFloorplanForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Floorplan</h5>
                        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-lg">
                        <div class="modal-grid">
                            <div><label class="form-label">Name</label><input name="name" id="editName" class="form-control"></div>
                            <div><label class="form-label">Email</label><input name="email" id="editEmail" type="email" class="form-control"></div>
                            <div><label class="form-label">Phone</label><input name="phone" id="editPhone" class="form-control"></div>
                            <div><label class="form-label">Company</label><input name="company" id="editCompany" class="form-control"></div>
                            <div><label class="form-label">Referral Code</label><input name="referal_code" id="editReferal" class="form-control"></div>
                            <div><label class="form-label">Booth No</label><input name="boothno" id="editBoothNo" class="form-control"></div>
                            <div><label class="form-label">Booth Title</label><input name="boothtitle" id="editBoothTitle" class="form-control"></div>
                            <div><label class="form-label">Booth Size</label><input name="boothsize" id="editBoothSize" class="form-control"></div>
                            <div><label class="form-label">Booth Amount</label><input name="boothammount" id="editBoothAmount" type="number" step="0.01" class="form-control"></div>
                            <div><label class="form-label">Payment Type</label><input name="paymenttype" id="editPaymentType" class="form-control"></div>
                            <div><label class="form-label">Network Type</label><input name="networktype" id="editNetworkType" class="form-control"></div>
                            <div><label class="form-label">Profile Name</label><input name="company_profile_name" id="editProfileName" class="form-control"></div>
                            <div><label class="form-label">Company URL</label><input name="company_url" id="editCompanyUrl" type="url" class="form-control"></div>
                            <div><label class="form-label">Company Logo</label><input name="company_logo" id="editCompanyLogo" type="file" class="form-control" accept="image/*"><div id="editCompanyLogoPreview" class="current-file-preview"></div></div>
                            <div>
                                <label class="form-label">Booth Design</label>
                                <select name="booth_design" id="editBoothDesign" class="form-control">
                                    <option value="">Select Booth Design</option>
                                    <option value="Standard Design">Standard Design</option>
                                    <option value="Premium Design">Premium Design</option>
                                    <option value="Custom Design">Custom Design</option>
                                </select>
                            </div>
                            <div><label class="form-label">Booth Design Image</label><input name="booth_design_image" id="editBoothDesignImage" type="file" class="form-control" accept="image/*,application/pdf,video/*"><div id="editBoothDesignImagePreview" class="current-file-preview"></div></div>
                            <div>
                                <label class="form-label">Status</label>
                                <select name="status" id="editStatus" class="form-control">
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="full"><label class="form-label">Company Details</label><textarea name="company_details" id="editCompanyDetails" rows="3" class="form-control"></textarea></div>
                            <div class="full"><label class="form-label">Admin Message</label><textarea name="approval_message" id="editApprovalMessage" rows="3" class="form-control"></textarea></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn dark-white p-x-md" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary p-x-md">Update Floorplan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('after-scripts')
<script>
$(document).ready(function () {
    const maxDesignFileSize = 10 * 1024 * 1024;

    function fileNameFromUrl(url) {
        if (!url) return '';
        return decodeURIComponent(String(url).split('?')[0].split('#')[0].split('/').pop() || 'Uploaded file');
    }

    function fileKind(url, type) {
        if (type && type.indexOf('image/') === 0) return 'image';
        if (type === 'application/pdf') return 'pdf';
        if (type && type.indexOf('video/') === 0) return 'video';
        const ext = String(url || '').split('?')[0].split('.').pop().toLowerCase();
        if (['jpg','jpeg','png','gif','svg','webp'].indexOf(ext) !== -1) return 'image';
        if (ext === 'pdf') return 'pdf';
        if (['mp4','mov','avi','webm','mkv'].indexOf(ext) !== -1) return 'video';
        return 'file';
    }

    function renderPreview(target, url, label, type) {
        const $target = $(target);
        if (!url) {
            $target.empty();
            return;
        }

        const kind = fileKind(url, type || '');
        const safeLabel = label || fileNameFromUrl(url);
        let media = `<span class="file-badge">FILE</span>`;

        if (kind === 'image') {
            media = `<img src="${url}" alt="${safeLabel}">`;
        } else if (kind === 'video') {
            media = `<video src="${url}" controls muted></video>`;
        } else if (kind === 'pdf') {
            media = `<span class="file-badge">PDF</span>`;
        }

        $target.html(`${media}<a href="${url}" target="_blank" rel="noopener">${safeLabel}</a>`);
    }

    function renderSelectedFile(input, target, label, enforceDesignLimit) {
        const file = input.files && input.files[0];
        if (!file) return;

        if (enforceDesignLimit && file.size > maxDesignFileSize) {
            input.value = '';
            $(target).empty();
            Swal.fire({ icon:'error', title:'File Too Large', text:'Booth Design Image must be 10MB or below.' });
            return;
        }

        renderPreview(target, URL.createObjectURL(file), file.name || label, file.type || '');
    }
    $('.approveBtn').click(function () {
        $('#approveFloorplanForm').attr('action', $(this).data('url'));
        $('#approveMessage').val($(this).data('message') || '');
        $('#companyLogo').val('');
        $('#companyUrl').val($(this).data('company_url') || '');
        $('#boothDesign').val($(this).data('booth_design') || '');
        $('#boothDesignImage').val('');
        renderPreview('#approveCompanyLogoPreview', $(this).attr('data-company_logo_url'), 'Company Logo');
        renderPreview('#approveBoothDesignImagePreview', $(this).attr('data-booth_design_image_url'), fileNameFromUrl($(this).attr('data-booth_design_image_url')) || 'Booth Design Image');
        $('#profileName').val($(this).data('profile_name') || '{{ auth()->user()->name ?? "" }}');
        $('#floorplanStatus').val($(this).data('status') || 'pending');
        $('#approveFloorplanModal').modal('show');
    });

    $('.editBtn').click(function () {
        $('#editFloorplanForm').attr('action', $(this).data('url'));
        $('#editName').val($(this).data('name') || '');
        $('#editEmail').val($(this).data('email') || '');
        $('#editPhone').val($(this).data('phone') || '');
        $('#editCompany').val($(this).data('company') || '');
        $('#editReferal').val($(this).data('referal_code') || '');
        $('#editBoothNo').val($(this).data('boothno') || '');
        $('#editBoothTitle').val($(this).data('boothtitle') || '');
        $('#editBoothSize').val($(this).data('boothsize') || '');
        $('#editBoothAmount').val($(this).data('boothammount') || '');
        $('#editPaymentType').val($(this).data('paymenttype') || '');
        $('#editNetworkType').val($(this).data('networktype') || '');
        $('#editProfileName').val($(this).data('company_profile_name') || '');
        $('#editCompanyUrl').val($(this).data('company_url') || '');
        $('#editCompanyLogo').val('');
        renderPreview('#editCompanyLogoPreview', $(this).attr('data-company_logo_url'), 'Company Logo');
        $('#editBoothDesign').val($(this).data('booth_design') || '');
        $('#editBoothDesignImage').val('');
        renderPreview('#editBoothDesignImagePreview', $(this).attr('data-booth_design_image_url'), fileNameFromUrl($(this).attr('data-booth_design_image_url')) || 'Booth Design Image');
        $('#editStatus').val($(this).data('status') || 'pending');
        $('#editCompanyDetails').val($(this).data('company_details') || '');
        $('#editApprovalMessage').val($(this).data('approval_message') || '');
        $('#editFloorplanModal').modal('show');
    });

    $('#companyLogo').on('change', function () { renderSelectedFile(this, '#approveCompanyLogoPreview', 'Company Logo', false); });
    $('#boothDesignImage').on('change', function () { renderSelectedFile(this, '#approveBoothDesignImagePreview', 'Booth Design Image', true); });
    $('#editCompanyLogo').on('change', function () { renderSelectedFile(this, '#editCompanyLogoPreview', 'Company Logo', false); });
    $('#editBoothDesignImage').on('change', function () { renderSelectedFile(this, '#editBoothDesignImagePreview', 'Booth Design Image', true); });

    @if(session('success'))
        Swal.fire({ icon:'success', title:'Success', text:"{{ session('success') }}", timer:2500, showConfirmButton:false });
    @endif
    @if(session('error'))
        Swal.fire({ icon:'error', title:'Rejected', text:"{{ session('error') }}", timer:2500, showConfirmButton:false });
    @endif
    @if(session('info'))
        Swal.fire({ icon:'info', title:'Updated', text:"{{ session('info') }}", timer:2500, showConfirmButton:false });
    @endif
});
</script>
@endpush
