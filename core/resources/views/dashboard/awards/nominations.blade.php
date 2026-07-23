@extends('dashboard.layouts.master')
@section('title', 'Award Nominations')
@section('content')
<style>
    .awards-admin-page { padding:24px; }
    .awards-shell { background:#f5f7f8; border-radius:22px; padding:24px; }
    .awards-top { display:flex; justify-content:space-between; align-items:flex-start; gap:18px; flex-wrap:wrap; margin-bottom:22px; }
    .awards-eyebrow { color:#c09a2e; font-size:12px; font-weight:900; letter-spacing:.1em; text-transform:uppercase; margin-bottom:6px; }
    .awards-title { margin:0; color:#082f27; font-size:32px; font-weight:900; }
    .awards-sub { color:#687776; font-weight:600; margin-top:7px; }
    .awards-stat-grid { display:grid; grid-template-columns:repeat(5,minmax(130px,1fr)); gap:14px; margin-bottom:22px; }
    .awards-stat { background:#fff; border:1px solid #e5e9ea; border-radius:16px; padding:18px; box-shadow:0 12px 26px rgba(8,47,39,.06); }
    .awards-stat span { color:#71817f; font-weight:800; font-size:12px; text-transform:uppercase; letter-spacing:.06em; }
    .awards-stat strong { display:block; color:#082f27; font-size:30px; line-height:1; margin-top:9px; }
    .category-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:14px; margin-bottom:24px; }
    .category-card { display:flex; justify-content:space-between; gap:14px; min-height:116px; padding:18px; background:#0b211c; color:#fff; border:1px solid rgba(201,162,39,.32); border-radius:18px; text-decoration:none; box-shadow:0 16px 34px rgba(0,0,0,.12); }
    .category-card:hover, .category-card.active { color:#fff; text-decoration:none; background:linear-gradient(135deg,#082f27,#137323); transform:translateY(-2px); }
    .category-card h5 { color:#fff; font-weight:900; line-height:1.25; margin:0; font-size:17px; }
    .category-card span { color:#d6b64e; font-size:12px; font-weight:900; text-transform:uppercase; letter-spacing:.08em; }
    .category-count { flex:0 0 auto; width:48px; height:48px; border-radius:14px; display:grid; place-items:center; background:#d0aa35; color:#fff; font-size:21px; font-weight:900; }
    .panel-card { background:#fff; border:1px solid #e3e8e8; border-radius:20px; padding:20px; box-shadow:0 16px 36px rgba(8,47,39,.08); }
    .filter-form { display:grid; grid-template-columns:1.1fr 1.2fr .8fr auto; gap:12px; margin-bottom:18px; }
    .filter-form .form-control { height:44px; border:1px solid #d7dedc; border-radius:11px; padding:0 13px; }
    .btn-gold { border:0; border-radius:11px; padding:0 18px; min-height:44px; background:linear-gradient(90deg,#bf982b,#dbc056); color:#fff; font-weight:900; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; gap:7px; }
    .btn-status { border:0; border-radius:999px; padding:10px 16px; min-height:42px; font-weight:900; text-transform:uppercase; font-size:12px; display:inline-flex; align-items:center; gap:8px; box-shadow:0 10px 20px rgba(8,47,39,.08); transition:transform .18s ease, filter .18s ease; }
    .btn-status:hover { transform:translateY(-1px); filter:brightness(.98); }
    .btn-status-pending { background:linear-gradient(90deg,#fff3cf,#ffe8a3); color:#9b6b00; }
    .btn-status-winner { background:linear-gradient(90deg,#dcfce7,#bbf7d0); color:#08793c; }
    .btn-status-rejected { background:linear-gradient(90deg,#ffe4e6,#fecdd3); color:#be123c; }
    .nomination-card-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; align-items:start; }
    .nomination-card { position:relative; overflow:hidden; background:linear-gradient(180deg,#ffffff 0%,#fbfcfc 100%); border:1px solid #e4eaea; border-radius:16px; padding:16px; box-shadow:0 14px 30px rgba(8,47,39,.08); display:flex; flex-direction:column; gap:10px; min-height:370px; transition:transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
    .nomination-card:before { content:""; position:absolute; inset:0 0 auto; height:5px; background:linear-gradient(90deg,#082f27,#168632,#d0aa35); }
    .nomination-card:hover { transform:translateY(-3px); box-shadow:0 20px 42px rgba(8,47,39,.13); border-color:#d8c17b; }
    .nomination-card-top { display:flex; justify-content:space-between; gap:14px; align-items:flex-start; padding-top:4px; }
    .nominee-name { color:#082f27; font-weight:900; font-size:15px; line-height:1.25; }
    .muted-line { color:#657472; font-weight:600; font-size:12px; margin-top:3px; word-break:break-word; }
    .award-chip { display:inline-flex; width:fit-content; max-width:100%; background:linear-gradient(90deg,#eaf8ef,#fff7d8); color:#08793c; border:1px solid #dceee3; border-radius:999px; padding:6px 10px; font-size:10px; font-weight:900; text-transform:uppercase; line-height:1.2; }
    .award-title-cell { color:#082f27; font-weight:900; font-size:14px; line-height:1.32; min-height:38px; }
    .reason-box { background:#fff; border:1px dashed #d8e1df; border-radius:12px; padding:10px; color:#566563; line-height:1.4; min-height:62px; max-height:92px; overflow:auto; font-size:13px; }
    .nomination-meta { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
    .meta-tile { background:#fff; border:1px solid #edf0f0; border-radius:11px; padding:8px; min-width:0; }
    .meta-tile span { display:block; color:#8a9896; font-size:10px; text-transform:uppercase; font-weight:900; letter-spacing:.06em; }
    .meta-tile strong { display:block; color:#082f27; font-size:12px; margin-top:3px; word-break:break-word; }
    .status-note { background:#fffaf0; border:1px solid #f0dfb4; color:#72530a; border-radius:13px; padding:10px; font-weight:700; }
    .alert-modern { border-radius:14px; border:0; padding:13px 16px; font-weight:800; margin-bottom:16px; }
    .alert-success { background:#dcfce7; color:#08793c; }
    .alert-danger { background:#ffe4e6; color:#be123c; }
    .award-modal-backdrop { position:fixed !important; top:0 !important; left:0 !important; right:0 !important; bottom:0 !important; width:100vw !important; height:100vh !important; display:none; align-items:center !important; justify-content:center !important; padding:20px; background:rgba(4,12,11,.72); z-index:999999 !important; transform:none !important; }
    .award-modal-backdrop.show { display:flex !important; }
    .award-status-modal { width:min(560px,calc(100vw - 32px)); max-height:calc(100vh - 40px); overflow:auto; background:#fff; border-radius:20px; box-shadow:0 30px 80px rgba(0,0,0,.35); margin:auto !important; transform:none !important; }
    .award-status-modal-head { padding:22px 24px; background:linear-gradient(135deg,#082f27,#127423); color:#fff; display:flex; justify-content:space-between; gap:14px; }
    .award-status-modal-head h4 { margin:4px 0 0; color:#fff; font-size:23px; font-weight:900; }
    .award-status-modal-head span { color:#d7b956; font-size:12px; font-weight:900; letter-spacing:.09em; text-transform:uppercase; }
    .modal-close-btn { width:40px; height:40px; border-radius:11px; border:1px solid rgba(255,255,255,.3); background:rgba(255,255,255,.12); color:#fff; font-size:24px; line-height:1; }
    .award-status-modal-body { padding:24px; }
    .modal-field { display:block; margin-bottom:16px; }
    .modal-field span { display:block; color:#b68d1f; font-size:12px; font-weight:900; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px; }
    .modal-field select, .modal-field textarea { width:100%; border:1px solid #d7dedc; border-radius:13px; padding:12px 13px; outline:0; font-weight:700; color:#082f27; }
    .modal-field textarea { min-height:100px; resize:vertical; }
    .status-choice-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:16px; }
    .status-choice { border:1px solid #d7dedc; border-radius:14px; padding:12px; text-align:center; font-weight:900; cursor:pointer; }
    .status-choice input { margin-right:5px; }
    .status-choice-winner { color:#08793c; background:#f0fdf4; }
    .status-choice-rejected { color:#be123c; background:#fff1f2; }
    .status-choice-pending { color:#a17400; background:#fffbeb; }
    @media(max-width:1100px){ .awards-stat-grid{grid-template-columns:repeat(2,1fr);} .filter-form{grid-template-columns:1fr;} }
    @media(max-width:1200px){ .nomination-card-grid{grid-template-columns:repeat(2,minmax(0,1fr));} }
    @media(max-width:640px){ .awards-admin-page{padding:12px;} .awards-shell{padding:14px;} .awards-stat-grid{grid-template-columns:1fr;} .awards-title{font-size:25px;} .nomination-card-grid{grid-template-columns:1fr;} .nomination-meta{grid-template-columns:1fr;} .status-choice-grid{grid-template-columns:1fr;} }
</style>

<div class="awards-admin-page">
    <div class="awards-shell">
        <div class="awards-top">
            <div>
                <div class="awards-eyebrow">PROFX EXPO AFRICA 2026</div>
                <h3 class="awards-title">Award Nomination CRM</h3>
                <div class="awards-sub">Category-wise enquiries, status review, and winner/rejection selection.</div>
            </div>
        </div>

        @if(session('success'))<div class="alert-modern alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert-modern alert-danger">{{ session('error') }}</div>@endif

        <div class="awards-stat-grid">
            <div class="awards-stat"><span>Total Nominations</span><strong>{{ $stats->total }}</strong></div>
            <div class="awards-stat"><span>Categories</span><strong>{{ $stats->categories }}</strong></div>
            <div class="awards-stat"><span>Pending</span><strong>{{ $stats->pending }}</strong></div>
            <div class="awards-stat"><span>Winners</span><strong>{{ $stats->winners }}</strong></div>
            <div class="awards-stat"><span>Rejected</span><strong>{{ $stats->rejected }}</strong></div>
        </div>

        <div class="category-grid">
            @forelse($categoryStats as $category)
                <a class="category-card {{ $selectedCategory === $category->category ? 'active' : '' }}" href="{{ route('awardNominations', ['category' => $category->category]) }}">
                    <div><span>{{ $category->winners }} winner{{ $category->winners == 1 ? '' : 's' }}</span><h5>{{ $category->category }}</h5></div>
                    <div class="category-count">{{ $category->total }}</div>
                </a>
            @empty
                <div class="panel-card">No award categories found yet.</div>
            @endforelse
        </div>

        <div class="panel-card">
            <div class="awards-top" style="margin-bottom:16px;">
                <div>
                    <div class="awards-eyebrow">Selected Category</div>
                    <h4 class="awards-title" style="font-size:24px;">{{ $selectedCategory ?: 'All Award Nominations' }}</h4>
                </div>
            </div>

            <form method="GET" class="filter-form">
                <input type="hidden" name="category" value="{{ $selectedCategory }}">
                <input type="text" name="email" placeholder="Search by email" value="{{ request('email') }}" class="form-control">
                <select name="award_title" class="form-control">
                    <option value="">All Awards</option>
                    @foreach($awardTitles as $awardTitle)
                        <option value="{{ $awardTitle }}" {{ request('award_title') === $awardTitle ? 'selected' : '' }}>{{ $awardTitle }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="winner" {{ request('status') === 'winner' ? 'selected' : '' }}>Winner</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                <button type="submit" class="btn-gold">Search</button>
            </form>

            <div class="nomination-card-grid">
                @forelse($nominations as $nomination)
                    <article class="nomination-card">
                        <div class="nomination-card-top">
                            <div>
                                <span class="award-chip">{{ $nomination->category }}</span>
                                <div class="nominee-name" style="margin-top:9px;">{{ $nomination->name }}</div>
                                <div class="muted-line">{{ $nomination->email }}</div>
                            </div>
                        </div>

                        <div class="award-title-cell">{{ $nomination->award_title }}</div>

                        <div class="nomination-meta">
                            <div class="meta-tile"><span>Company</span><strong>{{ $nomination->company ?: '-' }}</strong></div>
                            <div class="meta-tile"><span>Phone</span><strong>{{ $nomination->phone ?: '-' }}</strong></div>
                            <div class="meta-tile"><span>Submitted</span><strong>{{ $nomination->created_at }}</strong></div>
                            <div class="meta-tile"><span>Website</span><strong>@if($nomination->website)<a href="{{ $nomination->website }}" target="_blank">Open Website</a>@else - @endif</strong></div>
                        </div>

                        <div class="reason-box">{{ $nomination->reason ?: 'No nomination details added.' }}</div>

                        @if($nomination->winner_note)
                            <div class="status-note"><strong>Admin Note:</strong> {{ $nomination->winner_note }}</div>
                        @endif

                        <button type="button" class="btn-status btn-status-{{ $nomination->status }}" style="width:100%; justify-content:center; margin-top:auto;" onclick="openAwardStatusModal(this)"
                            data-action="{{ route('awardNominations.status', $nomination->id) }}"
                            data-name="{{ e($nomination->name) }}"
                            data-award="{{ e($nomination->award_title) }}"
                            data-status="{{ $nomination->status }}"
                            data-note="{{ e($nomination->winner_note) }}">
                            <span style="opacity:.75;">Status</span> {{ $nomination->status }}
                        </button>
                    </article>
                @empty
                    <div class="panel-card">No nominations found for this category.</div>
                @endforelse
            </div>

            <div class="mt-3">{{ $nominations->withQueryString()->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>
</div>

<div class="award-modal-backdrop" id="awardStatusModal" onclick="closeAwardStatusModal(event)">
    <div class="award-status-modal" onclick="event.stopPropagation()">
        <div class="award-status-modal-head">
            <div>
                <span>Update Nomination Status</span>
                <h4 id="statusModalTitle">Award Nomination</h4>
            </div>
            <button type="button" class="modal-close-btn" onclick="hideAwardStatusModal()">&times;</button>
        </div>
        <form method="POST" id="statusModalForm">
            @csrf
            <div class="award-status-modal-body">
                <label class="modal-field">
                    <span>Award</span>
                    <input type="text" id="statusModalAward" class="form-control" readonly style="height:44px;border-radius:13px;border:1px solid #d7dedc;font-weight:800;color:#082f27;">
                </label>

                <div class="modal-field">
                    <span>Status Type</span>
                    <div class="status-choice-grid">
                        <label class="status-choice status-choice-winner"><input type="radio" name="status" value="winner"> Winner</label>
                        <label class="status-choice status-choice-rejected"><input type="radio" name="status" value="rejected"> Rejected</label>
                        <label class="status-choice status-choice-pending"><input type="radio" name="status" value="pending"> Pending</label>
                    </div>
                </div>

                <label class="modal-field">
                    <span>Status Note</span>
                    <textarea name="winner_note" id="statusModalNote" placeholder="Type admin note, winner reason, or rejection reason"></textarea>
                </label>

                <button type="submit" class="btn-gold" style="width:100%;">Submit Status</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAwardStatusModal(button) {
        var modal = document.getElementById('awardStatusModal');
        var form = document.getElementById('statusModalForm');
        var title = document.getElementById('statusModalTitle');
        var award = document.getElementById('statusModalAward');
        var note = document.getElementById('statusModalNote');
        var status = button.getAttribute('data-status') || 'pending';

        form.action = button.getAttribute('data-action');
        title.textContent = button.getAttribute('data-name') || 'Award Nomination';
        award.value = button.getAttribute('data-award') || '';
        note.value = button.getAttribute('data-note') || '';
        form.querySelectorAll('input[name="status"]').forEach(function(input) {
            input.checked = input.value === status;
        });
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function hideAwardStatusModal() {
        document.getElementById('awardStatusModal').classList.remove('show');
        document.body.style.overflow = '';
    }

    function closeAwardStatusModal(event) {
        if (event.target.id === 'awardStatusModal') {
            hideAwardStatusModal();
        }
    }
</script>
@endsection