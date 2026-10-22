<div class="right-sideabr">
    <h4>{{ __('Manage Account') }}</h4>
    <ul class="list-item">

        @can('employer-dashboard')
            <li>
                <a href="{{ route('employer.dashboard') }}"
                    class="{{ Route::currentRouteName() == 'employer.dashboard' ? 'active' : '' }}">{{ __('messages.JobLists') }}</a>
            </li>
        @endcan

        @can('post-job')
            <li>
                <a href="{{ route('jobs.create') }}"
                    class="{{ Route::currentRouteName() == 'jobs.create' ? 'active' : '' }}">{{ __('messages.PostAJobs') }}</a>
            </li>
        @endcan
        @can('archive-job')
            <li>
                <a href="{{ route('archive-jobs.index') }}"
                    class="{{ Route::currentRouteName() == 'archive-jobs.index' ? 'active' : '' }}">{{ __('messages.ArchiveJobs') }}</a>
            </li>
        @endcan

        @can('job-manage')
            <li>
                <a href="{{ route('manage.job') }}"
                    class="{{ Route::currentRouteName() == 'manage.job' ? 'active' : '' }}">{{ __('messages.JobManage') }}</a>
            </li>
        @endcan

        @can('company-information')
            <li>
                <a href="{{ route('company.information') }}"
                    class="{{ Route::currentRouteName() == 'company.information' ? 'active' : '' }}">{{ __('messages.CompanyInformation') }}</a>
            </li>
        @endcan

        @can('post-job')
            <li>
                <a href="{{ route('interviews.manage.index') }}"
                   class="{{ Route::is('interviews.manage.*') ? 'active' : '' }}">
                    Online Interviews
                </a>
            </li>
        @endcan

        @if(config('application_chat.enabled') && auth()->user()?->user_type === \App\Enum\UserType::EMPLOYER && auth()->user()->can('post-job'))
            <li>
                <a href="{{ route('application-chat.index') }}"
                   class="{{ Route::is('application-chat.*') ? 'active' : '' }}">Applicant Messages</a>
            </li>
        @endif

        @if(config('support_center.enabled') && auth()->user()?->user_type === \App\Enum\UserType::EMPLOYER)
            <li><a href="{{ route('support.index') }}" class="{{ Route::is('support.*') ? 'active' : '' }}">Support &amp; Refunds</a></li>
        @endif

        {{-- TODO Something to add --}}

        <li>
            <a href="{{ route('employer-profile-update.page', Auth::user()->id) }}"
                class="{{ Route::currentRouteName() == 'employer-profile-update.page' ? 'active' : '' }}">{{ __('messages.UpdateProfile') }}</a>
        </li>

        {{-- TODO Something to add --}}

        <li>
            <form action="{{ route('logout') }}" method="post">
                @csrf
                <button class="btn btn-common">{{ __('messages.SingOut') }}</button>
            </form>
        </li>
    </ul>
</div>

@push('js')
    <script>
        $(document).ready(function() { 
            $('.autoRecvEmail').on('change', function() {
                let value = this.checked ? 1 : 0;
                $(this).val(value);

                axios.get('/auto-email-receive/' + value)
                    .then(function(res) {
                        if (res.status === 1) {
                            toastr.success("Auto email receive active success");
                        }else{
                            toastr.success("Auto email receive inactive success");
                        }
                    })
                    .catch(function(error) {
                        toastr.error("There was an error processing your request.");
                    });
            });
        });
    </script>
@endpush
