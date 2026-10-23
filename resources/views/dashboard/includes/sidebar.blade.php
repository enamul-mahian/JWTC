<div>
    <div class="brand-logo d-flex align-items-center justify-content-between">
        <a href="{{ route('admin.dashboard') }}" class="text-nowrap logo-img">
            @if (isset($setting->logo))
                <img src="{{ asset('assets/images/setting/' . $setting?->logo) }}" alt="" srcset="">
            @else
                <img src="{{ asset('frontend/assets/img/logo.png') }}">
            @endif
        </a>
        <div class="close-btn d-lg-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
            <i class="ti ti-x fs-8 text-muted"></i>
        </div>
    </div>
    <!-- Sidebar navigation-->
    <nav class="sidebar-nav scroll-sidebar" data-simplebar>
        <ul id="sidebarnav">
            @if(in_array(strtolower((string)auth()->user()?->user_type), ['admin','super_admin'], true) && auth()->user()->can('active-job'))
            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('admin.jobs.index') }}"><span><i class="ti ti-briefcase"></i></span><span class="hide-menu">Manage All Jobs</span></a></li>
            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('admin.application-progress.index') }}"><span><i class="ti ti-list-check"></i></span><span class="hide-menu">Applications &amp; Progress</span></a></li>
            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('admin.application-statuses.index') }}"><span><i class="ti ti-adjustments"></i></span><span class="hide-menu">Workflow Settings</span></a></li>
            @endif

            @if(in_array(strtolower((string)auth()->user()?->user_type), ['admin','super_admin'], true) && auth()->user()->can('employer-list'))
            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('admin.company-approvals.index') }}"><span><i class="ti ti-user-check"></i></span><span class="hide-menu">Company Approvals</span></a></li>
            @endif
            @if(in_array(strtolower((string)auth()->user()?->user_type), ['admin','super_admin'], true) && auth()->user()->can('application-setting'))
            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('admin.news-ticker') }}"><span><i class="ti ti-news"></i></span><span class="hide-menu">News Ticker</span></a></li>
            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('admin.hero-slides') }}"><span><i class="ti ti-photo"></i></span><span class="hide-menu">Homepage Slides</span></a></li>
            @endif
            @if(in_array(strtolower((string)auth()->user()?->user_type), ['admin','super_admin'], true) && (auth()->user()->can('manage-application-chat') || auth()->user()->hasAnyRole(['Admin','Super Admin'])))
            <li class="sidebar-item"><a class="sidebar-link" href="{{ route('admin.site-chat') }}"><span><i class="ti ti-message-circle"></i></span><span class="hide-menu">Chat Inbox &amp; Schedule</span></a></li>
            @endif
            <!-- ============================= -->
            <!-- Home -->
            <!-- ============================= -->
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('admin.dashboard') }}" aria-expanded="false">
                    <span>
                        <i class="ti ti-home"></i>
                    </span>
                    <span class="hide-menu">Dashboard </span>
                </a>
            </li>
            <!-- =================== -->

            @if (in_array(auth()->user()?->user_type, [\App\Enum\UserType::ADMIN, \App\Enum\UserType::SUPER_ADMIN], true) && auth()->user()->can('review-private-documents'))
                <li class="sidebar-item">
                    <a href="{{ route('admin.private-documents.index') }}" class="sidebar-link">
                        <span><i class="ti ti-file-lock"></i></span>
                        <span class="hide-menu">Private Document Reviews</span>
                    </a>
                </li>
            @endif

            @if(in_array(auth()->user()?->user_type, [\App\Enum\UserType::ADMIN, \App\Enum\UserType::SUPER_ADMIN], true) && auth()->user()->can('manage-interviews'))
                <li class="sidebar-item">
                    <a href="{{ route('interviews.manage.index') }}" class="sidebar-link">
                        <span><i class="ti ti-calendar-event"></i></span>
                        <span class="hide-menu">Online Interviews</span>
                    </a>
                </li>
            @endif

            @if(in_array(auth()->user()?->user_type, [\App\Enum\UserType::ADMIN, \App\Enum\UserType::SUPER_ADMIN], true) && auth()->user()->can('manage-email-studio'))
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('email-studio.index') }}">
                        <span><i class="ti ti-mail"></i></span>
                        <span class="hide-menu">SMTP Email Studio</span>
                    </a>
                </li>
            @endif

            @if(config('application_chat.enabled') && in_array(auth()->user()?->user_type, [\App\Enum\UserType::ADMIN, \App\Enum\UserType::SUPER_ADMIN], true) && auth()->user()->can('manage-application-chat'))
                <li class="sidebar-item">
                    <a href="{{ route('application-chat.index') }}" class="sidebar-link">
                        <span><i class="ti ti-messages"></i></span>
                        <span class="hide-menu">Applicant Messaging</span>
                    </a>
                </li>
            @endif

            @if(config('support_center.enabled') && in_array(auth()->user()?->user_type, [\App\Enum\UserType::ADMIN, \App\Enum\UserType::SUPER_ADMIN], true) &&
                (auth()->user()->can('manage-support-tickets') || auth()->user()->can('review-refund-requests') || auth()->user()->can('confirm-refund-settlement')))
                <li class="sidebar-item">
                    <a href="{{ route('support.index') }}" class="sidebar-link">
                        <span><i class="ti ti-headset"></i></span>
                        <span class="hide-menu">Support &amp; Refunds</span>
                    </a>
                </li>
            @endif

            {{-- TODO Something to put --}}

            @if (auth()->user()->can('job-categories.index') || auth()->user()->can('review-private-documents') || auth()->user()->can('inactive-job'))
                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow" href="#" aria-expanded="false">
                        <span class="d-flex">
                            <i class="ti ti-basket"></i>
                        </span>
                        <span class="hide-menu">Job Management</span>
                    </a>
                    <ul aria-expanded="false" class="collapse first-level">
                        {{-- TODO Something to put --}}

                        @can('review-private-documents')
                            <li class="sidebar-item">
                                <a href="{{ route('active.job') }}" class="sidebar-link">
                                    <div class="round-16 d-flex align-items-center justify-content-center">
                                        <i class="ti ti-circle"></i>
                                    </div>
                                    <span class="hide-menu">Active Job </span>
                                </a>
                            </li>
                        @endcan

                        @can('inactive-job')
                            <li class="sidebar-item">
                                <a href="{{ route('inactive.job') }}" class="sidebar-link">
                                    <div class="round-16 d-flex align-items-center justify-content-center">
                                        <i class="ti ti-circle"></i>
                                    </div>
                                    <span class="hide-menu">Inactive Job </span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endif

            <li class="sidebar-item">
                <a class="sidebar-link has-arrow" href="#" aria-expanded="false">
                    <span class="d-flex">
                        <i class="ti ti-code"></i>
                    </span>
                    <span class="hide-menu">Unique Code</span>
                </a>
                <ul aria-expanded="false" class="collapse first-level">

                    <li class="sidebar-item">
                        <a href="{{ route('code-request') }}" class="sidebar-link">
                            <div class="round-16 d-flex align-items-center justify-content-center">
                                <i class="ti ti-circle"></i>
                            </div>
                            <span class="hide-menu">Code Request </span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('uniquecode.index') }}" class="sidebar-link">
                            <div class="round-16 d-flex align-items-center justify-content-center">
                                <i class="ti ti-circle"></i>
                            </div>
                            <span class="hide-menu">All Unique Code</span>
                        </a>
                    </li>
                </ul>
            </li>

            @if (auth()->user()->can('employer-list') ||
                    auth()->user()->can('company-size.index') ||
                    auth()->user()->can('employer-job-history'))
                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow" href="#" aria-expanded="false">
                        <span class="d-flex">
                            <i class="ti ti-star"></i>
                        </span>
                        <span class="hide-menu">Employer Management</span>
                    </a>
                    <ul aria-expanded="false" class="collapse first-level">
                        @can('employer-list')
                            <li class="sidebar-item">
                                <a href="{{ route('employer.index') }}" class="sidebar-link">
                                    <div class="round-16 d-flex align-items-center justify-content-center">
                                        <i class="ti ti-circle"></i>
                                    </div>
                                    <span class="hide-menu">Employer List</span>
                                </a>
                            </li>
                        @endcan

                        {{-- TODO Something to put --}}

                        @can('employer-job-history')
                            <li class="sidebar-item">
                                <a href="{{ route('employer-history') }}" class="sidebar-link">
                                    <div class="round-16 d-flex align-items-center justify-content-center">
                                        <i class="ti ti-circle"></i>
                                    </div>
                                    <span class="hide-menu">Employer Job History </span>
                                </a>
                            </li>
                        @endcan

                        {{-- TODO Something to put --}}
                    </ul>
                </li>
            @endif

            @if (auth()->user()->can('active-jobSeeker') ||
                    auth()->user()->can('inactive-jobSeeker') ||
                    auth()->user()->can('jobSeeker-history'))
                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow" href="#" aria-expanded="false">
                        <span class="d-flex">
                            <i class="ti ti-search"></i>
                        </span>
                        <span class="hide-menu">Jobseeker</span>
                    </a>
                    <ul aria-expanded="false" class="collapse first-level">
                        @can('active-jobSeeker')
                            <li class="sidebar-item">
                                <a href="{{ route('active.jobseeker') }}" class="sidebar-link">
                                    <div class="round-16 d-flex align-items-center justify-content-center">
                                        <i class="ti ti-circle"></i>
                                    </div>
                                    <span class="hide-menu">Active Jobseeker</span>
                                </a>
                            </li>
                        @endcan

                        @can('inactive-jobSeeker')
                            <li class="sidebar-item">
                                <a href="{{ route('inactive.jobseeker') }}" class="sidebar-link">
                                    <div class="round-16 d-flex align-items-center justify-content-center">
                                        <i class="ti ti-circle"></i>
                                    </div>
                                    <span class="hide-menu">Inactive Jobseeker</span>
                                </a>
                            </li>
                        @endcan

                        @can('jobSeeker-history')
                            <li class="sidebar-item">
                                <a href="{{ route('jobseeker-history') }}" class="sidebar-link">
                                    <div class="round-16 d-flex align-items-center justify-content-center">
                                        <i class="ti ti-circle"></i>
                                    </div>
                                    <span class="hide-menu">Jobseeker History </span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endif

            @if (auth()->user()->can('user.index') || auth()->user()->can('role.index'))
                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow" href="#" aria-expanded="false">
                        <span class="d-flex">
                            <i class="ti ti-chart-donut-3"></i>
                        </span>
                        <span class="hide-menu">User Management</span>
                    </a>
                    <ul aria-expanded="false" class="collapse first-level">
                        @can('user.index')
                            <li class="sidebar-item">
                                <a href="{{ route('user-management.index') }}" class="sidebar-link">
                                    <div class="round-16 d-flex align-items-center justify-content-center">
                                        <i class="ti ti-circle"></i>
                                    </div>
                                    <span class="hide-menu">Users</span>
                                </a>
                            </li>
                        @endcan

                        @can('role.index')
                            <li class="sidebar-item">
                                <a href="{{ route('user-permission.index') }}" class="sidebar-link">
                                    <div class="round-16 d-flex align-items-center justify-content-center">
                                        <i class="ti ti-circle"></i>
                                    </div>
                                    <span class="hide-menu">Role</span>
                                </a>
                            </li>
                        @endcan

                    </ul>
                </li>
            @endif

            @if (auth()->user()->can('application-setting') ||
                    auth()->user()->can('email-setting') ||
                    auth()->user()->can('cache-clear'))
                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow" href="#" aria-expanded="false">
                        <span class="d-flex">
                            <i class="ti ti-settings"></i>
                        </span>
                        <span class="hide-menu">Settings</span>
                    </a>
                    <ul aria-expanded="false" class="collapse first-level">
                        @can('application-setting')
                            <li class="sidebar-item">
                                <a href="{{ route('setting') }}" class="sidebar-link">
                                    <div class="round-16 d-flex align-items-center justify-content-center">
                                        <i class="ti ti-circle"></i>
                                    </div>
                                    <span class="hide-menu">Application Setting </span>
                                </a>
                            </li>
                        @endcan

                        {{-- TODO Something to put --}}

                        @can('cache-clear')
                            <li class="sidebar-item">
                                <a href="{{ route('application-cache-clear') }}" class="sidebar-link">
                                    <div class="round-16 d-flex align-items-center justify-content-center">
                                        <i class="ti ti-circle"></i>
                                    </div>
                                    <span class="hide-menu">Cache clear </span>
                                </a>
                            </li>
                        @endcan

                        {{-- TODO Something to put --}}
                    </ul>
                </li>
            @endif

            

            {{-- TODO Something to put --}}
        </ul>
    </nav>
    <div class="fixed-profile p-3 bg-light-secondary rounded sidebar-ad mt-3">
        <div class="hstack gap-3">
            <div class="john-img">
                <img src="../../dist/images/profile/user-1.jpg" class="rounded-circle" width="40" height="40"
                    alt="">
            </div>
            <div class="john-title">
                <h6 class="mb-0 fs-4 fw-semibold">Mathew</h6>
                <span class="fs-2 text-dark">Designer</span>
            </div>
            <button class="border-0 bg-transparent text-primary ms-auto" tabindex="0" type="button"
                aria-label="logout" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="logout">
                <i class="ti ti-power fs-6"></i>
            </button>
        </div>
    </div>
    <!-- End Sidebar navigation -->
</div>
