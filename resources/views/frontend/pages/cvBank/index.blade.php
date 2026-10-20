@extends('frontend.layout.frontMaster')
@section('title', __('CV Bank'))

@push('style')
    <style>
        .cv_bank_pera {
            margin-bottom: 9px;
            line-height: 17px;
            font-size: 12px;
        }
    </style>
@endpush
@section('content')
@include('modal.common')
    <section id="latest-jobs" class="section bg-gray mt-4">
        <div class="page-header">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="inner-header">
                            <h3>{{ __('messages.ManageApplications') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-md-12 col-xs-12">
                        <div class="right-sideabr">
                            @include('frontend.pages.employer.sideMenu')
                        </div>
                    </div>
                    <div class="col-lg-9 col-md-12 col-xs-12">
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6>{{ __('messages.WelcometoCVBank') }}</h6>
                                        <p>
                                            {{ __('messages.CVBankprofessionals') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-12 col-md-12 col-xs-12">
                                <div class="job-listings-featured">

                                    <h6>{{ __('messages.FunctionalCategory') }}</h6>
                                    <p class="cv_bank_pera">
                                        {{ __('messages.easyhire') }}
                                    </p>

                                    <div class="row">
                                        @foreach ($applicationCounts as $key => $value )
                                        <div class="col-lg-4 mb-4">
                                            <a class="btn_modal" href="{{ route('pay.message', [$value['id'], $value['count']]) }}">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <p>{{ $key }}</p>
                                                        <strong>{{ $value['count']  }}</strong>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
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
$(document).ready(function () {
    $(document).on("click", ".btn_modal", function (e) {
        e.preventDefault();
        var url = $(this).attr("href");
        $.ajax({
            type: "GET",
            url: url,
            data: {},
            success: function (res) {
                $("div#common_modal").html(res);
                $("#common_modal").modal("show");
            },
        });
    });
});
</script>
@endpush