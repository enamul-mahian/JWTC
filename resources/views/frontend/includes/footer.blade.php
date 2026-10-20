<!-- Footer Area Start -->
{{-- <section class="footer-Content">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="widget">
                    <div class="footer-logo">
                        @if($setting->logo)
                        <img src="{{ asset('assets/images/setting/'.$setting->logo) }}" alt="" srcset="">
                        @else
                        <img src="{{ asset('frontend/assets/img/logo.png') }}">
                        @endif
                    </div>
                    <div class="textwidget">
                        <p>{{ $setting->address_line }}</p>
                        <p>{{ $setting->phone_1 }}</p>
                        <p>{{ $setting->phone_2 }}</p>
                    </div>
                </div>
            </div>


            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="widget">
                    <h3 class="block-title">Quick Links</h3>
                    <ul class="menu">

                        @if(isset($pages))
                            @foreach($pages as $page)
                                <li><a href="{{ route('get-page-details', $page->url) }}">{{ $page->name }}</a></li>
                            @endforeach
                        @endif

                    </ul>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="widget">
                    <h3 class="block-title">Quick Links</h3>
                    <ul class="menu">
                        @if(isset($pages))
                            @php($counter = 0)
                            @if($counter % 6 == 0)
                                @if($counter != 0)
                                    @foreach($pages as $page)
                                        <li><a href="#">{{ $page->name }}</a></li>
                                        @php($counter++)
                                    @endforeach
                                @endif
                            @endif
                        @endif
                    </ul>
                </div>
            </div>


            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="widget">
                    <h3 class="block-title">{{ __('messages.SubscribeNow') }}</h3>

                    <ul class="mt-3 footer-social">
                        <li><a class="facebook" href="{{ credential('facebook') }}"><i class="lni-facebook-filled"></i></a></li>
                        <li><a class="twitter" href="{{ credential('twitter') }}"><i class="lni-twitter-filled"></i></a></li>
                        <li><a class="linkedin" href="{{ credential('linkedin') }}"><i class="lni-linkedin-fill"></i></a></li>
                        <li><a class="google-plus" href="{{ credential('google') }}"><i class="lni-google-plus"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section> --}}

<div id="copyright">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="site-info text-center">
                    <p> {{ __('messages.DesignedandDevelopedby') }}<strong class="text-danger"> {{ __('messages.JobPortal') }}</strong></p>
                </div>
            </div>
        </div>
    </div>
</div>
