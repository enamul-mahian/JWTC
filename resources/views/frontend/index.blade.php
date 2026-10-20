@extends('frontend.layout.frontMaster')

@section('title', 'Job Portal')

@section('content')

<style>
    /* =========================
       DESKTOP
       ========================= */

    .gallagher-desktop {
        width: 100%;
    }

    .gallagher-desktop img {
        width: 100%;
        height: auto;
        display: block;
    }

    .gallagher-mobile {
        display: none;
    }


    /* =========================
       MOBILE
       ========================= */

    @media (max-width: 768px) {

        .gallagher-desktop {
            display: none;
        }

        .gallagher-mobile {
            display: block;
            width: 100%;
            background: #eef4f8;
        }

        .mobile-panel {
            width: 100%;
            background-image: url('{{ asset('images/Gallagher.png') }}');
            background-repeat: no-repeat;
            background-size: 200% auto;
        }

        /* Left half of original image */
        .mobile-left {
            height: 58vw;
            background-position: left top;
        }

        /* Right half of original image */
        .mobile-right {
            height: 86vw;
            background-position: right top;
        }
    }
</style>


{{-- DESKTOP --}}
<div class="gallagher-desktop">
    <img
        src="{{ asset('images/Gallagher.png') }}"
        alt="Gallagher"
    >
</div>


{{-- MOBILE --}}
<div class="gallagher-mobile">
    <div class="mobile-panel mobile-left"></div>
    <div class="mobile-panel mobile-right"></div>
</div>

@endsection