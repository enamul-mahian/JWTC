<div class="col-md-12">
    @forelse ($jobs as $item)

    <a class="job-listings-featured" href="{{ route('job-details', $item->id) }}">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-xs-12">
                <div class="job-company-logo">
                    @if(isset($item->company->photo))
                    <img src="{{ asset('assets/images/profilephoto/'.$item->company->photo) }}" alt="">
                    @endif
                </div>
                <div class="job-details">
                    <h3>{{ $item->title }}</h3>
                    <span class="company-neme">{{ $item->company->company_name }}</span>
                    <div class="tags">
                        <span><i class="lni-map-marker"></i>{{ $item->location ? $item->location->name : 'N/A' }}</span>
                        <span><i class="lni-user"></i>{{ $item->vacancies }}</span>
                        <span><i class="lni-wallet"></i>{{ $item->maximum_salary }}</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-xs-12 text-right">
                <div class="tag-type">

                    @if(($status = getJobStatus($item->id)) === 0)
                        <span class="heart-icon">
                            <img src="{{ asset('assets/images/placeholder/checkmark.svg') }}" height="10" width="10">
                        </span>
                    @endif

                    @php 
                    $type = $item->employment_status;
                    if($type){
                        $emp_type = json_decode($item->employment_status);
                    }else {
                        $emp_type = [];
                    }
                    @endphp 
                    @forelse ($emp_type as $key => $value)
                    <span class="full-time">{{ $value }}</span>
                    @empty
                    <span class="full-time">N/A</span>
                    @endforelse
                </div>
            </div>
        </div>
    </a>
    @empty 
    <span class="job-listings-featured">{{ __('messages.Nodatafound') }}</span>
    @endforelse

    <div>
        {{ $jobs->links() }}
    </div>
    
</div>
