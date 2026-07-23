@extends('dashboard.layouts.master')
@section('title', 'View Client Speaker')
@section('content')
<style>
    .profx-view-wrapper { padding:30px; border-radius:20px; box-shadow:0 15px 35px rgba(0,0,0,.1); max-width:900px; margin:30px auto; background:#fff; }
    .profx-view-wrapper h4 { color:#c19d38; font-weight:700; margin-bottom:22px; }
    .profx-view-table { width:100%; border-collapse:collapse; border-radius:12px; overflow:hidden; }
    .profx-view-table th,.profx-view-table td { padding:13px 15px; font-size:14px; border-bottom:1px solid #f0f0f0; vertical-align:top; }
    .profx-view-table th { width:210px; background:linear-gradient(90deg,#c19d38,#d4b452); color:#fff; }
    .btn-gold { background:linear-gradient(90deg,#c19d38,#d4b452); color:#fff; border:0; border-radius:8px; padding:9px 16px; text-decoration:none; display:inline-block; }
    .speaker-photo { max-width:180px; border-radius:14px; border:1px solid #ddd; }
    .form-control { width:100%; padding:8px 12px; border:1px solid #ccc; border-radius:8px; }
</style>

<div class="profx-view-wrapper">
    <h4>Client Speaker Details</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="profx-view-table">
        <tr><th>ID</th><td>{{ $speaker->id }}</td></tr>
        <tr><th>Photo</th><td>@if($speaker->photo)<img class="speaker-photo" src="{{ url('uploads/topics/' . $speaker->photo) }}" alt="{{ $speaker->name }}">@else N/A @endif</td></tr>
        <tr><th>Name</th><td>{{ $speaker->name }}</td></tr>
        <tr><th>Email</th><td>{{ $speaker->email }}</td></tr>
        <tr><th>Designation</th><td>{{ $speaker->designation }}</td></tr>
        <tr><th>Company</th><td>{{ $speaker->company }}</td></tr>
        <tr><th>Bio</th><td>{!! nl2br(e($speaker->bio)) !!}</td></tr>
        <tr><th>Website</th><td>@if($speaker->website)<a href="{{ $speaker->website }}" target="_blank">{{ $speaker->website }}</a>@endif</td></tr>
        <tr><th>LinkedIn</th><td>@if($speaker->linkedin)<a href="{{ $speaker->linkedin }}" target="_blank">{{ $speaker->linkedin }}</a>@endif</td></tr>
        <tr><th>Instagram</th><td>@if($speaker->instagram)<a href="{{ $speaker->instagram }}" target="_blank">{{ $speaker->instagram }}</a>@endif</td></tr>
        <tr><th>Status</th><td>{{ ucfirst($speaker->status) }}</td></tr>
        <tr><th>Public Position</th><td>{{ $speaker->display_order ?: 'Not fixed' }}</td></tr>
        <tr><th>Admin Message</th><td>{{ $speaker->admin_message }}</td></tr>
        <tr><th>Created At</th><td>{{ $speaker->created_at }}</td></tr>
    </table>

    <form method="POST" action="{{ route('clientSpeakersApprove', $speaker->id) }}" class="mt-4">
        @csrf
        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="pending" {{ $speaker->status === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ $speaker->status === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ $speaker->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>
        <div class="mb-3">
            <label>Public Position</label>
            <input type="number" name="display_order" min="0" max="9999" value="{{ $speaker->display_order ?: '' }}" class="form-control" placeholder="1, 2, 3...">
            <small>Lower number shows first. Empty/0 uses default order.</small>
        </div>
        <div class="mb-3">
            <label>Admin Message</label>
            <textarea name="admin_message" class="form-control" rows="4">{{ $speaker->admin_message }}</textarea>
        </div>
        <button type="submit" class="btn-gold">Update Status</button>
        <a href="{{ route('clientSpeakers') }}" class="btn-gold">Back</a>
    </form>
</div>
@endsection
