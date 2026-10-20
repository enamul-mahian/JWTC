@extends('frontend.layout.frontMaster')
@section('title' , 'Contact US')
@section('content')
<div class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="inner-header">
                    <h3>{{ __('messages.Contact') }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<section id="contact" class="section">
    <div class="contact-form">
        <div class="container">
            <div class="row contact-form-area">
                <div class="col-md-12 col-lg-6 col-sm-12">
                    <div class="contact-block">
                        <h2>{{ __('messages.ContactForm') }}</h2>
                        <form action="{{ route('send-contact-data') }}" method="POST">
                            @csrf 
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="name" name="name" placeholder="{{ __('messages.Name') }}" required value="{{ old('name') }}">
                                        
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" placeholder="{{ __('messages.Email') }}" id="email" class="form-control"
                                            name="email" required value="{{ old('email') }}">
                                        
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <input type="text" placeholder="{{ __('messages.Subject') }}" id="msg_subject" class="form-control" name="subject"
                                            required value="{{ old('subject') }}">
                                        
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <textarea class="form-control" id="message" placeholder="{{ __('messages.YourMessage') }}" rows="5" name="message" required>{{ old('message') }}</textarea>
                                        
                                    </div>
                                    <div class="submit-button">
                                        <button class="btn btn-common disabled" id="submit" type="submit"
                                            style="pointer-events: all; cursor: pointer;">{{ __('messages.SendMessage') }}</button>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-md-12 col-lg-6 col-sm-12">
                    <div class="contact-right-area wow fadeIn">
                        <div class="contact-info">
                            <div class="single-contact">
                                <div class="contact-icon">
                                    <i class="lni-map-marker"></i>
                                </div>
                                <p>{{ __('messages.MainOffice') }}: {{ $setting->business_address }}<br> {{ __('messages.CustomerCenter') }}: {{ $setting->address_line }}</p>
                            </div>
                            <div class="single-contact">
                                <div class="contact-icon">
                                    <i class="lni-envelope"></i>
                                </div>
                                <p><a href="mailto:{{ $setting->business_email }}">{{ __('messages.CustomerSupport') }}: {{ $setting->business_email }}
                                </a></p>
                            </div>
                            <div class="single-contact">
                                <div class="contact-icon">
                                    <i class="lni-phone-handset"></i>
                                </div>
                                <p><a href="#">{{ __('messages.MainOffice') }}: {{ $setting->phone_1 }}</a></p>
                                <p><a href="#">{{ __('messages.CustomerSupport') }}: {{ $setting->phone_2 }}</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
