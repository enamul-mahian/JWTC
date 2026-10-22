<div class="container">
    <div class="theme-header clearfix">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main-navbar"
                aria-controls="main-navbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                <span class="lni-menu"></span>
                <span class="lni-menu"></span>
                <span class="lni-menu"></span>
            </button>
            <a href="{{ route('home') }}" class="navbar-brand">
                @if (isset($setting->logo))
                    <img src="{{ asset('assets/images/setting/' . $setting?->logo) }}" alt="Join Gallagher" srcset="">
                @else
                    <img src="{{ asset('frontend/assets/img/logo.png') }}" alt="Join Gallagher">
                @endif
            </a>
        </div>
        
        
        <div class="collapse navbar-collapse" id="main-navbar">
            <ul class="navbar-nav mr-auto w-100 justify-content-end">
                <li class="nav-item {{ Route::currentRouteName() == 'home' ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('home') }}">
                        Home
                    </a>
                </li>
                @if(config('jwtc_design.enabled'))
                    <li class="nav-item {{ Route::is('search-jobs') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('search-jobs') }}"
                           @if(Route::is('search-jobs')) aria-current="page" @endif>Find Jobs</a>
                    </li>
                    <li class="nav-item {{ Route::is('contact') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('contact') }}"
                           @if(Route::is('contact')) aria-current="page" @endif>Contact</a>
                    </li>
                @endif

                @if (isset($pages))
                    @foreach ($pages as $page)
                        <li class="nav-item  {{ Route::currentRouteName() == 'get-page-details' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('get-page-details', $page->url) }}">
                                {{ $page->name }}
                            </a>
                        </li>
                    @endforeach
                @endif

                @if (!Auth::user())
                    <li class="nav-item {{ Route::currentRouteName() == 'login' ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('login') }}">
                            Sign In
                        </a>
                  </li>
                <li class="nav-item {{ Route::currentRouteName() == 'registration.form' ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('registration.form') }}">
        Create Account
    </a>
</li>
                @else
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false">
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu">

                            @if (auth()->user()->user_type === App\Enum\RoleType::JOB_SEEKER ||
                                    auth()->user()->user_type === App\Enum\UserType::JOB_SEEKER)
                                <li><a class="dropdown-item"
                                        href="{{ route('jobseeker.dashboard') }}">{{ __('Dashboard') }} </a></li>
                                <li><a class="dropdown-item" href="{{ route('view-jobseeker-profile') }}">My Profile
                                    </a></li>
                                <li><a class="dropdown-item" href="{{ route('library') }}">My Library </a></li>
                                <li><a class="dropdown-item" href="{{ route('job-seeker-profile') }}">Manage
                                        Resumes</a></li>
                            @elseif (auth()->user()->user_type === App\Enum\RoleType::EMPLOYER ||
                                    auth()->user()->user_type === App\Enum\UserType::EMPLOYER)
                                <li><a class="dropdown-item"
                                        href="{{ route('employer.dashboard') }}">{{ __('Dashboard') }} </a></li>
                                <li><a class="dropdown-item" href="{{ route('jobs.create') }}">Add Job</a></li>
                                <li><a class="dropdown-item" href="{{ route('manage.job') }}">Manage Jobs</a></li>
                            @elseif (auth()->user()->user_type === App\Enum\RoleType::ADMIN || auth()->user()->user_type === App\Enum\UserType::ADMIN)
                                <li><a class="dropdown-item"
                                        href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }} </a></li>
                            @endif
                            <form action="{{ route('logout') }}" method="post">
                                @csrf
                                <button type="submit" class="dropdown-item">{{ __('Logout') }} </button>
                            </form>
                        </ul>
                    </li>
                @endif

                @if (\Auth::check())
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false">
                            Notifications <span class="badge badge-danger">{{ count(userNotifications()) }}</span>
                        </a>
                        <ul class="dropdown-menu">
                            @foreach (userNotifications() as $unread)
                                <li><a class="dropdown-item"
                                        href="{{ route('notification-detail', $unread->id) }}">{{ $unread->data['intro'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endif
                @can('post-job')
                    <li class="button-group">
                        <a href="{{ route('jobs.create') }}" class="button btn btn-common">{{ __('Post a Job') }}</a>
                    </li>
                @endcan
            </ul>
        </div>
    </div>
</div>

<div class="mobile-menu" data-logo="{{ asset('assets/images/setting/' . $setting?->logo) }}"></div>
