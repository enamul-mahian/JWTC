<ul class="list-item" aria-label="My account">



    @can('job-apply')
        <li><a class="{{ Route::is('find-jobs') ? 'active' : null }}" href="{{ route('find-jobs') }}"
                >@include('jobseeker.partials.icon',['icon'=>'search']) Find Jobs </a></li>
    @endcan

    @can('view-profile')
        <li><a class="{{ Route::is('view-jobseeker-profile') ? 'active' : null }}"
                href="{{ route('view-jobseeker-profile') }}">@include('jobseeker.partials.icon',['icon'=>'user']) View Profile </a></li>
    @endcan

    @can('manage-profile')
        <li><a class="{{ Route::is('jobseeker.dashboard') || Route::is('job-seeker-profile') ? 'active' : null }}"
                href="{{ route('job-seeker-profile') }}">@include('jobseeker.partials.icon',['icon'=>'edit']) Manage Profile </a></li>
    @endcan


    @can('job-applied')
        <li><a href="{{ route('applied-jobs') }}"
                class="{{ Route::is('applied-jobs', 'jobseeker.application.timeline') ? 'active' : null }}">@include('jobseeker.partials.icon',['icon'=>'file']) Applied Jobs
            </a></li>
    @endcan
    
    @can('job-applied')
        <li>
            <a href="{{ route('unique_codes') }}" class="{{ Route::is('unique_codes') ? 'active' : null }}">@include('jobseeker.partials.icon',['icon'=>'code']) Unique Code </a>
        </li>
    @endcan

    @if (auth()->user()?->user_type === \App\Enum\UserType::JOB_SEEKER)
        <li>
            <a class="{{ Route::is('jobseeker.private-documents.*') ? 'active' : null }}"
               href="{{ route('jobseeker.private-documents.index') }}">
                @include('jobseeker.partials.icon',['icon'=>'lock']) Private passport &amp; documents
            </a>
        </li>
    @endif

    @if (auth()->user()?->user_type === \App\Enum\UserType::JOB_SEEKER)
        <li><a class="{{ Route::is('interviews.applicant.*') ? 'active' : null }}"
            href="{{ route('interviews.applicant.index') }}">@include('jobseeker.partials.icon',['icon'=>'calendar']) My online interviews</a></li>
    @endif

    @if(config('application_chat.enabled') && auth()->user()?->user_type === \App\Enum\UserType::JOB_SEEKER)
        <li><a class="{{ Route::is('application-chat.*') ? 'active' : '' }}"
            href="{{ route('application-chat.index') }}">Private Messages</a></li>
    @endif

    @if(config('support_center.enabled') && auth()->user()?->user_type === \App\Enum\UserType::JOB_SEEKER)
        <li><a href="{{ route('support.index') }}" class="{{ Route::is('support.*') ? 'active' : '' }}">@include('jobseeker.partials.icon',['icon'=>'support']) Support Inbox &amp; Refunds</a></li>
    @endif

    {{-- TODO Something to put --}}

    <li>
        <a class="{{ Route::is('jobseeker-change-password') ? 'active' : null }}"
            href="{{ route('jobseeker-change-password') }}">@include('jobseeker.partials.icon',['icon'=>'lock']) Change Password</a>
    </li>

    {{-- <li>
        <a class="bg-danger text-white px-3 py-2" style="border-radius: 3px" href="{{ route('delete.account') }}"
            onclick="return confirm('Are you sure to delete account?');">Delete account
        </a>
    </li> --}}

    <li>
        <form action="{{ route('logout') }}" method="post">
            @csrf
            <button type="submit" class="btn-sm btn-danger">Logout </button>
        </form>
    </li>
</ul>


@push('js')<script>document.querySelectorAll('.autoRecvEmail').forEach(el=>el.addEventListener('change',()=>{const value=el.checked?1:0;el.value=value;fetch('/auto-email-receive/'+value,{credentials:'same-origin'}).catch(()=>{el.checked=!el.checked;});}));</script>@endpush
