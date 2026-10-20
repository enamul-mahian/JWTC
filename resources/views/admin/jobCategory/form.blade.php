<div class="row">
    <div class="col-md-6 mb-4">
        <label for="name">Name <span class="text-danger">*</span></label>
        {!! Form::text('name', null, [
            'class' => 'form-control',
            'id' => 'tb-fname',
            'placeholder' => 'job category name', 'required' => true
        ]) !!}
        @error('name')
            <p class="mb-0 position-absolute text-danger">
                <i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}
            </p>
        @enderror
    </div>
    <div class="col-md-6 mb-4">
        <label for="status">Status <span class="text-danger">*</span></label>
        {!! Form::select('status', [1 => 'Published', 0 => 'Unpublished'], null, [
            'class' => 'form-select',
            'placeholder' => 'job category status', 
            'required'
        ]) !!}
        @error('status')
            <p class="mb-0 position-absolute text-danger">
                <i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}
            </p>
        @enderror
    </div>
</div>
