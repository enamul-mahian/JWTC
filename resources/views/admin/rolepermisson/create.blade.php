@extends('dashboard.layout.dasboardMaster')
@section('title', 'Role Create')
@push('style')
    <style>
        .form-check-input {
            border: 1.25px solid #0355dd !important;
        }
    </style>
@endpush
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                @can('role.create')
                <div class="card-body">
                    <h5 class="mb-3">@yield('title')</h5>
                    {{ Form::open(['route' => 'role.store', 'method' => 'post']) }}
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            {!! Form::label('name', 'Name *') !!}
                            {!! Form::text('name', null, ['class' => 'form-control', 'id' => 'tb-fname', 'placeholder' => 'Role name', 'required']) !!}
                            @error('name')
                                <p class="mb-0 position-absolute text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <table class="table table-borderless">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Index</th>
                                    <th>Create</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        User
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('user.index', $permission->pluck('name')->toArray()) }}
                                            value="user.index" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('user.create', $permission->pluck('name')->toArray()) }}
                                            value="user.create" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('user.edit', $permission->pluck('name')->toArray()) }}
                                            value="user.edit" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('user.delete', $permission->pluck('name')->toArray()) }}
                                            value="user.delete" id="flexCheckDefault_1">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Role
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('role.index', $permission->pluck('name')->toArray()) }}
                                            value="role.index" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('role.create', $permission->pluck('name')->toArray()) }}
                                            value="role.create" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('role.edit', $permission->pluck('name')->toArray()) }}
                                            value="role.edit" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('role.delete', $permission->pluck('name')->toArray()) }}
                                            value="role.delete" id="flexCheckDefault_1">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Job Categories
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('job-categories.index', $permission->pluck('name')->toArray()) }}
                                            value="job-categories.index" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('job-categories.create', $permission->pluck('name')->toArray()) }}
                                            value="job-categories.create" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('job-categories.edit', $permission->pluck('name')->toArray()) }}
                                            value="job-categories.edit" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('job-categories.delete', $permission->pluck('name')->toArray()) }}
                                            value="job-categories.delete" id="flexCheckDefault_1">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Skills
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('skills.index', $permission->pluck('name')->toArray()) }}
                                            value="skills.index" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('skills.create', $permission->pluck('name')->toArray()) }}
                                            value="skills.create" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('skills.edit', $permission->pluck('name')->toArray()) }}
                                            value="skills.edit" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('skills.delete', $permission->pluck('name')->toArray()) }}
                                            value="skills.delete" id="flexCheckDefault_1">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Company Size
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('company-size.index', $permission->pluck('name')->toArray()) }}
                                            value="company-size.index" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('company-size.create', $permission->pluck('name')->toArray()) }}
                                            value="company-size.create" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('company-size.edit', $permission->pluck('name')->toArray()) }}
                                            value="company-size.edit" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('company-size.delete', $permission->pluck('name')->toArray()) }}
                                            value="company-size.delete" id="flexCheckDefault_1">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Industry Type
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('industry-type.index', $permission->pluck('name')->toArray()) }}
                                            value="industry-type.index" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('industry-type.create', $permission->pluck('name')->toArray()) }}
                                            value="industry-type.create" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('industry-type.edit', $permission->pluck('name')->toArray()) }}
                                            value="industry-type.edit" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('industry-type.delete', $permission->pluck('name')->toArray()) }}
                                            value="industry-type.delete" id="flexCheckDefault_1">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Job Type
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('job-type.index', $permission->pluck('name')->toArray()) }}
                                            value="job-type.index" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('job-type.create', $permission->pluck('name')->toArray()) }}
                                            value="job-type.create" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('job-type.edit', $permission->pluck('name')->toArray()) }}
                                            value="job-type.edit" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('job-type.delete', $permission->pluck('name')->toArray()) }}
                                            value="job-type.delete" id="flexCheckDefault_1">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Location
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('location.index', $permission->pluck('name')->toArray()) }}
                                            value="location.index" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('location.create', $permission->pluck('name')->toArray()) }}
                                            value="location.create" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('location.edit', $permission->pluck('name')->toArray()) }}
                                            value="location.edit" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('location.delete', $permission->pluck('name')->toArray()) }}
                                            value="location.delete" id="flexCheckDefault_1">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Degree Level
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('degree-level.index', $permission->pluck('name')->toArray()) }}
                                            value="degree-level.index" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('degree-level.create', $permission->pluck('name')->toArray()) }}
                                            value="degree-level.create" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('degree-level.edit', $permission->pluck('name')->toArray()) }}
                                            value="degree-level.edit" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('degree-level.delete', $permission->pluck('name')->toArray()) }}
                                            value="degree-level.delete" id="flexCheckDefault_1">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Degree Name
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('degree-name.index', $permission->pluck('name')->toArray()) }}
                                            value="degree-name.index" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('degree-name.create', $permission->pluck('name')->toArray()) }}
                                            value="degree-name.create" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('degree-name.edit', $permission->pluck('name')->toArray()) }}
                                            value="degree-name.edit" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('degree-name.delete', $permission->pluck('name')->toArray()) }}
                                            value="degree-name.delete" id="flexCheckDefault_1">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Front Web Page
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('page.index', $permission->pluck('name')->toArray()) }}
                                            value="page.index" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('page.create', $permission->pluck('name')->toArray()) }}
                                            value="page.create" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('page.edit', $permission->pluck('name')->toArray()) }}
                                            value="page.edit" id="flexCheckDefault_1">
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('page.delete', $permission->pluck('name')->toArray()) }}
                                            value="page.delete" id="flexCheckDefault_1">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Active Job
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('active-job', $permission->pluck('name')->toArray()) }}
                                            value="active-job" id="flexCheckDefault_1">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        In Active Job
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('inactive-job', $permission->pluck('name')->toArray()) }}
                                            value="inactive-job" id="flexCheckDefault_1">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Employer List
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('employer-list', $permission->pluck('name')->toArray()) }}
                                            value="employer-list" id="flexCheckDefault_1">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Employer Job History
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('employer-job-history', $permission->pluck('name')->toArray()) }}
                                            value="employer-job-history" id="flexCheckDefault_1">
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        Employer Cv Downloads
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('employer-cv-downloads', $permission->pluck('name')->toArray()) }}
                                            value="employer-cv-downloads" id="flexCheckDefault_1">
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        Application Setting
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('application-setting', $permission->pluck('name')->toArray()) }}
                                            value="application-setting" id="flexCheckDefault_1">
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        Email Setting
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('email,setting', $permission->pluck('name')->toArray()) }}
                                            value="email,setting" id="flexCheckDefault_1">
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        Cache Clear
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('cache-clear', $permission->pluck('name')->toArray()) }}
                                            value="cache-clear" id="flexCheckDefault_1">
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        Active JobSeeker
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('active-jobSeeker', $permission->pluck('name')->toArray()) }}
                                            value="active-jobSeeker" id="flexCheckDefault_1">
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        Inactive JobSeeker
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('inactive-jobSeeker', $permission->pluck('name')->toArray()) }}
                                            value="inactive-jobSeeker" id="flexCheckDefault_1">
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        JobSeeker History
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('jobSeeker-history', $permission->pluck('name')->toArray()) }}
                                            value="jobSeeker-history" id="flexCheckDefault_1">
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        Send Mass Mail
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('sendMassMail ', $permission->pluck('name')->toArray()) }}
                                            value="sendMassMail" id="flexCheckDefault_1">
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        Contact Message
                                    </td>
                                    <td>
                                        <input class="form-check-input me-2" type="checkbox" name="permision[]"
                                            {{ in_array('contact-message ', $permission->pluck('name')->toArray()) }}
                                            value="contact-message" id="flexCheckDefault_1">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    {!! Form::button('Submit', ['class' => ' btn btn-primary mt-4', 'type' => 'submit']) !!}
                    <a href="{{ route('user-permission.index') }}">
                        <button class="btn btn-danger mt-4 ms-2" type="button">Back</button>
                    </a>
                    {{ Form::close() }}
                </div>
                @endcan
            </div>
        </div>
    </div>
@endsection
