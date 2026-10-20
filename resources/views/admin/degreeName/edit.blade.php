@extends('dashboard.layout.dasboardMaster')
@section('title', " $title")
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3"> {{ $title }}</h5>
                    {!! Form::model($degreeName, ['route' => ['degree-name.update', $degreeName], 'method' => 'put']) !!}
                    @include('admin.degreeName.form')
                    {!! Form::button('<i class="fa-regular fa-pen-to-square"></i> Edit', ['class' => ' btn btn-primary', 'type' => 'submit']) !!}
                    <a href="{{ route('degree-name.index') }}">
                        <button class="btn btn-danger" type="button">Back</button>
                    </a>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection

