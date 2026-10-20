@extends('dashboard.layout.dasboardMaster')
@section('title', " $title")
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3"> {{ __('Update Location') }}</h5>
                    @include('admin.location._form', ['action' => 'update'])
                </div>
            </div>
        </div>
    </div>
@endsection
