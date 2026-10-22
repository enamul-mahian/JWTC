@extends('frontend.layout.frontMaster')
@section('title', 'Application Progress')

@section('content')
<section class="container py-5" style="max-width: 1100px;">
    <a href="{{ route('applied-jobs') }}" class="text-primary">&larr; My applications</a>
    <header class="my-4">
        <h1 class="h3">Application progress</h1>
        <p class="text-muted mb-1">{{ $application->job?->title ?? 'Job application' }}</p>
        <p class="small text-muted">Applied: {{ $application->created_at?->format('d M Y, H:i') ?? $application->date_applied }}</p>
    </header>
    @php
        $percent = $current['progress'] ?? 0;
        $terminal = $current['is_terminal'] ?? false;
        $isRejected = (int) $application->status === 11;
        $steps = collect($statuses)->filter(fn ($s) => !$s['is_terminal'] && $s['is_active'])->values();
        $currentOrder = $current['sort_order'] ?? 0;
    @endphp

    <div class="card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <p class="text-muted small mb-1">Current status</p>
                <h2 class="h5 mb-0">{{ $current['name'] ?? 'Status under review' }}</h2>
            </div>
            <strong>{{ $percent }}% complete</strong>
        </div>
        <div class="progress mt-3" style="height:12px">
            <div class="progress-bar {{ $isRejected ? 'bg-danger' : '' }}" role="progressbar"
                style="width:{{ $percent }}%" aria-valuenow="{{ $percent }}"
                aria-valuemin="0" aria-valuemax="100"
                aria-label="Application completion: {{ $percent }} percent"></div>
        </div>
        @if ($terminal)
            <p class="mt-3 mb-0 {{ $isRejected ? 'text-danger' : 'text-success' }}">
                {{ $isRejected ? 'Your application is no longer in progress.' : 'This workflow has reached a final stage.' }}
            </p>
        @endif
    </div>

    <h2 class="h5 mb-3">Process stages</h2>
    <div class="border rounded bg-white p-3 mb-4" style="overflow-x:auto">
        <ol class="list-unstyled d-flex mb-0" style="gap:16px;min-width:max-content">
            @foreach ($steps as $step)
                @php $done = !$isRejected && $step['sort_order'] <= $currentOrder; @endphp
                <li style="width:156px" aria-current="{{ $step['id'] === (int) $application->status ? 'step' : 'false' }}">
                    <span class="rounded-circle d-inline-flex justify-content-center align-items-center {{ $done ? 'bg-primary text-white' : 'bg-light text-muted' }}"
                          style="width:32px;height:32px;" aria-hidden="true">{{ $loop->iteration }}</span>
                    <div class="small mt-2">{{ $step['name'] }}</div>
                    <div class="small text-muted">{{ $step['progress'] }}%</div>
                </li>
            @endforeach
        </ol>
    </div>

    <h2 class="h5 mb-3">Status history</h2>
    <div class="card p-4">
        <ol class="list-unstyled mb-0" style="border-left:3px solid #dce6ef;padding-left:20px;">
            <li class="mb-4">
                <strong>Application submitted</strong>
                <div class="small text-muted">{{ $application->created_at?->format('d M Y, H:i') ?? $application->date_applied }}</div>
            </li>
            @forelse ($events as $event)
                <li class="mb-4">
                    <strong>{{ $event->status_name }}</strong>
                    <div class="small text-muted">
                        {{ \Illuminate\Support\Carbon::parse($event->created_at)->format('d M Y, H:i') }}
                        &middot; {{ $event->progress }}%
                    </div>
                    @if ($event->note)
                        <p class="mt-2 mb-0">{{ $event->note }}</p>
                    @endif
                </li>
            @empty
                <li class="text-muted">No subsequent status changes have been recorded.</li>
            @endforelse
        </ol>
    </div>
    <p class="small text-muted mt-3">Historical updates logged before this timeline was introduced may not be available.</p>
</section>
@endsection
