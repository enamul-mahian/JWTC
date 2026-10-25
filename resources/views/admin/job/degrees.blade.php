@extends('dashboard.layout.dasboardMaster')
@section('title', 'Manage Education & Degrees')
@section('content')
<section class="container-fluid py-4" aria-labelledby="degree-page-title">
    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-start mb-4">
        <div>
            <p class="small fw-semibold text-primary text-uppercase mb-1">Job settings</p>
            <h1 id="degree-page-title" class="h3 mb-2">Manage Education &amp; Degrees</h1>
            <p class="text-muted mb-0">Add or rename degree choices, or hide duplicates from new job forms.</p>
        </div>
        <a href="{{ route('admin.jobs.index') }}" class="btn btn-outline-secondary">&larr; Manage All Jobs</a>
    </div>
    <div class="alert alert-info">
        <strong>Historical data stays safe.</strong> Disabling a choice hides it from new jobs, but does not delete it or change existing jobs and jobseeker education. Renaming a choice updates the label wherever that ID is used.
    </div>
    @if(session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Could not save changes:</strong>
            <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <div class="row g-4">
        <div class="col-12 col-xl-5">
            <div class="card border rounded-3 h-100">
                <div class="card-body">
                    <h2 class="h5 fw-bold mb-3">Education levels</h2>
                    <form method="POST" action="{{ route('admin.job-degrees.levels.store') }}" class="row g-2 mb-4">
                        @csrf
                        <div class="col-12 col-sm">
                            <label for="new_level_name" class="form-label">New education level</label>
                            <input id="new_level_name" name="name" class="form-control" placeholder="e.g. Undergraduate" maxlength="120" required>
                        </div>
                        <div class="col-12 col-sm-auto d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Add level</button>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light"><tr><th scope="col">Level / Rename</th><th scope="col">Status</th><th scope="col">Action</th></tr></thead>
                            <tbody>
                            @forelse($levels as $level)
                                @php($levelActive = $level->status === null || (int) $level->status === 1)
                                <tr>
                                    <td>
                                        <form method="POST" id="edit-level-{{ $level->id }}" action="{{ route('admin.job-degrees.levels.update', $level->id) }}">
                                            @csrf @method('PATCH')
                                            <label class="visually-hidden" for="level-name-{{ $level->id }}">Rename {{ $level->name }}</label>
                                            <input id="level-name-{{ $level->id }}" name="name" class="form-control form-control-sm" value="{{ $level->name }}" maxlength="120" required>
                                        </form>
                                    </td>
                                    <td><span class="badge {{ $levelActive ? 'bg-success' : 'bg-secondary' }}">{{ $levelActive ? 'Active' : 'Disabled' }}</span></td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="submit" form="edit-level-{{ $level->id }}" class="btn btn-outline-primary btn-sm">Save</button>
                                            <form method="POST" action="{{ route('admin.job-degrees.levels.status', $level->id) }}">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="enabled" value="{{ $levelActive ? 0 : 1 }}">
                                                <button type="submit" class="btn btn-sm {{ $levelActive ? 'btn-outline-secondary' : 'btn-outline-success' }}">{{ $levelActive ? 'Disable' : 'Enable' }}</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted">No education levels configured.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-7">
            <div class="card border rounded-3 h-100">
                <div class="card-body">
                    <h2 class="h5 fw-bold mb-3">Degrees</h2>
                    <form method="POST" action="{{ route('admin.job-degrees.degrees.store') }}" class="row g-2 mb-4">
                        @csrf
                        <div class="col-12 col-md-5">
                            <label for="new_degree_name" class="form-label">New degree name</label>
                            <input id="new_degree_name" name="name" class="form-control" placeholder="e.g. Bachelor of Arts" maxlength="120" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="new_degree_level" class="form-label">Education level</label>
                            <select id="new_degree_level" name="degree_lavel_id" class="form-select" required>
                                <option value="">Select level</option>
                                @foreach($levels as $level)
                                    @if($level->status === null || (int) $level->status === 1)
                                        <option value="{{ $level->id }}">{{ $level->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Add degree</button>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light"><tr><th scope="col">Degree / Rename</th><th scope="col">Level</th><th scope="col">Status</th><th scope="col">Action</th></tr></thead>
                            <tbody>
                            @forelse($degrees as $degree)
                                @php($degreeActive = $degree->status === null || (int) $degree->status === 1)
                                <tr>
                                    <td>
                                        <form method="POST" id="edit-degree-{{ $degree->id }}" action="{{ route('admin.job-degrees.degrees.update', $degree->id) }}">
                                            @csrf @method('PATCH')
                                            <label class="visually-hidden" for="degree-name-{{ $degree->id }}">Rename {{ $degree->name }}</label>
                                            <input id="degree-name-{{ $degree->id }}" name="name" class="form-control form-control-sm" value="{{ $degree->name }}" maxlength="120" required>
                                        </form>
                                    </td>
                                    <td>
                                        <label class="visually-hidden" for="degree-level-{{ $degree->id }}">Education level</label>
                                        <select form="edit-degree-{{ $degree->id }}" id="degree-level-{{ $degree->id }}" name="degree_lavel_id" class="form-select form-select-sm" required>
                                            @foreach($levels as $level)
                                                @if($level->status === null || (int) $level->status === 1 || $degree->degree_lavel_id == $level->id)
                                                    <option value="{{ $level->id }}" @selected($degree->degree_lavel_id == $level->id)>{{ $level->name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><span class="badge {{ $degreeActive ? 'bg-success' : 'bg-secondary' }}">{{ $degreeActive ? 'Active' : 'Disabled' }}</span></td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="submit" form="edit-degree-{{ $degree->id }}" class="btn btn-outline-primary btn-sm">Save</button>
                                            <form method="POST" action="{{ route('admin.job-degrees.degrees.status', $degree->id) }}">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="enabled" value="{{ $degreeActive ? 0 : 1 }}">
                                                <button type="submit" class="btn btn-sm {{ $degreeActive ? 'btn-outline-secondary' : 'btn-outline-success' }}">{{ $degreeActive ? 'Disable' : 'Enable' }}</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-muted">No degrees configured.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <p class="small text-muted mb-0">Names can appear under different education levels. Duplicate names within the same level are rejected. Use Disable instead of deleting outdated or duplicate choices.</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
