@extends('dashboard.layout.dasboardMaster')
@section('title', 'Update Role')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3">@yield('title')</h5>
                    {!! Form::model($role, ['route' => ['role.update', $role->id], 'method' => 'put']) !!}
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            {!! Form::label('name', 'Name') !!}
                            {!! Form::text('name', null, ['class' => 'form-control', 'id' => 'tb-fname', 'placeholder' => 'Rrole name']) !!}
                            @error('name')
                                <p class="mb-0 position-absolute text-danger"> 
                                    <i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        @foreach ($permission as $value)
                            <div class="col-lg-2">
                                <input class="form-check-input" type="checkbox" name="permision[]" value="{{ $value->name }}"
                                    id="flexCheckDefault_{{ $value->id }}">
                                <label class="form-check-label" for="flexCheckDefault_{{ $value->id }}">
                                    {{ $value->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    {!! Form::button('Update', ['class' => ' btn btn-sm btn-primary mt-4', 'type' => 'submit']) !!}
                    <a href="{{ route('role.index') }}">
                        <button class="btn btn-secondary btn-sm mt-4 ms-2" type="button"><i class="fa-solid fa-arrow-left"></i> Back</button>
                    </a>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection
