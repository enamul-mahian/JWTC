<script src="
https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.all.min.js
"></script>
<script src="{{ asset('backend/dist/libs/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('backend/dist/libs/simplebar/dist/simplebar.min.js') }}"></script>
<script src="{{ asset('backend/dist/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
<!--  core files -->
{{-- ck editor --}}
<script src="https://cdn.ckeditor.com/ckeditor5/40.2.0/classic/ckeditor.js"></script>

<script src="{{ asset('backend/dist/js/app.min.js') }}"></script>
<script src="{{ asset('backend/dist/js/app.init.js') }}"></script>
<script src="{{ asset('backend/dist/js/app-style-switcher.js') }}"></script>
<script src="{{ asset('backend/dist/js/sidebarmenu.js') }}"></script>

<!--  current page js files -->
<script src="{{ asset('backend/dist/libs/owl.carousel/dist/owl.carousel.min.js') }}"></script>
<script src="{{ asset('backend/dist/js/dashboard.js') }}"></script>
<script src="{{ asset('backend/dist/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/dist/js/datatable/datatable-basic.init.js') }}"></script>
<script src="{{ asset('backend/dist/js/custom.js') }}"></script>
<script src="{{ asset('frontend/assets/js/toastr.min.js') }}"></script>
{{-- SweetAlert2 is already loaded from its versioned CDN above. --}}

<script>
    // success message popup notification
    @if(Session::has('success'))
        toastr.success(@json(Session::get('success')));
    @endif

    // info message popup notification
    @if(Session::has('info'))
        toastr.info(@json(Session::get('info')));
    @endif

    // warning message popup notification
    @if(Session::has('warning'))
        toastr.warning(@json(Session::get('warning')));
    @endif

    // error message popup notification
    @if(Session::has('error'))
        toastr.error(@json(Session::get('error')));
    @endif
</script>

<script>
    @foreach($errors->all() as $error)
        toastr.error(@json($error));
    @endforeach
</script>