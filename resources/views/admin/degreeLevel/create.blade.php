@extends('dashboard.layout.dasboardMaster')
@section('title', "$title")
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3"> {{ $title }}</h5>
                    {{ Form::open(['route' => 'degree-level.store', 'method' => 'post']) }}
                    @include('admin.degreeLevel.form')
                    {!! Form::button('Submit', ['class' => ' btn  btn-primary', 'type' => 'submit']) !!}
                    <a href="{{ route('degree-level.index') }}">
                        <button class="btn btn-danger" type="button">Back</button>
                    </a>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection
