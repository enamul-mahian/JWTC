@extends('dashboard.layout.dasboardMaster')
@section('title' , 'Massmail')
@section('content')
<div class="container">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('send-mail') }}" method="post">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">User Type </label>
                    <select name="user_type" class="form-control" id="">
                        <option value="job_seeker">Job Seeker </option>
                        <option value="employer">Eamployer </option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="message" class="form-label">Your Message</label>
                    <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>
    </div>
</div>
@endsection