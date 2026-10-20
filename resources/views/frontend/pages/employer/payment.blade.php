{{-- @extends('frontend.layout.frontMaster')
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

                        <div class="col-md-10 col-md-offset-1">
                            <div class="panel panel-default credit-card-box">
                                <div class="panel-heading display-table">
                                    <div class="row display-tr">
                                        <h3 class="panel-title display-td">Payment Details</h3>
                                        <div class="display-td">
                                            <img class="img-responsive pull-right" style="width: 500px; height:115px"
                                                src="https://www.business2community.com/wp-content/uploads/2023/03/stripe-payments-platform-760x428-1.png">
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <form role="form" action="" method="post" class="require-validation" data-cc-on-file="false"
                                        data-stripe-publishable-key="{{ env('STRIPE_KEY') }}" id="payment-form">
                                        @csrf
                                        <div class="form-row">
                                            <div class="form-group col-12 required">
                                                <label class="control-label">Name on Card</label>
                                                <input class="form-control" size="4" type="text" placeholder="Card Holder Name">
                                            </div>
                                        </div>
                                
                                        <div class="form-row">
                                            <div class="form-group col-12 required">
                                                <label class="control-label">Card Number</label>
                                                <input autocomplete="off" class="form-control card-number" size="20" type="text"
                                                    placeholder="Card Number">
                                            </div>
                                        </div>
                                
                                        <div class="form-row">
                                            <div class="form-group col-12 col-md-4 required">
                                                <label class="control-label">CVC</label>
                                                <input autocomplete="off" class="form-control card-cvc" placeholder="ex. 311" size="4" type="text">
                                            </div>
                                
                                            <div class="form-group col-6 col-md-4 required">
                                                <label class="control-label">Expiration Month</label>
                                                <input class="form-control card-expiry-month" placeholder="MM" size="2" type="text">
                                            </div>
                                
                                            <div class="form-group col-6 col-md-4 required">
                                                <label class="control-label">Expiration Year</label>
                                                <input class="form-control card-expiry-year" placeholder="YYYY" size="4" type="text">
                                            </div>
                                        </div>
                                
                                        <div class="form-row">
                                            <div class="form-group col-12 error hide">
                                                <div class="alert alert-danger">Please correct the errors and try again.</div>
                                            </div>
                                        </div>
                                
                                        <div class="form-row">
                                            <div class="form-group col-12">
                                                <button class="btn btn-primary btn-lg btn-block" type="submit">Pay Now</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                
                            </div>
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
@endpush --}}


<!DOCTYPE html>
<html>

<head>
    <title>Subscription Payment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

    <style type="text/css">
        .panel-title {
            display: inline;
            font-weight: bold;
        }

        .display-table {
            display: table;
        }

        .display-tr {
            display: table-row;
        }

        .display-td {
            display: table-cell;
            vertical-align: middle;
            width: 61%;
        }
    </style>

</head>

<body style="background-color: #ddd">
    <div class="dashboard-content-container" data-simplebar>
        <div class="dashboard-content-inner">
            <!-- Titlebar
        ================================================== -->
            <div style="margin-bottom: 40px;">
                <div class="container">
                    <div class="row text-center">
                        <div class="col-md-12">

                            <h2>Subscription Payment</h2>

                        </div>
                    </div>
                </div>
            </div>

            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-10 col-md-offset-1">
                        <div class="panel panel-default credit-card-box">
                            <div class="panel-heading display-table">
                                <div class="row display-tr">
                                    <h3 class="panel-title display-td">Payment Details</h3>
                                    <div class="display-td">
                                        <img class="img-responsive pull-right" style="width: 500px; height:115px"
                                            src="https://www.business2community.com/wp-content/uploads/2023/03/stripe-payments-platform-760x428-1.png">
                                    </div>
                                </div>
                            </div>
                            <div class="panel-body">

                                <form role="form" action="{{ route('stripe.post', $plan->id) }}" method="post"
                                    class="require-validation" data-cc-on-file="false"
                                    data-stripe-publishable-key="{{ env('STRIPE_KEY') }}" id="payment-form">
                                    @csrf
                                    <div class='form-row row'>
                                        <div class='col-xs-12 form-group required'>
                                            <label class='control-label'>Name on Card</label> <input
                                                class='form-control' size='4' type='text'
                                                placeholder="Card Holder Name">
                                        </div>
                                    </div>
                                    <div class='form-row row'>
                                        <div class='col-xs-12 form-group card required'>
                                            <label class='control-label'>Card Number</label> <input autocomplete='off'
                                                class='form-control card-number' size='20' type='text'
                                                placeholder="Card Number">
                                        </div>
                                    </div>
                                    <div class='form-row row'>
                                        <div class='col-xs-12 col-md-4 form-group cvc required'>
                                            <label class='control-label'>CVC</label> <input autocomplete='off'
                                                class='form-control card-cvc' placeholder='ex. 311' size='4'
                                                type='text'>
                                        </div>

                                        <div class='col-xs-12 col-md-4 form-group expiration required'>
                                            <label class='control-label'>Expiration Month</label> <input
                                                class='form-control card-expiry-month' placeholder='MM' size='2'
                                                type='text'>
                                        </div>

                                        <div class='col-xs-12 col-md-4 form-group expiration required'>
                                            <label class='control-label'>Expiration Year</label> <input
                                                class='form-control card-expiry-year' placeholder='YYYY' size='4'
                                                type='text'>
                                        </div>
                                    </div>

                                    <div class='form-row row'>
                                        <div class='col-md-12 error form-group hide'>
                                            <div class='alert-danger alert'>Please correct the errors and try
                                                again.</div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-xs-12">
                                            <button class="btn btn-primary btn-lg btn-block" type="submit">Pay
                                                Now</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script type="text/javascript">
    $(function() {

        var $form = $(".require-validation");
        $('form.require-validation').bind('submit', function(e) {
            var $form = $(".require-validation"),
                inputSelector = ['input[type=email]', 'input[type=password]',
                    'input[type=text]', 'input[type=file]',
                    'textarea'
                ].join(', '),
                $inputs = $form.find('.required').find(inputSelector),
                $errorMessage = $form.find('div.error'),
                valid = true;
            $errorMessage.addClass('hide');

            $('.has-error').removeClass('has-error');
            $inputs.each(function(i, el) {
                var $input = $(el);
                if ($input.val() === '') {
                    $input.parent().addClass('has-error');
                    $errorMessage.removeClass('hide');
                    e.preventDefault();
                }
            });

            if (!$form.data('cc-on-file')) {
                e.preventDefault();
                Stripe.setPublishableKey($form.data('stripe-publishable-key'));
                Stripe.createToken({
                    number: $('.card-number').val(),
                    cvc: $('.card-cvc').val(),
                    exp_month: $('.card-expiry-month').val(),
                    exp_year: $('.card-expiry-year').val()
                }, stripeResponseHandler);
            }
        });

        function stripeResponseHandler(status, response) {
            if (response.error) {
                $('.error')
                    .removeClass('hide')
                    .find('.alert')
                    .text(response.error.message);
            } else {
                /* token contains id, last4, and card type */
                var token = response['id'];

                $form.find('input[type=text]').empty();
                $form.append("<input type='hidden' name='stripeToken' value='" + token + "'/>");
                $form.get(0).submit();
            }
        }
    });
</script>

</html>
