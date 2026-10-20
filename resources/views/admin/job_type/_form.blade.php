<form action="{{ route('job-type.' . $action, isset($jobType) ? $jobType->id : '') }}" method="POST">
    @csrf

    @if ($action == 'update')
        @method('PUT')
    @endif

    <label for="name">name: <span>*</span> </label>
    <input type="text" class="form-control" name="name" value="{{ old('name', isset($jobType) ? $jobType->name : '') }}" required>
    @error('name')
        <div>{{ $message }}</div>
    @enderror

    <label for="description">Description:</label>
    <input type="text" class="form-control" name="description" value="{{ old('description', isset($jobType) ? $jobType->description : '') }}">
    @error('description')
        <div>{{ $message }}</div>
    @enderror

    <label for="status">Status:</label>
    <select name="status"  class="form-control" required>
        <option value="1" {{ old('status', isset($jobType) ? $jobType->status : '') == '1' ? 'selected' : '' }}>Active</option>
        <option value="0" {{ old('status', isset($jobType) ? $jobType->status : '') == '0' ? 'selected' : '' }}>Inactive</option>
    </select>
    @error('status')
        <div>{{ $message }}</div>
    @enderror
    <p> </p>
    {!! Form::button('Submit', ['class' => ' btn me-2 btn-primary', 'type' => 'submit']) !!}
    <a href="{{ route('job-type.index') }}">
        <button class="btn btn-danger" type="button">Back</button>
    </a>
</form>