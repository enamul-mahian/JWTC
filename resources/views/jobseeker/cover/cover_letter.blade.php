@extends('frontend.layout.frontMaster')
@section('title', 'Cover Letter')
@Section('content')
    <div class="page-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="inner-header">
                        <h3>  {{ __('messages.MyLibrary') }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="content">
        <div class="container">
            <div class="row">
                <ul class="nav nav-tabs" id="myTabs">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab1" data-toggle="tab" href="#content1">{{ __('messages.Detailspersonorganisation') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab2" data-toggle="tab" href="#content2">{{ __('messages.CitySubject') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab3" data-toggle="tab" href="#content3">{{ __('messages.Content') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab4" data-toggle="tab" href="#content4">{{ __('messages.Closing') }}</a>
                    </li>
                </ul>

                <div class="tab-content">

                    <div id="content1" class="tab-pane fade show active">
                        <div class="container mt-4">
                            <form id="form_data" method="POST" action="{{ route('save-person-details') }}">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="input1">{{ __('messages.Title') }}</label>
                                        <input type="text" name="title" class="form-control"
                                            placeholder="{{ __('messages.Entertitle') }}" value="{{ $cover->title ?? null }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="input2">{{ __('messages.Lastnames') }}</label>
                                        <input type="text" name="last_name" class="form-control" id="input2"
                                            placeholder="{{ __('messages.EnterLastnames') }}" value="{{ $cover->last_name ?? null }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="input3">{{ __('messages.Firstname') }}</label>
                                        <input type="text" name="first_name" class="form-control" id="input3"
                                            placeholder="{{ __('messages.Enterfirstname') }}" value="{{ $cover->first_name ?? null }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="input3">{{ __('messages.Nameoforganisation') }}</label>
                                        <input type="text" name="organization" class="form-control" id="input3"
                                            placeholder="{{ __('messages.Organisation') }}" value="{{ $cover->organization ?? null }}">
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label for="input3">{{ __('messages.Addressline1') }}</label>
                                        <textarea name="address_line_1" class="form-control">{{ $cover->address_line_1 ?? null }}</textarea>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label for="input3">{{ __('messages.Addressline2') }}</label>
                                        <textarea name="address_line_2" class="form-control">{{ $cover->address_line_2 ?? null }}</textarea>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <button class="btn-sm btn-info btn">{{ __('messages.Save') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div id="content2" class="tab-pane fade">
                        <div class="container mt-4">
                            <form id="form_data_1" method="POST" action="{{ route('save-person-contact-details') }}">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="input1">{{ __('messages.City') }}</label>
                                        <input type="text" name="city" class="form-control" id="input1"
                                            placeholder="{{ __('messages.Entercity') }}" value="{{ $cover->city ?? null }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="input2">{{ __('messages.Date') }}</label>
                                        <input type="date" name="date" class="form-control" id="input2"
                                            placeholder="{{ __('messages.Date') }}" value="{{ $cover->date ?? null }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="input3">{{ __('messages.Subject') }}</label>
                                        <input type="text" name="subject" class="form-control" id="input3"
                                            placeholder="{{ __('messages.Entersubject') }}" value="{{ $cover->subject ?? null }}">
                                    </div>

                                    <div class="form-group col-md-12">
                                        <button class="btn-sm btn-info btn">{{ __('messages.Save') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div id="content3" class="tab-pane fade">
                        <div class="container mt-4">
                            <form id="form_data_2" method="POST" action="{{ route('save-person-content-details') }}">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="input3">{{ __('messages.Openingsalutation') }}</label>
                                        <input type="text" name="openin_salutation" class="form-control" id="input3"
                                            placeholder="{{ __('messages.Enteropeningsalutation') }}" value="{{ $cover->openin_salutation ?? null }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="input3">{{ __('messages.Lastnames') }}</label>
                                        <input type="text" name="opening_last_name" class="form-control" id="input3"
                                            placeholder="{{ __('messages.EnterLastnames') }}" value="{{ $cover->opening_last_name ?? null }}">
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label for="input3"> {{ __('messages.MainBody') }}</label>
                                        <textarea name="content" id="summernote" class="form-control">{{ $cover->content ?? null }}</textarea>
                                    </div>

                                    <div class="form-group col-md-12">
                                        <button class="btn-sm btn-info btn">{{ __('messages.Save') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div id="content4" class="tab-pane fade">
                        <div class="container mt-4">
                            <form id="form_data_3" method="POST" action="{{ route('save-person-closing-details') }}" enctype="multipart/form-data">
                                <div class="form-row">

                                    <div class="form-group col-md-6">
                                        <label for="input3">{{ __('messages.Closingsalutation') }}</label>
                                        <input type="text" class="form-control" name="closing_salutation" value="{{ $cover->closing_salutation ?? null }}">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="input3">{{ __('messages.Name') }}</label>
                                        <input type="text" class="form-control" name="your_name" value="{{ $cover->your_name ?? null }}">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="input3">{{ __('messages.SignatureImage') }}</label>
                                        <input type="file" class="form-control" name="signature">
                                        @if(isset($cover) && isset($cover->signature))
                                            <img src="{{ asset('assets/images/signature/'.$cover->signature) }}" style="height: 60px" alt="" srcset="">
                                        @endif


                                    </div>

                                    <div class="form-group col-md-12">
                                        <button class="btn-sm btn-info btn">{{ __('messages.Save') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    @endsection

    @push('script')
    <script src="{{ asset('frontend/assets/script/cover_letter.js') }}"></script>
        <script>
            $(document).ready(function () {
                $('#myTabs').tab();
            });
        </script>
    @endpush
