@extends('dashboard.layout.dasboardMaster')
@section('title', " $title")
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3"> {{ __('Update Skill') }}</h5>
                    <form action="{{ route('skills.update', $skill->id) }}" method="POST">
                        @method('put')
                        @csrf
                        @include('admin.skill._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
