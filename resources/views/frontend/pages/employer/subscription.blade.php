@extends('frontend.layout.frontMaster')
@section('title', __('messages.Subscription'))
@push('style')
    <style>
        .preview-img-div {
            height: 110px;
            width: 130px;
            object-fit: cover;
        }
    </style>
@endpush
@section('content')
    <!-- Page Header Start -->
    <div class="page-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="inner-header">
                        <h3>{{ __('messages.MemberShipPlan') }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Page Header End -->

    <!-- Content section Start -->
    <section id="content" class="section-padding">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-12 col-xs-12">
                    <div class="right-sideabr">
                        @include('frontend.pages.employer.sideMenu')
                    </div>
                </div>
                <div class="col-lg-9 col-md-6 col-xs-12 ">
                    <div class="row">

                        @foreach ($plans as $plan)
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-body">
                                        <!-- Plan -->
                                        <div class="pricing-plans-container">
                                            <div class="pricing-plan">
                                                <h5 class="text-center"> <strong> {{ $plan->name }}</strong></h5>
                                                <p class="margin-top-10">{{ $plan->description }}</p>
                                                <div
                                                    class="pricing-plan-label billed-monthly-label py-3 mt-4 bg-light text-center">
                                                    <strong>${{ $plan->price }}</strong> {{ $plan->plan_type }}
                                                </div>
                                                {{-- <div class="pricing-plan-label billed-yearly-label"><strong>$205</strong>/
                                                    yearly
                                                </div> --}}
                                                <div class="text-center">
                                                    <a href="{{ route('stripe.payment', $plan->id) }}"><button class="btn btn-sm btn-success mt-4 ">{{ __('messages.BuyNow') }}</button></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    </div>

                </div>
            </div>
        </div>
    </section>
    <!-- Content section End -->
@endsection

@push('script')
    <script>
        ClassicEditor
            .create(document.querySelector('#about'))
            .catch(error => {
                console.error(error);
            });

        $('.update-profile-photo').on('change', function(e) {
            let photo = e.target.files[0]
            photo = URL.createObjectURL(photo)
            $('.preview-img').attr('src', photo)
            $('.preview-img-div').show()
        })
    </script>
@endpush
