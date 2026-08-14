@extends('dashboard.layouts.master')
@section('title', $speaker->exists ? 'Edit Client Speaker' : 'Add Client Speaker')
@section('content')
<style>
    .speaker-form-wrapper { padding:30px; border-radius:18px; box-shadow:0 12px 30px rgba(0,0,0,.1); max-width:980px; margin:20px auto; background:#fff; }
    .speaker-form-header { display:flex; justify-content:space-between; gap:15px; align-items:center; margin-bottom:22px; flex-wrap:wrap; }
    .speaker-form-header h4 { margin:0; color:#173a30; font-weight:800; }
    .speaker-form-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; }
    .speaker-form-field.full { grid-column:1 / -1; }
    .speaker-form-field label { display:block; margin-bottom:7px; font-weight:700; color:#173a30; }
    .form-control { width:100%; padding:10px 12px; border:1px solid #d7dce2; border-radius:8px; }
    textarea.form-control { min-height:120px; resize:vertical; }
    .btn-gold { background:linear-gradient(90deg,#c19d38,#d4b452); color:#fff; border:0; border-radius:8px; padding:10px 16px; text-decoration:none; display:inline-block; font-weight:700; }
    .btn-dark-green { background:#173a30; color:#fff; border:0; border-radius:8px; padding:10px 16px; text-decoration:none; display:inline-block; font-weight:700; }
    .speaker-photo-preview { max-width:150px; border-radius:12px; border:1px solid #ddd; display:block; margin-top:8px; }
    .form-actions { display:flex; gap:10px; margin-top:22px; flex-wrap:wrap; }
    .alert-danger ul { margin-bottom:0; }
    @media (max-width: 768px) { .speaker-form-grid { grid-template-columns:1fr; } }
</style>

<div class="speaker-form-wrapper">
    <div class="speaker-form-header">
        <h4>{{ $speaker->exists ? 'Edit Client Speaker' : 'Add Client Speaker' }}</h4>
        <a href="{{ route('clientSpeakers') }}" class="btn-dark-green">Back to List</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $speaker->exists ? route('clientSpeakersUpdate', $speaker->id) : route('clientSpeakersStore') }}" enctype="multipart/form-data">
        @csrf

        <div class="speaker-form-grid">
            <div class="speaker-form-field">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $speaker->name) }}" required>
            </div>

            <div class="speaker-form-field">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $speaker->email) }}" required>
            </div>

            <div class="speaker-form-field">
                <label>Designation</label>
                <input type="text" name="designation" class="form-control" value="{{ old('designation', $speaker->designation) }}">
            </div>

            <div class="speaker-form-field">
                <label>Company</label>
                <input type="text" name="company" class="form-control" value="{{ old('company', $speaker->company) }}">
            </div>

            <div class="speaker-form-field">
                <label>Website</label>
                <input type="url" name="website" class="form-control" value="{{ old('website', $speaker->website) }}" placeholder="https://example.com">
            </div>

            <div class="speaker-form-field">
                <label>LinkedIn</label>
                <input type="url" name="linkedin" class="form-control" value="{{ old('linkedin', $speaker->linkedin) }}" placeholder="https://linkedin.com/in/name">
            </div>

            <div class="speaker-form-field">
                <label>Instagram</label>
                <input type="url" name="instagram" class="form-control" value="{{ old('instagram', $speaker->instagram) }}" placeholder="https://instagram.com/name">
            </div>

            <div class="speaker-form-field">
                <label>Public Position</label>
                <input type="number" name="display_order" min="0" max="9999" class="form-control" value="{{ old('display_order', $speaker->display_order ?: '') }}" placeholder="1, 2, 3...">
            </div>

            <div class="speaker-form-field">
                <label>Status</label>
                <select name="status" class="form-control" required>
                    @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                        <option value="{{ $value }}" {{ old('status', $speaker->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="speaker-form-field">
                <label>Photo</label>
                <input type="file" name="photo" class="form-control" accept="image/*">
                @if($speaker->photo)
                    <img src="{{ url('uploads/topics/' . $speaker->photo) }}" alt="{{ $speaker->name }}" class="speaker-photo-preview">
                    <label style="margin-top:8px; font-weight:500;">
                        <input type="checkbox" name="photo_delete" value="1"> Remove current photo
                    </label>
                @endif
            </div>

            <div class="speaker-form-field full">
                <label>Bio</label>
                <textarea name="bio" class="form-control">{{ old('bio', $speaker->bio) }}</textarea>
            </div>

            <div class="speaker-form-field full">
                <label>Admin Message</label>
                <textarea name="admin_message" class="form-control">{{ old('admin_message', $speaker->admin_message) }}</textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-gold">{{ $speaker->exists ? 'Update Speaker' : 'Add Speaker' }}</button>
            <a href="{{ route('clientSpeakers') }}" class="btn-dark-green">Cancel</a>
        </div>
    </form>
</div>
@endsection
