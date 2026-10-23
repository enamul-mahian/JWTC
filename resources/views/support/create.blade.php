@extends('secure.layout')
@section('title', 'Create Support Ticket')
@section('content')
    <a href="{{ route('support.index') }}" class="back">&larr; Back to support</a>
    <h1>Create support ticket</h1>
    <p class="muted">Choose the appropriate category. For a financial refund request, create a Payment or Refund ticket and then submit its dedicated refund form.</p>
    <section class="card">
        <form method="POST" action="{{ route('support.store') }}">
            @csrf
            <label for="recipient-company">Send to</label><select id="recipient-company" name="recipient_company_id"><option value="">JoinGallagher support</option>@foreach($companies as $company)<option value="{{ $company->id }}" @selected(old('recipient_company_id')==$company->id)>{{ $company->company_name }}</option>@endforeach</select><p class="small muted">Your selected recipient can read and reply to this ticket. An email notification is sent through the configured SMTP profile.</p>
            <label for="category">Issue category</label>
            <select id="category" name="category" required>
                @foreach(['general'=>'General inquiry','complaint'=>'Complaint','payment'=>'Payment issue','refund'=>'Request refund','account'=>'Account','documents'=>'Documents','technical'=>'Technical issue'] as $code => $label)
                    <option value="{{ $code }}" @selected(old('category',request('category')) === $code)>{{ $label }}</option>
                @endforeach
            </select>
            <label for="subject">Ticket subject</label>
            <input type="text" name="subject" id="subject" maxlength="180" minlength="5" required
                placeholder="Briefly describe the issue" value="{{ old('subject') }}">
            <label for="description">Detailed explanation</label>
            <textarea id="description" name="description" minlength="10" maxlength="10000" rows="7"
                required placeholder="Explain what happened, relevant dates and what support you need. Avoid sharing passwords, OTPs or full payment card details.">{{ old('description') }}</textarea>
            <p class="small muted">Ticket details and messages are encrypted in the application's private database.</p>
            <button type="submit">Create private ticket</button>
        </form>
    </section>
@endsection
