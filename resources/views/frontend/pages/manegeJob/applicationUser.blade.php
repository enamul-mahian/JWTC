@extends('frontend.layout.frontMaster')
@section('title', "$title'")
@push('style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .user-name {
            cursor: pointer;
        }
    </style>
@endpush
@section('content')
    <section id="latest-jobs" class="section bg-gray mt-4">
        <div class="page-header">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="inner-header">
                            <h3>{{ __('messages.ManageJobs') }} <span style="font-size: 16px"> /{{ __('messages.ApplyUserList') }}</span></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-md-12 col-xs-12">
                        <div class="right-sideabr">
                            @include('frontend.pages.employer.sideMenu')
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-12 col-xs-12">

                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab"
                                    aria-controls="home" aria-selected="true">{{ __('messages.AppliedUserList') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab"
                                    aria-controls="profile" aria-selected="false">{{ __('messages.ShortListCv') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="profile-tab" data-toggle="tab" href="#interview" role="tab"
                                    aria-controls="profile" aria-selected="false">{{ __('messages.Interview') }}</a>
                            </li>
                        </ul>
                        <div class="tab-content mt-3" id="myTabContent">
                            <div class="tab-pane fade show active" id="home" role="tabpanel"
                                aria-labelledby="home-tab">
                                @foreach ($personalInfo as $value)
                                    @php
                                        $applicationId = App\Models\Application::where('user_id', $value->user_id)
                                            ->where('job_id', $jobId)
                                            ->select('id', 'status', 'expected_salary')
                                            ->first();
                                    @endphp
                                    <div class="card mb-4">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6 class="mb-0 text-success user-name">
                                                        {{ $value->first_name . ' ' . $value->last_name }}
                                                    </h6>
                                                    <h3 class="mb-0 text-success" style="font-size: 14px">
                                                        {{ $applicationId->expected_salary }}
                                                    </h3>
                                                    <p class="mb-0">
                                                        {{ __('messages.Email') }}: <a style="color: black"
                                                            href="mailto:{{ $value?->email }}">{{ $value?->email }} </a>
                                                    </p>
                                                    <p class="mb-0">
                                                        {{ __('messages.Phone') }}: <a style="color: black"
                                                            href="tel:{{ $value?->phone }}">{{ $value?->phone }} </a>
                                                    </p>
                                                    <p class="mb-0">
                                                        {{ __('messages.Website') }}: <a style="color: black"
                                                            href="{{ $value?->website }}">{{ $value?->website }} </a>
                                                    </p>
                                                    <div class="row mt-2 align-middle">
                                                        <div class="col-lg-6">
                                                            <a
                                                                href="{{ route('view.resume', [$value->user_id, $applicationId]) }}">
                                                                <button class="btn btn-sm btn-success "> {{ __('messages.viewcv') }}</button>
                                                            </a>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <select name="" class="form-control short_list"
                                                                data-id="{{ $applicationId->id }}">
                                                                <option>Applied</option>
                                                                <option {{ $applicationId->status == 2 ? 'selected' : '' }}
                                                                    value="2">{{ __('messages.ShortListed') }}</option>
                                                                <option {{ $applicationId->status == 3 ? 'selected' : '' }}
                                                                    value="3">{{ __('messages.Interview') }}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                @php
                                                    $user = App\Models\User::where('id', $value->user_id)
                                                        ->select('photo')
                                                        ->first();
                                                @endphp
                                                <div class="col-md-6 text-right">
                                                    <img class="img-thumbnail"
                                                        src="{{ asset('assets/images/profilephoto/' . $user?->photo) }}"
                                                        alt="{{ $value->first_name . ' ' . $value->last_name }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                {{ $personalInfo->links() }}
                            </div>

                            <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                                @foreach ($shortListPersonalInfo as $value)
                                    @php
                                        $applicationId = App\Models\Application::where('user_id', $value->user_id)
                                            ->where('job_id', $jobId)
                                            ->select('id', 'status', 'expected_salary')
                                            ->first();
                                    @endphp
                                    <div class="card mb-4">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6 class="mb-0 text-success user-name">
                                                        {{ $value->first_name . ' ' . $value->last_name }}
                                                    </h6>
                                                    <h3 class="mb-0 text-success" style="font-size: 14px">
                                                        {{ $applicationId->expected_salary }}
                                                    </h3>
                                                    <p class="mb-0">
                                                        {{ __('messages.Email') }}: <a style="color: black"
                                                            href="mailto:{{ $value?->email }}">{{ $value?->email }} </a>
                                                    </p>
                                                    <p class="mb-0">
                                                        {{ __('messages.Phone') }}: <a style="color: black"
                                                            href="tel:{{ $value?->phone }}">{{ $value?->phone }} </a>
                                                    </p>
                                                    <p class="mb-0">
                                                        {{ __('messages.Website') }}: <a style="color: black"
                                                            href="{{ $value?->website }}">{{ $value?->website }} </a>
                                                    </p>
                                                    <div class="row mt-2 align-middle">
                                                        <div class="col-lg-6">
                                                            <a
                                                                href="{{ route('view.resume', [$value->user_id, $applicationId]) }}">
                                                                <button class="btn btn-sm btn-success "> {{ __('messages.viewcv') }}</button>
                                                            </a>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <select name="short_list" class="form-control short_list"
                                                                data-id="{{ $applicationId->id }}">
                                                    
                                                                <option {{ $applicationId->status == 2 ? 'selected' : '' }}
                                                                    value="2">{{ __('messages.ShortListed') }}</option>
                                                                <option {{ $applicationId->status == 3 ? 'selected' : '' }}
                                                                    value="3">{{ __('messages.Interview') }}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                @php
                                                    $user = App\Models\User::where('id', $value->user_id)
                                                        ->select('photo')
                                                        ->first();
                                                @endphp
                                                <div class="col-md-6 text-right">
                                                    <img class="img-thumbnail"
                                                        src="{{ asset('assets/images/profilephoto/' . $user?->photo) }}"
                                                        alt="{{ $value->first_name . ' ' . $value->last_name }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                {{ $personalInfo->links() }}
                            </div>

                            <div class="tab-pane fade" id="interview" role="tabpanel" aria-labelledby="profile-tab">
                                @foreach ($interviewPersonalInfo as $value)
                                    @php
                                        $applicationId = App\Models\Application::where('user_id', $value->user_id)
                                            ->where('job_id', $jobId)
                                            ->select('id', 'status', 'expected_salary')
                                            ->first();
                                    @endphp
                                    <div class="card mb-4">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6 class="mb-0 text-success user-name">
                                                        {{ $value->first_name . ' ' . $value->last_name }}
                                                    </h6>
                                                    <h3 class="mb-0 text-success" style="font-size: 14px">
                                                        {{ $applicationId->expected_salary }}
                                                    </h3>
                                                    <p class="mb-0">
                                                        {{ __('messages.Email') }}: <a style="color: black"
                                                            href="mailto:{{ $value?->email }}">{{ $value?->email }} </a>
                                                    </p>
                                                    <p class="mb-0">
                                                        {{ __('messages.Phone') }}: <a style="color: black"
                                                            href="tel:{{ $value?->phone }}">{{ $value?->phone }} </a>
                                                    </p>
                                                    <p class="mb-0">
                                                        {{ __('messages.Website') }}: <a style="color: black"
                                                            href="{{ $value?->website }}">{{ $value?->website }} </a>
                                                    </p>
                                                    <div class="row mt-2 align-middle">
                                                        <div class="col-lg-6">
                                                            <a
                                                                href="{{ route('view.resume', [$value->user_id, $applicationId]) }}">
                                                                <button class="btn btn-sm btn-success "> {{ __('messages.viewcv') }}</button>
                                                            </a>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <select name="short_list" class="form-control short_list"
                                                                data-id="{{ $applicationId->id }}">
                                                                <option {{ $applicationId->status == 2 ? 'selected' : '' }}
                                                                    value="2">{{ __('messages.ShortListed') }}</option>
                                                                <option {{ $applicationId->status == 3 ? 'selected' : '' }}
                                                                    value="3">{{ __('messages.Interview') }}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                @php
                                                    $user = App\Models\User::where('id', $value->user_id)
                                                        ->select('photo')
                                                        ->first();
                                                @endphp
                                                <div class="col-md-6 text-right">
                                                    <img class="img-thumbnail"
                                                        src="{{ asset('assets/images/profilephoto/' . $user?->photo) }}"
                                                        alt="{{ $value->first_name . ' ' . $value->last_name }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                {{ $personalInfo->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('script')
    <script>
        $('.short_list').on('change', function() {
            let value = $(this).val()
            let applicationId = $(this).attr('data-id')

            $.ajax({
                url: "{{ route('shortList.cv') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    status: value,
                    applicationId: applicationId,
                },
                success: function(response) {
                    if (response.status == 2) {
                        toastr.success("done add shortlisted");
                    } else if (response.status == 3) {
                        toastr.success("done add interview");
                    } else {
                        toastr.success("done add applied");
                    }
                    setTimeout(location.reload.bind(location), 1000);
                }
            })
        })
    </script>
@endpush
