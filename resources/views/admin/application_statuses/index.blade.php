@extends('dashboard.layout.dasboardMaster')
@section('title', 'Application Workflow Settings')

@push('style')<link rel="stylesheet" href="{{ asset('theme/approved.css') }}?v=20261023-6">@endpush
@section('content')
<section class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <div>
            <h2 class="h4 mb-1">Application workflow</h2>
            <p class="text-muted mb-0">Edit stage names and progress without changing historical status IDs.</p>
        </div>
        <a href="{{ route('active.job') }}" class="btn btn-outline-secondary">Back to jobs</a>
    </div>
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="jg-workflow-preview mb-4"><h2 class="h5">Application progress preview</h2><p>These stages and percentages are shared with the applicant timeline.</p><ol>@foreach($statuses as $stage)@if($stage['is_active'])<li><strong>{{ $stage['name'] }}</strong><small>{{ $stage['progress'] }}% complete</small></li>@endif
@endforeach</ol><a href="{{ route('admin.application-progress.index') }}" class="btn btn-primary btn-sm">Open application timelines</a></div>
    <div class="card mb-4">
        <div class="card-header"><strong>Current stages</strong></div>
        <div class="card-body">
            @foreach ($statuses as $status)
                <form method="POST" action="{{ route('admin.application-statuses.update', $status['id']) }}"
                    class="border-bottom py-3">
                    @csrf
                    @method('PATCH')
                    <div class="row align-items-end">
                        <div class="col-md-1 mb-2">
                            <label class="small" for="id-{{ $status['id'] }}">ID</label>
                            <input id="id-{{ $status['id'] }}" class="form-control" value="{{ $status['id'] }}" readonly>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small" for="name-{{ $status['id'] }}">Stage name</label>
                            <input id="name-{{ $status['id'] }}" type="text" name="name" class="form-control"
                                required maxlength="180" value="{{ $status['name'] }}">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="small" for="progress-{{ $status['id'] }}">Progress %</label>
                            <input id="progress-{{ $status['id'] }}" name="progress" type="number"
                                min="0" max="100" class="form-control" value="{{ $status['progress'] }}" required>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="small" for="order-{{ $status['id'] }}">Display order</label>
                            <input id="order-{{ $status['id'] }}" name="sort_order" type="number"
                                min="1" max="65535" class="form-control" value="{{ $status['sort_order'] }}" required>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="small" for="terminal-{{ $status['id'] }}">Terminal</label>
                            <select id="terminal-{{ $status['id'] }}" name="is_terminal" class="form-control">
                                <option value="0" @selected(!$status['is_terminal'])>No</option>
                                <option value="1" @selected($status['is_terminal'])>Yes</option>
                            </select>
                            <label class="small mt-1" for="active-{{ $status['id'] }}">Visibility</label>
                            <select id="active-{{ $status['id'] }}" name="is_active" class="form-control">
                                <option value="1" @selected($status['is_active'])>Active</option>
                                <option value="0" @selected(!$status['is_active'])>Disabled</option>
                            </select>
                        </div>
                        <div class="col-md-1 mb-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                </form>
            @endforeach
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Create a new stage</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.application-statuses.store') }}">
                @csrf
                <div class="row">
                    <div class="form-group col-md-5">
                        <label for="new-name">Stage name</label>
                        <input id="new-name" name="name" class="form-control" maxlength="180" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="new-progress">Progress %</label>
                        <input id="new-progress" type="number" name="progress" class="form-control" min="0" max="100" value="0" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="new-order">Display order</label>
                        <input id="new-order" type="number" name="sort_order" class="form-control" min="1" max="65535" value="120" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="new-terminal">Terminal</label>
                        <select id="new-terminal" name="is_terminal" class="form-control">
                            <option value="0">No</option><option value="1">Yes</option>
                        </select>
                        <input type="hidden" name="is_active" value="1">
                    </div>
                    <div class="form-group col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Add</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
