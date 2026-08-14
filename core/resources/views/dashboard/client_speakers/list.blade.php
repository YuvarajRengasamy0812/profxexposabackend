@extends('dashboard.layouts.master')
@section('title', 'Client Speakers')
@section('content')
<style>
    .profx-admin-table-wrapper { padding:25px; border-radius:16px; box-shadow:0 10px 25px rgba(0,0,0,.1); background:#fff; overflow-x:auto; }
    .profx-admin-table { width:100%; border-collapse:collapse; background:#fff; border-radius:12px; overflow:hidden; }
    .profx-admin-table thead { background:linear-gradient(90deg,#c19d38,#d4b452); }
    .profx-admin-table th { color:#fff; padding:14px; font-size:14px; text-align:left; }
    .profx-admin-table td { padding:13px 14px; border-bottom:1px solid #f0f0f0; font-size:14px; vertical-align:middle; }
    .profx-search-form { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:20px; }
    .form-control { padding:8px 12px; border:1px solid #ccc; border-radius:8px; }
    .btn-gold { background:linear-gradient(90deg,#c19d38,#d4b452); color:#fff; border:0; border-radius:8px; padding:8px 14px; text-decoration:none; display:inline-block; }
    .badge-status { border-radius:50px; padding:6px 12px; font-size:12px; font-weight:700; text-transform:uppercase; }
    .badge-pending { background:#fff7d6; color:#a77800; }
    .badge-approved { background:#e8f7ee; color:#167447; }
    .badge-rejected { background:#fdecec; color:#b42318; }
    .speaker-thumb { width:56px; height:56px; object-fit:cover; border-radius:10px; background:#f7f7f7; }
    .position-form { display:flex; gap:8px; align-items:center; }
    .position-input { width:82px; padding:7px 8px; border:1px solid #d9d9d9; border-radius:8px; }
    .btn-order { background:#173a30; color:#fff; border:0; border-radius:8px; padding:8px 12px; }
    .speaker-list-header { display:flex; justify-content:space-between; gap:15px; align-items:flex-start; flex-wrap:wrap; margin-bottom:18px; }
    .speaker-list-header h4 { margin-bottom:8px; }
    .speaker-actions { display:flex; gap:8px; flex-wrap:wrap; }
    .btn-green { background:#173a30; color:#fff; border:0; border-radius:8px; padding:8px 14px; text-decoration:none; display:inline-block; }
</style>

<div class="profx-admin-table-wrapper">
    <div class="speaker-list-header">
        <div>
            <h4>Client Speakers</h4>
            <p>Total: {{ $stats->total }} | Pending: {{ $stats->pending }} | Approved: {{ $stats->approved }} | Rejected: {{ $stats->rejected }}</p>
        </div>
        <a href="{{ route('clientSpeakersCreate') }}" class="btn-gold">Add Client Speaker</a>
    </div>

    <form method="GET" class="profx-search-form">
        <input type="text" name="email" placeholder="Search by email" value="{{ request('email') }}" class="form-control">
        <select name="status" class="form-control">
            <option value="">All Status</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
        </select>
        <button type="submit" class="btn-gold">Search</button>
    </form>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="profx-admin-table">
        <thead>
            <tr>
                <th>Photo</th>
                <th>Name</th>
                <th>Email</th>
                <th>Company</th>
                <th>Designation</th>
                <th>Status</th>
                <th>Position</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($speakers as $speaker)
                <tr>
                    <td>
                        @if($speaker->photo)
                            <img class="speaker-thumb" src="{{ url('uploads/topics/' . $speaker->photo) }}" alt="{{ $speaker->name }}">
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $speaker->name }}</td>
                    <td>{{ $speaker->email }}</td>
                    <td>{{ $speaker->company }}</td>
                    <td>{{ $speaker->designation }}</td>
                    <td><span class="badge-status badge-{{ $speaker->status }}">{{ $speaker->status }}</span></td>
                    <td>
                        <form method="POST" action="{{ route('clientSpeakersOrder', $speaker->id) }}" class="position-form">
                            @csrf
                            <input type="number" name="display_order" min="0" max="9999" value="{{ $speaker->display_order ?: '' }}" placeholder="Order" class="position-input">
                            <button type="submit" class="btn-order">Save</button>
                        </form>
                    </td>
                    <td>
                        <div class="speaker-actions">
                            <a class="btn-gold" href="{{ route('clientSpeakersView', $speaker->id) }}">View</a>
                            <a class="btn-green" href="{{ route('clientSpeakersEdit', $speaker->id) }}">Edit</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center">No speaker requests found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-3">{{ $speakers->withQueryString()->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
