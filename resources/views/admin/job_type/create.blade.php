@extends('dashboard.layout.dasboardMaster')
@section('title', "$title")
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3"> {{ __('Create Job Type') }}</h5>
                    @include('admin.job_type._form', ['action' => 'store'])
                </div>
            </div>
        </div>
    </div>
@endsection
