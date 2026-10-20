<form action="{{ route('industry-type.' . $action, isset($industryType) ? $industryType->id : '') }}" method="POST">
    @csrf

    @if ($action == 'update')
        @method('PUT')
    @endif

    <label for="name">name: <span>*</span> </label>
    <input type="text" class="form-control" name="name"
        value="{{ old('name', isset($industryType) ? $industryType->name : '') }}" required>
    @error('name')
        <div>
            <p class="text-danger"> {{ $message }} </p>
        </div>
    @enderror

    <label for="description">Description:</label>
    <input type="text" class="form-control" name="description"
        value="{{ old('description', isset($industryType) ? $industryType->description : '') }}">
    @error('description')
        <div>
            <p class="text-danger">{{ $message }} </p>
        </div>
    @enderror

    <label for="status">Status:</label>
    <select name="status" class="form-control" required>
        <option value="1"
            {{ old('status', isset($industryType) ? $industryType->status : '') == '1' ? 'selected' : '' }}>Active
        </option>
        <option value="0"
            {{ old('status', isset($industryType) ? $industryType->status : '') == '0' ? 'selected' : '' }}>Inactive
        </option>
    </select>
    @error('status')
        <div>
            <p class="text-danger"> {{ $message }} </p>
        </div>
    @enderror
    <p> </p>
    {!! Form::button('Submit', ['class' => ' btn  btn-primary', 'type' => 'submit']) !!}
    <a href="{{ route('industry-type.index') }}">
        <button class="btn btn-danger" type="button">Back</button>
    </a>
</form>
