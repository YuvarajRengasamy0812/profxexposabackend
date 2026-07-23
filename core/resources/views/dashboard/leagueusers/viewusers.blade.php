@extends('dashboard.layouts.master')
@section('title', 'View League Users')
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
    background: #c19d38;
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
    background: #c19d38;
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
    background: #c19d38;
    color: #fff;
    border-radius: 8px;
    text-decoration: none;
    transition: 0.3s;
}

.profx-view-file-link:hover {
    background: #c19d38;
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
    background: #c19d38;
    color: #fff;
    text-decoration: none;
    transition: 0.3s;
}

.profx-view-back-btn:hover {
    background: #c19d38;
    box-shadow: 0 6px 20px rgba(233,30,99,0.4);
    color: #fff;
}
</style>

<div class="profx-view-wrapper">
    <h4>League Users Details</h4>
    
    <table class="profx-view-table">
        <tr><th>ID</th><td>{{ $leagueusers->id }}</td></tr>
        <tr><th>Name</th><td>{{ $leagueusers->name }}</td></tr>
        <tr><th>Email</th><td>{{ $leagueusers->email }}</td></tr>
        <tr><th>Phone</th><td>{{ $leagueusers->phone }}</td></tr>
        <tr><th>Country</th><td>{{ $leagueusers->country ?? "-" }}</td></tr>
        <tr><th>Role</th><td>{{ $leagueusers->role ?? "-" }}</td></tr>
        <tr><th>Company</th><td>{{ $leagueusers->company }}</td></tr>
        <tr><th>Referral User</th><td>{{ $leagueusers->referral_user_name ?? $leagueusers->referrer_name ?? "-" }}</td></tr>
        <tr><th>Used Referral Code</th><td>{{ $leagueusers->referral_code ?? "-" }}</td></tr>
        <tr><th>Own Referral Code</th><td>{{ $leagueusers->own_referral_code ?? "-" }}</td></tr>
        <tr><th>Own Referral Link</th><td>
            @if(!empty($leagueusers->own_referral_link))
                <a href="{{ $leagueusers->own_referral_link }}" target="_blank" class="profx-view-file-link">{{ $leagueusers->own_referral_link }}</a>
            @else
                -
            @endif
        </td></tr>

     
        <tr><th>Created At</th><td>{{ $leagueusers->created_at }}</td></tr>
        <tr><th>Updated At</th><td>{{ $leagueusers->updated_at }}</td></tr>
    </table>


    <h4 style="margin-top:30px;">Referral League Users</h4>
    <table class="profx-view-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Country</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($referralLeagueUsers ?? [] as $referralLeagueUser)
                <tr>
                    <td>{{ $referralLeagueUser->id }}</td>
                    <td>{{ $referralLeagueUser->name }}</td>
                    <td>{{ $referralLeagueUser->email }}</td>
                    <td>{{ $referralLeagueUser->phone }}</td>
                    <td>{{ $referralLeagueUser->role ?? '-' }}</td>
                    <td>{{ $referralLeagueUser->country ?? '-' }}</td>
                    <td>{{ $referralLeagueUser->created_at }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No referred league users found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <a href="{{ route('leagueusers') }}" class="profx-view-back-btn">Back to List</a>
</div>

@endsection


