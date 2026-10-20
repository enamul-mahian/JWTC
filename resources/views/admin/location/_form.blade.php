<form action="{{ route('location.' . $action, isset($location) ? $location->id : '') }}" method="POST">
    @csrf
    @if ($action == 'update')
        @method('PUT')
    @endif

    <label for="name">name: <span>*</span> </label>
    <input type="text" class="form-control" name="name"
        value="{{ old('name', isset($location) ? $location->name : '') }}" required>
    @error('name')
        <div class="text-danger">{{ $message }}</div>
    @enderror
    {{-- 
    <label for="description">Description:</label>
    <input type="text" class="form-control" name="description"
        value="{{ old('description', isset($location) ? $location->description : '') }}">
    @error('description')
        <div  class="text-danger">{{ $message }}</div>
    @enderror --}}

    <label for="code">code:</label>
    <input type="text" class="form-control" name="code"
        value="{{ old('code', isset($location) ? $location->code : '') }}" placeholder="enter contry code">
    @error('code')
        <div class="text-danger">{{ $message }}</div>
    @enderror

    <label for="status">Status:</label>
    <select name="status" class="form-control" required>
        <option value="{{ App\Enum\StatusType::ACTIVE }}"
            {{ old('status', isset($location)) == App\Enum\StatusType::ACTIVE ? 'selected' : '' }}>Active</option>
        <option value="{{ App\Enum\StatusType::INACTIVE }}"
            {{ old('status', isset($location) ? $location->status : '') == App\Enum\StatusType::INACTIVE ? 'selected' : '' }}>
            Inactive</option>
    </select>
    @error('status')
        <div class="text-danger">{{ $message }}</div>
    @enderror
    <p> </p>
    {!! Form::button('Submit', ['class' => ' btn  btn-primary', 'type' => 'submit']) !!}
    <a href="{{ route('location.index') }}">
        <button class="btn btn-danger" type="button">Back</button>
    </a>
</form>
