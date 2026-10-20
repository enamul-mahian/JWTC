@extends('frontend.layout.frontMaster')
@section('title', __('messages.Subscriptions'))
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
                        <h3>{{ __('messages.Subscriptions') }}</h3>
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
                <div class="col-lg-9 col-md-6 col-xs-12">
                    <div class="card">
                        <div class="card-header text-right">
                            <a href="{{ route('subscriptions') }}">
                                <button class="btn btn-sm btn-primary">Buy Plan</button>
                            </a>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Plan Name</th>
                                        <th>Plan Type</th>
                                        <th>Price</th>
                                        <th>Expire Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($features as $feature)
                                        <tr>
                                            <td>{{ $loop->iteration }} </td>
                                            <td>{{ $feature?->plan?->name }}</td>
                                            <td>{{ ucfirst($feature?->plan?->plan_type) }}</td>
                                            <td>{{ $feature?->plan?->price }}</td>
                                            <td>{{ Carbon\Carbon::now()->gt(subscriptionExpireDate($feature->id)) ? 'Expired' : Carbon\Carbon::parse(subscriptionExpireDate($feature->id))->format('d M Y') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
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
