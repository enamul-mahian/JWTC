@extends('dashboard.layout.dasboardMaster')
@section('title', 'Job Seeker Details')
@section('content')
<style>
    iframe {
        width: 100%;
        height: 90vh;
    }
</style>
    <section class="datatables">
        <!-- basic table -->
        <div class="row">
            <div class="col-12">  
                <div class="card">
                    <iframe src="{{ route('get-resume-template') }}" title="description"></iframe>
                </div>
            </div>
        </div>
    </section>

<!-- Bootstrap JS and Popper.js -->
<script src="{{ asset('frontend/assets/js/bootstrap.min.js') }}"></script>
@endsection