@extends('dashboard.layout.dasboardMaster')
@section('title', "$title")
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3"> {{ __('Add Skills') }}</h5>
                    <form action="{{ route('skills.store') }}" method="POST">
                        @csrf
                        @include('admin.skill._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
