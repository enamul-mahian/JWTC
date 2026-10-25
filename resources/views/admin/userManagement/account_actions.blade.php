@extends('dashboard.layout.dasboardMaster')
@section('title', 'Manage User Account')
@section('content')
<section class="container-fluid" style="max-width:950px">
    <div class="mb-4">
        <h2 class="h3">Manage User Account</h2>
        <p class="text-muted">Employer and job seeker account operations. Administrator accounts are protected.</p>
        <a href="{{ route('user-management.index') }}" class="btn btn-outline-secondary btn-sm">Back to User Management</a>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <strong>{{ $user->name }}</strong>
            <div class="text-muted">{{ $user->email }} · {{ $user->user_type }} · ID {{ $user->id }}</div>
        </div>
    </div>
    @if(session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            @foreach($errors->all() as $error)<p class="mb-1">{{ $error }}</p>@endforeach
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><h3 class="h5 mb-0">Set a new password</h3></div>
        <div class="card-body">
            <p>Enter a new strong password. This revokes API access tokens and changes the remember-me token.</p>
            <form action="{{ route('admin.users.password', $user->id) }}" method="POST" autocomplete="off">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="admin-password-change">Your administrator password</label>
                    <input class="form-control" id="admin-password-change" name="admin_password" type="password" required autocomplete="current-password">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="new-user-password">New user password</label>
                    <input class="form-control" id="new-user-password" name="password" type="password" required minlength="12" autocomplete="new-password">
                    <small class="text-muted">At least 12 characters with uppercase, lowercase, numbers and symbols.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="confirm-user-password">Confirm new user password</label>
                    <input class="form-control" id="confirm-user-password" name="password_confirmation" type="password" required minlength="12" autocomplete="new-password">
                </div>
                <button class="btn btn-primary" type="submit">Set password</button>
            </form>
        </div>
    </div>

    <div class="card border-danger">
        <div class="card-header"><h3 class="h5 mb-0 text-danger">Delete user and owned uploaded files</h3></div>
        <div class="card-body">
            <p class="text-danger">This disables and soft-deletes the account, and physically removes its owned CVs, certificates, uploaded documents, profile/company photos and signatures. Existing job, application and financial history is preserved. Removed files cannot be restored from the application.</p>
            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" autocomplete="off"
                  onsubmit="return confirm('Delete this user and permanently remove their owned uploaded files?');">
                @csrf
                @method('DELETE')
                <div class="mb-3">
                    <label class="form-label" for="delete-email">Type the user's full email address to confirm</label>
                    <input class="form-control" id="delete-email" name="confirm_email" type="text" required autocomplete="off" spellcheck="false" placeholder="{{ $user->email }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="admin-password-delete">Your administrator password</label>
                    <input class="form-control" id="admin-password-delete" name="admin_password" type="password" required autocomplete="current-password">
                </div>
                <button class="btn btn-danger" type="submit">Delete account and owned files</button>
            </form>
        </div>
    </div>
</section>
@endsection
