@extends('dashboard.layouts.master')
@section('title', 'Influencer Registers')
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
.ifx-admin-wrap { padding: 24px; border-radius: 12px; background: #fff; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08); overflow-x: auto; }
.ifx-admin-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 18px; }
.ifx-admin-title { margin: 0; color: #112f45; font-size: 22px; font-weight: 800; }
.ifx-admin-count { color: #64748b; font-size: 13px; font-weight: 700; }
.ifx-search-form { display: grid; grid-template-columns: repeat(3, minmax(180px, 1fr)) auto auto; gap: 12px; align-items: end; padding: 14px; margin-bottom: 18px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; }
.ifx-field label { display: block; margin-bottom: 6px; color: #334155; font-size: 12px; font-weight: 800; }
.ifx-field input, .ifx-field select { width: 100%; min-height: 40px; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; color: #112f45; }
.ifx-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-height: 40px; padding: 8px 14px; border: 0; border-radius: 8px; font-size: 13px; font-weight: 800; text-decoration: none; white-space: nowrap; }
.ifx-btn-primary { background: #c19d38; color: #fff; }
.ifx-btn-light { background: #fff; color: #475569; border: 1px solid #cbd5e1; }
.ifx-btn-view { width: 40px; padding: 0; background: #112f45; color: #fff; }
.ifx-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; }
.ifx-table thead { background: linear-gradient(90deg, #0e5941, #c19d38); }
.ifx-table th { color: #fff; padding: 14px; font-size: 13px; text-align: left; white-space: nowrap; }
.ifx-table td { padding: 13px 14px; color: #334155; font-size: 13px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.ifx-photo { width: 46px; height: 46px; border-radius: 50%; object-fit: cover; border: 2px solid #f3e5b5; background: #f8fafc; }
.ifx-ref-code { display: inline-flex; padding: 6px 10px; border-radius: 999px; background: #fff7df; color: #8a6815; font-weight: 900; }
.ifx-ref-link { max-width: 230px; display: inline-block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #2563eb; vertical-align: middle; }
.ifx-status { display: inline-flex; padding: 6px 10px; border-radius: 999px; font-size: 12px; font-weight: 900; text-transform: capitalize; }
.ifx-status.approved { background: #dcfce7; color: #166534; }
.ifx-status.rejected { background: #fee2e2; color: #991b1b; }
.ifx-status.pending { background: #fff7df; color: #8a6815; }
@media (max-width: 900px) { .ifx-search-form { grid-template-columns: 1fr; } .ifx-btn { width: 100%; } }
</style>

<div class="ifx-admin-wrap">
    <div class="ifx-admin-head">
        <div>
            <h4 class="ifx-admin-title">Influencer Registers</h4>
            <div class="ifx-admin-count">Total: {{ $stats->total }} | Pending: {{ $stats->pending }} | Approved: {{ $stats->approved }}</div>
        </div>
    </div>

    <form method="GET" class="ifx-search-form">
        <div class="ifx-field">
            <label>Email</label>
            <input type="text" name="email" value="{{ request('email') }}" placeholder="Search email">
        </div>
        <div class="ifx-field">
            <label>Name</label>
            <input type="text" name="full_name" value="{{ request('full_name') }}" placeholder="Search name">
        </div>
        <div class="ifx-field">
            <label>Status</label>
            <select name="status">
                <option value="">All Status</option>
                @foreach(['pending', 'approved', 'rejected'] as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="ifx-btn ifx-btn-primary"><i class="bi bi-search"></i> Search</button>
        <a href="{{ route('influencerRegisters') }}" class="ifx-btn ifx-btn-light"><i class="bi bi-x-circle"></i> Clear</a>
    </form>

    <table class="ifx-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Action</th>
                <th>Photo</th>
                <th>Name</th>
                <th>Status</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Country</th>
                <th>Company</th>
                <th>Position</th>
                <th>Referral Code</th>
                <th>Referral Link</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($influencers as $influencer)
                <tr>
                    <td>{{ $influencer->id }}</td>
                    <td><a href="{{ route('influencerRegistersView', $influencer->id) }}" class="ifx-btn ifx-btn-view" title="View"><i class="bi bi-eye"></i></a></td>
                    <td>
                        @if(!empty($influencer->profile_photo))
                            <img src="{{ asset('uploads/settings/' . $influencer->profile_photo) }}" alt="{{ $influencer->full_name }}" class="ifx-photo">
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $influencer->full_name }}</td>
                    <td><span class="ifx-status {{ $influencer->status ?? 'pending' }}">{{ ucfirst($influencer->status ?? 'pending') }}</span></td>
                    <td>{{ $influencer->email }}</td>
                    <td>{{ $influencer->phone }}</td>
                    <td>{{ $influencer->nationality }}</td>
                    <td>{{ $influencer->company_name }}</td>
                    <td>{{ $influencer->position_role }}</td>
                    <td><span class="ifx-ref-code">{{ $influencer->referral_code ?? '-' }}</span></td>
                    <td>
                        @if(!empty($influencer->referral_link))
                            <a href="{{ $influencer->referral_link }}" target="_blank" class="ifx-ref-link" title="{{ $influencer->referral_link }}">{{ $influencer->referral_link }}</a>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $influencer->created_at }}</td>
                </tr>
            @empty
                <tr><td colspan="13" class="text-center">No influencer registers found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-3">
        {{ $influencers->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
