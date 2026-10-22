<nav class="navbar navbar-expand-lg navbar-light" aria-label="Admin toolbar">
    <div class="d-flex align-items-center gap-3">
        <button class="btn sidebartoggler nav-icon-hover" id="headerCollapse" type="button"
            aria-label="Toggle main navigation" aria-controls="sidebarnav" aria-expanded="true">
            <i class="ti ti-menu-2" aria-hidden="true"></i>
        </button>
        <div>
            <strong class="d-block" style="color:#183e5c;line-height:1.25">Join Gallagher</strong>
            <span class="small text-muted">Administration Workspace</span>
        </div>
    </div>
    <div class="ms-auto d-flex align-items-center gap-3 flex-wrap">
        <a href="{{ route('home') }}" class="btn btn-outline-primary btn-sm">
            <i class="ti ti-world" aria-hidden="true"></i> View Website
        </a>
        @if(config('support_center.enabled') &&
            (auth()->user()->can('manage-support-tickets') ||
             auth()->user()->can('review-refund-requests') ||
             auth()->user()->can('confirm-refund-settlement')))
            <a href="{{ route('support.index') }}" class="btn btn-outline-primary btn-sm">Support</a>
        @endif
        <div class="dropdown">
            <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2"
                type="button" id="jw-account-menu" data-bs-toggle="dropdown" aria-expanded="false"
                aria-label="Account menu">
                <i class="ti ti-user-circle" aria-hidden="true"></i>
                <span class="d-none d-sm-inline">{{ auth()->user()?->name ?? 'Administrator' }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="jw-account-menu">
                <li><a class="dropdown-item" href="{{ route('admin-profile-setting') }}">My Profile</a></li>
                <li><a class="dropdown-item" href="{{ route('admin-change-password') }}">Change Password</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="dropdown-item" type="submit">Log Out</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>
