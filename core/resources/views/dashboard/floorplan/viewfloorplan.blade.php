@extends('dashboard.layouts.master')
@section('title', 'View Floorplan')
@section('content')

<style>
/* ---------------- Wrapper ---------------- */
.profx-view-wrapper {
    /* background: linear-gradient(135deg, #fff0f6, #ffe6f0); */
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    max-width: 900px;
    margin: 30px auto;
    font-family: 'Inter', sans-serif;
}

/* ---------------- Title ---------------- */
.profx-view-wrapper h4 {
    font-weight: 700;
    font-size: 22px;
    color: #c19d38;
    margin-bottom: 25px;
    position: relative;
}

.profx-view-wrapper h4::after {
    content: '';
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, #c19d38, #d4b452);
    display: block;
    margin-top: 5px;
    border-radius: 2px;
}

/* ---------------- Table ---------------- */
.profx-view-table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    background: #fff;
}

.profx-view-table th, .profx-view-table td {
    padding: 14px 16px;
    font-size: 14px;
}

.profx-view-table th {
    background: linear-gradient(90deg, #c19d38, #d4b452);
    color: #fff;
    font-weight: 600;
    text-align: left;
}

.profx-view-table td {
    background: #fff0f6;
    border-bottom: 1px solid #ffd6e3;
}

.profx-view-table tr:nth-child(even) td {
    background: #ffe6f0;
}

/* ---------------- File Link ---------------- */
.profx-view-file-link {
    display: inline-block;
    padding: 6px 12px;
    background: linear-gradient(90deg, #c19d38, #d4b452);
    color: #fff;
    border-radius: 8px;
    text-decoration: none;
    transition: 0.3s;
}

.profx-view-file-link:hover {
    background: linear-gradient(90deg, #d4b452, #c19d38);
    color: #fff;
}

/* ---------------- Back Button ---------------- */
.profx-view-back-btn {
    display: inline-block;
    margin-top: 25px;
    padding: 10px 22px;
    font-weight: 600;
    font-size: 14px;
    border-radius: 10px;
    background: linear-gradient(90deg, #c19d38, #d4b452);
    color: #fff;
    text-decoration: none;
    transition: 0.3s;
}

.profx-view-back-btn:hover {
    background: linear-gradient(90deg, #d4b452, #c19d38);
    box-shadow: 0 6px 20px rgba(233,30,99,0.4);
    color: #fff;
}
</style>

<div class="profx-view-wrapper">
    <h4>Floorplan Details</h4>
    
    <table class="profx-view-table">
        <tr><th>ID</th><td>{{ $floorplan->id }}</td></tr>
        <tr><th>Name</th><td>{{ $floorplan->name }}</td></tr>
        <tr><th>Email</th><td>{{ $floorplan->email }}</td></tr>
        <tr><th>Phone</th><td>{{ $floorplan->phone }}</td></tr>
        <tr><th>Company</th><td>{{ $floorplan->company }}</td></tr>
        <tr><th>Referral Code</th><td>{{ $floorplan->referal_code }}</td></tr>
        <tr><th>Booth No</th><td>{{ $floorplan->boothno }}</td></tr>
        <tr><th>Booth Title</th><td>{{ $floorplan->boothtitle }}</td></tr>
        <tr><th>Booth Size</th><td>{{ $floorplan->boothsize }}</td></tr>
        <tr><th>Booth Amount</th><td>{{ $floorplan->boothammount }}</td></tr>
        <tr><th>Payment Type</th><td>{{ $floorplan->paymenttype }}</td></tr>
<tr>
    <th>PaymentProof</th>
    <td>
        @if($floorplan->file)

            @php
                $extension = pathinfo($floorplan->file, PATHINFO_EXTENSION);
            @endphp

            @if(in_array(strtolower($extension), ['jpg','jpeg','png','gif','webp']))
                <!-- ✅ Image Preview -->
                <a href="{{ $floorplan->file }}" target="_blank">
                    <img src="{{ $floorplan->file }}"
                         alt="Floorplan Image"
                         style="max-width:200px; max-height:200px; border-radius:8px; border:1px solid #ddd;">
                </a>
            @else
                <!-- ✅ PDF / Other File -->
                <a href="{{ $floorplan->file }}" target="_blank" class="profx-view-file-link">
                    View File
                </a>
            @endif

        @else
            N/A
        @endif
    </td>
</tr>

        <tr><th>Company Profile Name</th><td>{{ $floorplan->company_profile_name ?: 'N/A' }}</td></tr>
        <tr>
            <th>Company Website</th>
            <td>
                @if(!empty($floorplan->company_url))
                    <a href="{{ $floorplan->company_url }}" target="_blank" rel="noopener">{{ $floorplan->company_url }}</a>
                @else
                    N/A
                @endif
            </td>
        </tr>
        <tr><th>Company Details</th><td>{{ $floorplan->company_details ?: 'N/A' }}</td></tr>
        <tr>
            <th>Company Logo</th>
            <td>
                @if(!empty($floorplan->company_logo))
                    <a href="{{ url('uploads/settings/'.$floorplan->company_logo) }}" target="_blank" rel="noopener">
                        <img src="{{ url('uploads/settings/'.$floorplan->company_logo) }}"
                             alt="Company Logo"
                             style="max-width:200px; max-height:120px; border-radius:8px; border:1px solid #ddd; background:#fff; padding:8px;">
                    </a>
                @else
                    N/A
                @endif
            </td>
        </tr>
        <tr><th>Booth Design</th><td>{{ $floorplan->booth_design ?? 'N/A' }}</td></tr>
        <tr>
            <th>Booth Design Image</th>
            <td>
                @if(!empty($floorplan->booth_design_image))
                    @php
                        $designUrl = url('uploads/settings/'.$floorplan->booth_design_image);
                        $designExt = strtolower(pathinfo($floorplan->booth_design_image, PATHINFO_EXTENSION));
                        $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
                        $videoTypes = ['mp4', 'mov', 'avi', 'webm', 'mkv'];
                    @endphp
                    <a href="{{ $designUrl }}" target="_blank" rel="noopener" style="display:inline-block; text-decoration:none; color:#333;">
                        @if(in_array($designExt, $imageTypes))
                            <img src="{{ $designUrl }}" alt="Booth Design" style="max-width:220px; max-height:140px; border-radius:8px; border:1px solid #ddd; background:#fff; padding:8px;">
                        @elseif(in_array($designExt, $videoTypes))
                            <video src="{{ $designUrl }}" controls style="max-width:220px; max-height:140px; border-radius:8px; border:1px solid #ddd; background:#000;"></video>
                        @else
                            <span style="display:inline-block; padding:12px 18px; border:1px solid #ddd; border-radius:8px; background:#fff; font-weight:700;">{{ strtoupper($designExt ?: 'FILE') }} File</span>
                        @endif
                    </a>
                @else
                    N/A
                @endif
            </td>
        </tr>
        <tr><th>Approval Status</th><td>{{ ucfirst($floorplan->status ?? 'pending') }}</td></tr>
        <tr><th>Admin Message</th><td>{{ $floorplan->approval_message ?: 'N/A' }}</td></tr>
        <tr><th>Approved By</th><td>{{ $floorplan->approved_by ?: 'N/A' }}</td></tr>
        <tr><th>Network Type</th><td>{{ $floorplan->networktype }}</td></tr>
        <tr><th>Created At</th><td>{{ $floorplan->created_at }}</td></tr>
        <tr><th>Updated At</th><td>{{ $floorplan->updated_at }}</td></tr>
    </table>

    <a href="{{ route('floorplanList') }}" class="profx-view-back-btn">Back to List</a>
</div>

@endsection


