@extends('dashboard.layout.dasboardMaster')
@section('title', " $title")
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3"> {{ __('Update Job category') }}</h5>
                    {!! Form::model($jobCategory, ['route' => ['job-categories.update', $jobCategory], 'method' => 'put']) !!}
                    @include('admin.jobCategory.form')
                    {!! Form::button('Submit', ['class' => 'me-2 btn btn-primary', 'type' => 'submit']) !!}
                    <a href="{{ route('job-categories.index') }}">
                        <button class="btn btn-danger" type="button">Back</button>
                    </a>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection
