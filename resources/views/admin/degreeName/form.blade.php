<div class="row">
    <div class="col-lg-6">
        {!! Form::label('degree_lavel_id', 'Degree Level') !!}
        <span class="text-danger">*</span>
        {!! Form::select('degree_lavel_id', $degreeLevel, null, ['class' => 'form-select', 'placeholder'=>'select degree level', 'required'] ) !!}
    </div>
    <div class="col-md-6 mb-4">
        {!! Form::label('name', 'Name') !!}
        <span class="text-danger">*</span>
        {!! Form::text('name', null, [
            'class' => 'form-control',
            'id' => 'tb-fname',
            'placeholder' => 'degree name',
            'required'
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
            'required'
        ]) !!}
        @error('status')
            <p class="mb-0 position-absolute text-danger">
                <i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}
            </p>
        @enderror
    </div>
</div>
