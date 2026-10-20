@extends('dashboard.layout.dasboardMaster')
@section('title', "Ads setup")
@push('styles')
    <style>
        .desc-box {
            max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
    </style>
@endpush

@section('content')
    <section class="pc-container">
        <div class="pc-content">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="h3">Ads Settings</span>
                        </div>
                        <div class="card-body">

                            <form action="{{ route('ads-update') }}" method="post">
                                @csrf
                                @method('PUT')                      
                                <div class="row">
                                    <div class="mb-3">
                                        <label for="">Square Ads <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="square_ads" placeholder="Square Ads" required rows="10">{{ $arr['square_ads'] ?? '' }}</textarea>
                                        <b><small class="text-danger">{{ $errors->first('square_ads') }}</small></b>
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Horizontal Ads<span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="horizontal_ads" placeholder="horizontal ads" required rows="10">{{ $arr['horizontal_ads'] ?? '' }}</textarea>
                                        <b><small class="text-danger">{{ $errors->first('horizontal_ads') }}</small></b>
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Vertical Ads<span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="vertical_ads" placeholder="vertical ads" required rows="10">{{ $arr['vertical_ads'] ?? '' }}</textarea>
                                        <b><small class="text-danger">{{ $errors->first('vertical_ads') }}</small></b>
                                    </div>
                                    <div class="mb-3">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>    
                                </div>
                            </form>
                            
                        </div>
                    </div>
                </div>
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            {!! $arr->square_ads ?? 'No square ads available' !!}
                            <hr>
                            {!! $arr->horizontal_ads ?? 'No horizontal ads available' !!}
                            <hr>
                            {!! $arr->vertical_ads ?? 'No vertical ads available' !!}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection
