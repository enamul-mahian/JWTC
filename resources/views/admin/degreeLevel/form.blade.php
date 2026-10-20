<div class="row">
    <div class="col-md-6 mb-4">
        {!! Form::label('name', 'Degree Level') !!}
        <span class="text-danger">*</span>
        {!! Form::text('name', null, [
            'class' => 'form-control',
            'id' => 'tb-fname',
            'placeholder' => 'degree level name',
            'required',
        ]) !!}
        @error('name')
            <p class="mb-0 position-absolute text-danger">
                <i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}
            </p>
        @enderror
    </div>
    <div class="col-md-6 mb-4">
        {!! Form::label('status', 'Status') !!}
        <span class="text-danger">*</span>
        {!! Form::select('status', [1 => 'Published', 0 => 'Unpublished'], null, [
            'class' => 'form-select',
            'placeholder' => 'status',
            'required',
        ]) !!}
        @error('status')
            <p class="mb-0 position-absolute text-danger">
                <i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}
            </p>
        @enderror
    </div>
</div>
