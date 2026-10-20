@extends('dashboard.layout.dasboardMaster')
@section('title', "$title")
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3"> {{ __('Create Job category') }}</h5>
                    {{ Form::open(['route' => 'job-categories.store', 'method' => 'post']) }}
                    @include('admin.jobCategory.form')
                    {!! Form::button('Submit', ['class' => 'btn me-2 btn-primary', 'type' => 'submit']) !!}
                    <a href="{{ route('job-categories.index') }}">
                        <button class="btn btn-danger" type="button">Back</button>
                    </a>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection
