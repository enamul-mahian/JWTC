@extends('dashboard.layout.dasboardMaster')
@section('title', "Create Page")
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3"> {{ __('Create Page') }}</h5>
                    <form action="{{ route('pages.store') }}" method="post">
                        @csrf
                
                        <!-- Name Field -->
                        <div class="form-group mb-2">
                            <label for="name">Name:</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                
                        <!-- URL Field -->
                        <div class="form-group mb-2">
                            <label for="url">URL:</label>
                            <input type="text" class="form-control" id="url" name="url" required>
                        </div>
                
                        <!-- Content Field -->
                        <div class="form-group mb-2">
                            <label for="content">Content:</label>
                            <textarea class="form-control" id="content" name="content" rows="5"></textarea>
                        </div>
                
                        <!-- Order By Field -->
                        <div class="form-group mb-2">
                            <label for="order_by">Order By:</label>
                            <input type="number" class="form-control" id="order_by" name="order_by" required>
                        </div>
                
                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <a href="{{ route('pages.index') }}">
                            <button class="btn btn-danger ms-2" type="button">Back</button>
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    ClassicEditor
        .create( document.querySelector( '#content' ),{
            ckfinder: {
                uploadUrl: '{{route("text-editor-file-upload").'?_token='.csrf_token()}}',
            }
        })
        .catch( error => {
            
        } );

    document.getElementById("order_by").addEventListener("input", function() {
        if (this.value < 0) {
            this.value = 0;
        }
    });
</script>
@endpush
