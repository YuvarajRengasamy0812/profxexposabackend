@extends('dashboard.layouts.master')
@section('title', 'View profxusers')
@section('content')

@php
    $referralCode = $profxusers->referral_code ?? '-';
    $referralLink = $profxusers->referral_link ?? (!empty($profxusers->referral_code) ? 'https://profxexpo.com/africa/LeagueEnroll?ref=' . urlencode($profxusers->referral_code) : '-');
@endphp

<style>
.profx-view-shell {
    max-width: 1120px;
    margin: 28px auto;
    font-family: 'Inter', sans-serif;
}
.profx-profile-card,
.profx-referral-card {
    background: #fff;
    border: 1px solid #e7e9ee;
    border-radius: 18px;
    box-shadow: 0 18px 45px rgba(17, 47, 69, 0.08);
    overflow: hidden;
}
.profx-profile-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 24px 28px;
    background: linear-gradient(135deg, #082e24, #c19d38);
    color: #fff;
}
.profx-profile-head h4,
.profx-referral-card h4 {
    margin: 0;
    font-weight: 800;
}
.profx-profile-head span {
    display: inline-flex;
    padding: 6px 12px;
    border-radius: 999px;
    background: rgba(255,255,255,0.16);
    font-weight: 700;
}
.profx-detail-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
    padding: 24px 28px;
}
.profx-detail-item {
    padding: 14px 16px;
    border-radius: 14px;
    background: #f8fafc;
    border: 1px solid #edf0f3;
}
.profx-detail-item span,
.profx-referral-link span {
    display: block;
    margin-bottom: 5px;
    color: #c19d38;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
}
.profx-detail-item strong,
.profx-referral-link strong {
    color: #112f45;
    overflow-wrap: anywhere;
}
.profx-referral-card {
    margin-top: 22px;
    padding: 24px 28px;
}
.profx-referral-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 18px;
}
.profx-referral-code {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    border-radius: 12px;
    background: #fff7df;
    color: #9b7417;
    font-weight: 900;
}
.profx-referral-link {
    padding: 14px 16px;
    border-radius: 14px;
    background: #f8fafc;
    border: 1px dashed #cbd5e1;
    margin-bottom: 18px;
}
.profx-list-table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 14px;
    overflow: hidden;
    background: #fff;
}
.profx-list-table th {
    background: #c19d38;
    color: #fff;
    padding: 13px 14px;
    text-align: left;
    font-size: 13px;
}
.profx-list-table td {
    padding: 13px 14px;
    border-bottom: 1px solid #edf0f3;
    font-size: 13px;
}
.profx-empty-row {
    text-align: center;
    color: #64748b;
    padding: 24px !important;
}
.profx-view-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 22px;
    padding: 11px 18px;
    font-weight: 700;
    border-radius: 12px;
    background: #c19d38;
    color: #fff;
    text-decoration: none;
}
.profx-view-back-btn:hover { color: #fff; background: #ad8c2e; }
@media (max-width: 768px) {
    .profx-profile-head,
    .profx-referral-top { flex-direction: column; }
    .profx-detail-grid { grid-template-columns: 1fr; }
}
</style>

<div class="profx-view-shell">
    <div class="profx-profile-card">
        <div class="profx-profile-head">
            <div>
                <span>Registered User</span>
                <h4>{{ $profxusers->full_name }}</h4>
            </div>
            <span>ID #{{ $profxusers->id }}</span>
        </div>

        <div class="profx-detail-grid">
            <div class="profx-detail-item"><span>Name</span><strong>{{ $profxusers->full_name }}</strong></div>
            <div class="profx-detail-item"><span>Email</span><strong>{{ $profxusers->email }}</strong></div>
            <div class="profx-detail-item"><span>Phone</span><strong>{{ $profxusers->phone }}</strong></div>
            <div class="profx-detail-item"><span>Country</span><strong>{{ $profxusers->nationality ?? '-' }}</strong></div>
            <div class="profx-detail-item"><span>Role</span><strong>{{ $profxusers->user_type ?? '-' }}</strong></div>
            <div class="profx-detail-item"><span>Company</span><strong>{{ $profxusers->company_name }}</strong></div>
            <div class="profx-detail-item"><span>Referral Code</span><strong>{{ $referralCode }}</strong></div>
            <div class="profx-detail-item"><span>Created At</span><strong>{{ $profxusers->created_at }}</strong></div>
            <div class="profx-detail-item"><span>Updated At</span><strong>{{ $profxusers->updated_at }}</strong></div>
        </div>
    </div>

    <div class="profx-referral-card">
        <div class="profx-referral-top">
            <div>
                <h4>Referral League Users</h4>
                <p class="mb-0 text-muted">League enrollments registered using this user's referral code.</p>
            </div>
            <div class="profx-referral-code"><i class="bi bi-link-45deg"></i> {{ $referralCode }}</div>
        </div>

        <div class="profx-referral-link">
            <span>Referral Link</span>
            <strong>{{ $referralLink }}</strong>
        </div>

        <div class="table-responsive">
            <table class="profx-list-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Company</th>
                        <th>Role</th>
                        <th>Country</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($referralLeagueUsers as $leagueUser)
                        <tr>
                            <td>{{ $leagueUser->id }}</td>
                            <td>{{ $leagueUser->name }}</td>
                            <td>{{ $leagueUser->email }}</td>
                            <td>{{ $leagueUser->phone }}</td>
                            <td>{{ $leagueUser->company }}</td>
                            <td>{{ $leagueUser->role }}</td>
                            <td>{{ $leagueUser->country }}</td>
                            <td>{{ $leagueUser->created_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="profx-empty-row">No referral league users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <a href="{{ route('profxusers') }}" class="profx-view-back-btn"><i class="bi bi-arrow-left"></i> Back to List</a>
</div>

@endsection

