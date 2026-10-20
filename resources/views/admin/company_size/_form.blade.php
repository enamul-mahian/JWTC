<form action="{{ route('company-size.' . $action, isset($companySize) ? $companySize->id : '') }}" method="POST">
    @csrf

    @if ($action == 'update')
        @method('PUT')
    @endif

    <label for="title">Title <span class="text-danger"> * </span> </label>
    <input type="text" class="form-control" name="title" value="{{ old('title', isset($companySize) ? $companySize->title : '') }}" required>
    @error('title')
        <div>{{ $message }}</div>
    @enderror

    <label for="status" class="mt-4">Status <span class="text-danger"> * </span> </label>
    <select name="status"  class="form-control" required>
        <option value="1" {{ old('status', isset($companySize) ? $companySize->status : '') == '1' ? 'selected' : '' }}>Active</option>
        <option value="0" {{ old('status', isset($companySize) ? $companySize->status : '') == '0' ? 'selected' : '' }}>Inactive</option>
    </select>
    @error('status')
        <div>{{ $message }}</div>
    @enderror
    <p> </p>
    {!! Form::button('Submit', ['class' => ' btn  btn-primary', 'type' => 'submit']) !!}
    <a href="{{ route('company-size.index') }}">
        <button class="btn btn-danger ms-2" type="button">Back</button>
    </a>
</form>