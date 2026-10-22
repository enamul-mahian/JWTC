@extends('secure.layout')
@section('title', 'Interview Details')
@section('content')
    <a class="back" href="{{ route('interviews.applicant.index') }}">&larr; My interviews</a>
    <h1>Interview details</h1>
    <p class="muted">{{ $application->job?->title ?? 'Job application' }} · Application #{{ $application->id }}</p>
    <section class="card">
        <div class="row"><span class="badge">{{ ucfirst(str_replace('_', ' ', $interview->status)) }}</span></div>
        @php
            $when = \Illuminate\Support\Carbon::parse($interview->starts_at_utc, 'UTC')
                ->setTimezone($interview->timezone);
        @endphp
        <h2 style="margin-top:1rem">{{ $when->format('D, d M Y · h:i A') }}</h2>
        <p class="muted">Time zone: {{ $interview->timezone }} ({{ $when->format('T P') }}) · Duration: {{ $interview->duration_minutes }} minutes</p>
        @if(!in_array($interview->status, ['cancelled', 'completed'], true))
            <p><a class="button" target="_blank" rel="noopener noreferrer" referrerpolicy="no-referrer"
                href="{{ $interview->meeting_url }}">Open secure interview meeting</a></p>
            <p class="small muted">Only open meeting links from a trusted organizer. This website does not record the meeting.</p>
        @endif
        @if($interview->instructions)<p><strong>Instructions:</strong> {{ $interview->instructions }}</p>@endif
    </section>
    @if(in_array($interview->status, ['scheduled','confirmed'], true) && \Illuminate\Support\Carbon::parse($interview->starts_at_utc, 'UTC')->isFuture())
        <section class="card">
            <h2>Respond to invitation</h2>
            <div class="grid">
                @if($interview->status === 'scheduled')
                    <form method="POST" action="{{ route('interviews.applicant.reply', $application->id) }}">
                        @csrf
                        <input type="hidden" name="action" value="confirm">
                        <button type="submit">Confirm attendance</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('interviews.applicant.reply', $application->id) }}">
                    @csrf
                    <input type="hidden" name="action" value="request_change">
                    <label for="reason">Request another interview time</label>
                    <textarea id="reason" name="reason" required maxlength="1000" placeholder="Explain your availability and preferred dates"></textarea>
                    <button type="submit" class="secondary">Request time change</button>
                </form>
            </div>
        </section>
    @endif
    <section class="card">
        <h2>Interview history</h2>
        <ol class="timeline">
            @forelse($events as $event)
                <li>
                    <strong>{{ ucfirst(str_replace('_', ' ', $event->action)) }}</strong>
                    <div class="small muted">{{ \Illuminate\Support\Carbon::parse($event->created_at, 'UTC')->format('d M Y · H:i') }} UTC</div>
                    @if($event->note)<p>{{ $event->note }}</p>@endif
                </li>
            @empty
                <li>No interview events have been recorded.</li>
            @endforelse
        </ol>
    </section>
@endsection
