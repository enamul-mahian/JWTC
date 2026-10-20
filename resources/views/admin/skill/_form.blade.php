    <label for="title">Job Category <span class="text-danger">*</span></label>

    <select name="job_category_id" id="" class="form-select mb-4" required>
        <option value="">select job category</option>
        @foreach ($jobCategories as $jobCategory)
            <option
                @if (Route::currentRouteName() == 'skills.edit') {{ $skill->job_category_id == $jobCategory->id ? 'selected' : '' }} @endif
                value="{{ $jobCategory->id }}">{{ $jobCategory->name }}</option>
        @endforeach
    </select>

    <label for="title">Skill Name <span class="text-danger">*</span></label>
    <input type="text" class="form-control mb-4" name="skill_name"
        value="{{ old('skill_name', isset($skill) ? $skill->skill_name : '') }}" required>


    {!! Form::button('Submit', ['class' => ' btn  btn-primary', 'type' => 'submit']) !!}
    <a href="{{ route('skills.index') }}">
        <button class="btn btn-danger ms-2" type="button">Back</button>
    </a>
