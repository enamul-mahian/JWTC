@extends('frontend.layout.frontMaster')
@section('title' , $page->name)
@section('content')
<div class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="inner-header">
                    <h3>{{ $page->name }}</h3>
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
                        {!! $page->content !!}
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection


    