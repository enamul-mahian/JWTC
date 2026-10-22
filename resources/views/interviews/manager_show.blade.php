@extends('secure.layout')
@section('title', 'Schedule Interview')
@section('content')
    <a class="back" href="{{ route('interviews.manage.index') }}">&larr; All applications</a>
    <h1>Interview schedule</h1>
    <p class="muted">Applicant: {{ $application->user?->name ?? 'Applicant' }} · {{ $application->job?->title ?? 'Job application' }} · #{{ $application->id }}</p>
    @if($interview)
        <section class="card">
            <div class="row"><span class="badge">{{ ucfirst(str_replace('_', ' ', $interview->status)) }}</span></div>
            @php $when = \Illuminate\Support\Carbon::parse($interview->starts_at_utc, 'UTC')->setTimezone($interview->timezone); @endphp
            <h2 style="margin-top:1rem">{{ $when->format('D, d M Y · h:i A') }}</h2>
            <p class="muted">{{ $interview->timezone }} · {{ $interview->duration_minutes }} minutes</p>
            <p class="small">Meeting URL: <a href="{{ $interview->meeting_url }}" target="_blank" rel="noopener noreferrer">{{ $interview->meeting_url }}</a></p>
            @if($interview->instructions)<p>{{ $interview->instructions }}</p>@endif
        </section>
    @endif

    @if(!$interview || $interview->status !== 'completed')
        <section class="card">
            <h2>{{ $interview ? 'Reschedule interview' : 'Schedule online interview' }}</h2>
            <form method="POST" action="{{ route('interviews.manage.schedule', $application->id) }}">
                @csrf
                <input type="hidden" name="action" value="{{ $interview ? 'reschedule' : 'schedule' }}">
                <div class="grid">
                    <div>
                        <label for="starts_at_local">Date and time in selected timezone</label>
                        <input id="starts_at_local" type="datetime-local" name="starts_at_local" required
                            value="{{ old('starts_at_local', isset($when) ? $when->format('Y-m-d\TH:i') : '') }}">
                    </div>
                    <div>
                        <label for="timezone">Timezone (IANA name)</label>
                        <input id="timezone" name="timezone" maxlength="64" required placeholder="Europe/London"
                            value="{{ old('timezone', $interview->timezone ?? 'UTC') }}">
                        <p class="small muted">Examples: Europe/London, Asia/Dhaka, UTC</p>
                    </div>
                    <div>
                        <label for="duration_minutes">Duration</label>
                        <select id="duration_minutes" name="duration_minutes" required>
                            @foreach([15,30,45,60,90,120] as $minutes)
                                <option value="{{ $minutes }}" @selected((int)old('duration_minutes', $interview->duration_minutes ?? 30) === $minutes)>{{ $minutes }} minutes</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="meeting_url">HTTPS meeting link</label>
                        <input id="meeting_url" type="url" name="meeting_url" maxlength="2000"
                            placeholder="https://meet.google.com/..." value="{{ old('meeting_url', $interview->meeting_url ?? '') }}"
                            @if(!$interview) required @endif>
                    </div>
                </div>
                <label for="instructions">Instructions for applicant</label>
                <textarea id="instructions" name="instructions" maxlength="2000">{{ old('instructions', $interview->instructions ?? '') }}</textarea>
                <label for="note">Update note (visible to applicant)</label>
                <textarea id="note" name="note" maxlength="1000"></textarea>
                <button type="submit">{{ $interview ? 'Save new appointment time' : 'Schedule interview' }}</button>
            </form>
        </section>
    @endif

    @if($interview && in_array($interview->status, ['scheduled','confirmed','change_requested'], true))
        <section class="card">
            <h2>Interview decision</h2>
            <div class="grid">
                <form method="POST" action="{{ route('interviews.manage.action', $application->id) }}">
                    @csrf
                    <input type="hidden" name="action" value="cancel">
                    <label for="cancel-reason">Reason for cancellation</label>
                    <textarea id="cancel-reason" name="reason" maxlength="1000" required></textarea>
                    <button type="submit" class="secondary">Cancel interview</button>
                </form>
                @if($interview->status !== 'change_requested' && \Illuminate\Support\Carbon::parse($interview->starts_at_utc, 'UTC')->isPast())
                    <form method="POST" action="{{ route('interviews.manage.action', $application->id) }}">
                        @csrf
                        <input type="hidden" name="action" value="complete">
                        <button type="submit">Mark interview completed</button>
                    </form>
                @endif
            </div>
        </section>
    @endif

    <section class="card">
        <h2>Interview audit history</h2>
        <ol class="timeline">
            @forelse($events as $event)
                <li>
                    <strong>{{ ucfirst(str_replace('_', ' ', $event->action)) }}</strong>
                    <div class="small muted">{{ \Illuminate\Support\Carbon::parse($event->created_at, 'UTC')->format('d M Y, H:i') }} UTC</div>
                    @if($event->note)<p>{{ $event->note }}</p>@endif
                </li>
            @empty
                <li>No interview has been scheduled.</li>
            @endforelse
        </ol>
    </section>
@endsection
