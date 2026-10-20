<div class="col-md-12">
    @forelse ($jobs as $item)

        <a class="job-listings-featured" href="{{ route('job-details', $item->id) }}">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-xs-12">
                    <div class="job-company-logo">
                        @if (isset($item->company->photo))
                            <img src="{{ asset('assets/images/profilephoto/' . $item->company->photo) }}" alt="">
                        @endif
                    </div>
                    <div class="job-details">
                        <h3>{{ $item->title }}</h3>
                        <span class="company-neme">{{ $item->company->company_name }}</span>
                        <div class="tags">
                            <span><i class="lni-map-marker"></i> {{ $item->location ? $item->location->name : 'N/A' }}
                            </span>
                            <span><i class="lni-user"></i>{{ $item->vacancies }}</span>
                            <span><i class="lni-wallet"></i>{{ $item->maximum_salary }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-xs-12 text-right">
                    <div class="tag-type">

                        @if (($status = getJobStatus($item->id)) === 0)
                            <span class="heart-icon">
                                <img src="{{ asset('assets/images/placeholder/checkmark.svg') }}" height="10"
                                    width="10">
                            </span>
                        @endif

                        <span class="part-time">
                            <ul class="text-left">
                                @forelse (employee_status($item->employment_status) as  $value)
                                    <li>{{ ucwords(str_replace('_', ' ', $value->name)) }}
                                        @if (!$loop->last)
                                            
                                        @endif
                                    </li>
                                @empty
                                    <li>{{ __('messages.NA') }}</li>
                                @endforelse
                            </ul>
                        </span>
                    </div>
                    
                    <!-- Progress Bar Section -->
                    <div class="mt-3">
                        @php
    $application = getApplication($item->id); 

    $statuses = [
        ['id' => 1, 'name' => 'File Under Process of Decision Making', 'progress' => 0],
        ['id' => 2, 'name' => 'File Under Process of Assessment', 'progress' => 20],
        ['id' => 3, 'name' => 'File Under Process of Acceptance', 'progress' => 30],
        ['id' => 4, 'name' => 'File Under Process of Approval', 'progress' => 40],
        ['id' => 5, 'name' => 'Agreement on Board', 'progress' => 50],
        ['id' => 6, 'name' => 'Police/Criminal Clearance Certificate Received', 'progress' => 55],
        ['id' => 7, 'name' => 'Medical Test Report Received', 'progress' => 60],
        ['id' => 8, 'name' => 'COS Letter Processed', 'progress' => 70],
        ['id' => 9, 'name' => 'VFS Appointment Date Approved', 'progress' => 75],
        ['id' => 10, 'name' => 'Visa Processing Complete', 'progress' => 100],
        ['id' => 11, 'name' => 'Rejected / Terminated', 'progress' => 0],
    ];

    $applicationStatus = collect($statuses)->firstWhere('id', $application->status);
    
  //  print_r($application);
    
@endphp
                        
                          

                        @if ($applicationStatus)
                         
                            <!-- Status Name -->
                            <p class="mb-1"><strong>Status:</strong> {{ $applicationStatus['name'] }}</p>

                            <!-- Progress Bar -->
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar" role="progressbar" 
                                    style="width: {{ $applicationStatus['progress'] }}%;" 
                                    aria-valuenow="{{ $applicationStatus['progress'] }}" 
                                    aria-valuemin="0" 
                                    aria-valuemax="100">
                                    {{ $applicationStatus['progress'] }}%
                                </div>
                            </div>
                        @else
                            <p class="text-danger">{{ __('Status not available') }}</p>
                        @endif
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
