@extends('dashboard.layouts.master')
@section('title', 'View Influencer')
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@php($status = $influencer->status ?? 'pending')
<style>
.ifx-view-shell { max-width: 1120px; margin: 28px auto; font-family: 'Inter', sans-serif; }
.ifx-card { margin-bottom: 22px; padding: 24px 28px; border: 1px solid #e7e9ee; border-radius: 18px; background: #fff; box-shadow: 0 18px 45px rgba(17, 47, 69, 0.08); }
.ifx-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin: -24px -28px 24px; padding: 24px 28px; border-radius: 18px 18px 0 0; background: linear-gradient(135deg, #082e24, #c19d38); color: #fff; }
.ifx-head h4, .ifx-card h4 { margin: 0; font-weight: 900; }
.ifx-profile-row { display: flex; align-items: center; gap: 18px; margin-bottom: 22px; }
.ifx-profile-row img { width: 108px; height: 108px; border-radius: 50%; object-fit: cover; border: 3px solid #f3e5b5; background: #f8fafc; }
.ifx-status { display: inline-flex; padding: 7px 12px; border-radius: 999px; font-size: 12px; font-weight: 900; text-transform: capitalize; }
.ifx-status.approved { background: #dcfce7; color: #166534; }
.ifx-status.rejected { background: #fee2e2; color: #991b1b; }
.ifx-status.pending { background: #fff7df; color: #8a6815; }
.ifx-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
.ifx-item { padding: 14px 16px; border-radius: 14px; background: #f8fafc; border: 1px solid #edf0f3; }
.ifx-item span { display: block; margin-bottom: 5px; color: #c19d38; font-size: 12px; font-weight: 900; text-transform: uppercase; }
.ifx-item strong { color: #112f45; overflow-wrap: anywhere; }
.ifx-status-form { display: grid; grid-template-columns: minmax(170px, 220px) 1fr auto; gap: 12px; align-items: end; margin-top: 18px; }
.ifx-status-form label { display: block; margin-bottom: 6px; color: #c19d38; font-size: 12px; font-weight: 900; text-transform: uppercase; }
.ifx-status-form select, .ifx-status-form input { width: 100%; min-height: 42px; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 10px; color: #112f45; }
.ifx-status-form button, .ifx-back { min-height: 42px; padding: 10px 16px; border: 0; border-radius: 10px; background: #c19d38; color: #fff; font-weight: 900; text-decoration: none; }
.ifx-table { width: 100%; border-collapse: collapse; border-radius: 14px; overflow: hidden; background: #fff; }
.ifx-table th { background: #c19d38; color: #fff; padding: 13px 14px; text-align: left; font-size: 13px; }
.ifx-table td { padding: 13px 14px; border-bottom: 1px solid #edf0f3; font-size: 13px; }
.ifx-empty { text-align: center; color: #64748b; padding: 24px !important; }
@media (max-width: 768px) { .ifx-head, .ifx-profile-row { flex-direction: column; align-items: flex-start; } .ifx-grid, .ifx-status-form { grid-template-columns: 1fr; } }
</style>

<div class="ifx-view-shell">
    <div class="ifx-card">
        <div class="ifx-head">
            <div>
                <span>Influencer Register</span>
                <h4>{{ $influencer->full_name }}</h4>
            </div>
            <span>ID #{{ $influencer->id }}</span>
        </div>

        <div class="ifx-profile-row">
            @if(!empty($influencer->profile_photo))
                <img src="{{ asset('uploads/settings/' . $influencer->profile_photo) }}" alt="{{ $influencer->full_name }}">
            @else
                <img src="{{ asset('assets/dashboard/images/user.png') }}" alt="{{ $influencer->full_name }}">
            @endif
            <div>
                <span class="ifx-status {{ $status }}">{{ ucfirst($status) }}</span>
                @if(!empty($influencer->approval_message))
                    <p class="mb-0 mt-2 text-muted">{{ $influencer->approval_message }}</p>
                @endif
            </div>
        </div>

        <div class="ifx-grid">
            <div class="ifx-item"><span>Name</span><strong>{{ $influencer->full_name }}</strong></div>
            <div class="ifx-item"><span>Email</span><strong>{{ $influencer->email }}</strong></div>
            <div class="ifx-item"><span>Phone</span><strong>{{ $influencer->phone }}</strong></div>
            <div class="ifx-item"><span>Country</span><strong>{{ $influencer->nationality ?? '-' }}</strong></div>
            <div class="ifx-item"><span>Company</span><strong>{{ $influencer->company_name ?? '-' }}</strong></div>
            <div class="ifx-item"><span>Position / Role</span><strong>{{ $influencer->position_role ?? '-' }}</strong></div>
            <div class="ifx-item"><span>Referral Code</span><strong>{{ $influencer->referral_code ?? '-' }}</strong></div>
            <div class="ifx-item"><span>Referral Link</span><strong>{{ $influencer->referral_link ?? '-' }}</strong></div>
            <div class="ifx-item"><span>Submitted Referral</span><strong>{{ $influencer->submitted_referral_code ?? '-' }}</strong></div>
            <div class="ifx-item"><span>Created At</span><strong>{{ $influencer->created_at }}</strong></div>
        </div>
    </div>

    <div class="ifx-card">
        <h4>Approval Status</h4>
        <form class="ifx-status-form" method="POST" action="{{ route('influencerRegistersStatus', $influencer->id) }}">
            @csrf
            <div>
                <label>Status</label>
                <select name="status" required>
                    @foreach(['pending', 'approved', 'rejected'] as $item)
                        <option value="{{ $item }}" {{ $status === $item ? 'selected' : '' }}>{{ ucfirst($item) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Admin Message</label>
                <input type="text" name="approval_message" value="{{ $influencer->approval_message }}" placeholder="Optional message">
            </div>
            <button type="submit">Update Status</button>
        </form>
    </div>

    <div class="ifx-card">
        <h4>Referred Registered Users</h4>
        <div class="table-responsive mt-3">
            <table class="ifx-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Company</th>
                        <th>Status</th>
                        <th>Country</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($referralUsers as $referralUser)
                        <tr>
                            <td>{{ $referralUser->id }}</td>
                            <td>{{ $referralUser->full_name }}</td>
                            <td>{{ $referralUser->email }}</td>
                            <td>{{ $referralUser->phone }}</td>
                            <td>{{ $referralUser->company_name }}</td>
                            <td><span class="ifx-status approved">Registered</span></td>
                            <td>{{ $referralUser->nationality }}</td>
                            <td>{{ $referralUser->created_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="ifx-empty">No referred registered users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <a href="{{ route('influencerRegisters') }}" class="ifx-back"><i class="bi bi-arrow-left"></i> Back to Influencer List</a>
</div>
@endsection
