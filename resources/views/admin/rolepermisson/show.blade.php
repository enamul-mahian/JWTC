@extends('dashboard.layout.dasboardMaster')
@section('title', 'Update Role')
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
                @can('role.edit')
                    <div class="card-body">
                        <h5 class="mb-3">
                            <strong>   {{ $role->name }} </strong>  
                        </h5>
                        <div class="row">
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
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('user.index', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="user.index" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('user.create', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="user.create" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('user.edit', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="user.edit" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('user.delete', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="user.delete" id="flexCheckDefault_1">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Role
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('role.index', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="role.index" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('role.create', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="role.create" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('role.edit', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="role.edit" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('role.delete', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="role.delete" id="flexCheckDefault_1">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Job Categories
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('job-categories.index', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="job-categories.index" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('job-categories.create', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="job-categories.create" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('job-categories.edit', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="job-categories.edit" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('job-categories.delete', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="job-categories.delete" id="flexCheckDefault_1">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Skills
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('skills.index', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="skills.index" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('skills.create', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="skills.create" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('skills.edit', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="skills.edit" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('skills.delete', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="skills.delete" id="flexCheckDefault_1">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Company Size
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('company-size.index', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="company-size.index" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('company-size.create', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="company-size.create" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('company-size.edit', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="company-size.edit" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('company-size.delete', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="company-size.delete" id="flexCheckDefault_1">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Industry Type
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('industry-type.index', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="industry-type.index" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('industry-type.create', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="industry-type.create" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('industry-type.edit', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="industry-type.edit" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('industry-type.delete', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="industry-type.delete" id="flexCheckDefault_1">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Job Type
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('job-type.index', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="job-type.index" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('job-type.create', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="job-type.create" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('job-type.edit', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="job-type.edit" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('job-type.delete', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="job-type.delete" id="flexCheckDefault_1">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Location
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('location.index', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="location.index" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('location.create', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="location.create" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('location.edit', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="location.edit" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('location.delete', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="location.delete" id="flexCheckDefault_1">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Degree Level
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('degree-level.index', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="degree-level.index" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('degree-level.create', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="degree-level.create" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('degree-level.edit', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="degree-level.edit" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('degree-level.delete', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="degree-level.delete" id="flexCheckDefault_1">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Degree Name
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('degree-name.index', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="degree-name.index" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('degree-name.create', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="degree-name.create" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('degree-name.edit', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="degree-name.edit" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('degree-name.delete', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="degree-name.delete" id="flexCheckDefault_1">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Front Web Page
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('page.index', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="page.index" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('page.create', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="page.create" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('page.edit', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="page.edit" id="flexCheckDefault_1">
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('page.delete', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="page.delete" id="flexCheckDefault_1">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Active Job
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('active-job', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="active-job" id="flexCheckDefault_1">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            In Active Job
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('inactive-job', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="inactive-job" id="flexCheckDefault_1">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Employer List
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('employer-list', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="employer-list" id="flexCheckDefault_1">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Employer Job History
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('employer-job-history', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="employer-job-history" id="flexCheckDefault_1">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            Employer Cv Downloads
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('employer-cv-downloads', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="employer-cv-downloads" id="flexCheckDefault_1">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            Application Setting
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('application-setting', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="application-setting" id="flexCheckDefault_1">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            Email Setting
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('email-setting', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="email-setting" id="flexCheckDefault_1">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            Cache Clear
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('cache-clear', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="cache-clear" id="flexCheckDefault_1">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            Active JobSeeker
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('active-jobSeeker', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="active-jobSeeker" id="flexCheckDefault_1">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            Inactive JobSeeker
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('inactive-jobSeeker', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="inactive-jobSeeker" id="flexCheckDefault_1">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            JobSeeker History
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('jobSeeker-history', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="jobSeeker-history" id="flexCheckDefault_1">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            Send Mass Mail
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('sendMassMail', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="sendMassMail" id="flexCheckDefault_1">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            Contact Message
                                        </td>
                                        <td>
                                            <input disabled class="form-check-input me-2" type="checkbox" name="permision[]"
                                                {{ in_array('contact-message', $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                                                value="contact-message" id="flexCheckDefault_1">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>
                       
                        <a href="{{ route('user-permission.index') }}">
                            <button class="btn btn-danger mt-4 ms-2" type="button"> Back</button>
                        </a>
                        {{ Form::close() }}
                    </div>
                @endcan
            </div>
        </div>
    </div>
@endsection
