@extends('frontend.layout.frontMaster')
@Section('title', 'Jobseeker Dashbaord')
@Section('content')

<div class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="inner-header">
                    <h3>{{ __('messages.ManageProfile') }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="content">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-4 col-xs-12">
                <div class="right-sideabr">
                    <h4>{{ __('messages.Profile') }}</h4>
                    @include('jobseeker.partials.sidenav')
                </div>
            </div>
            <div class="col-lg-8 col-md-8 col-xs-12">
                @if($user->personal_info)
                <table class="table">
                    <tr>
                        <td align="left">
                            <h5 class="alerts-title">{{ $user->name }}</h5>
                    
                            <div class="">
                                <img src="{{ asset('assets/images/placeholder/limg.png') }}" alt="">
                                <span>{{ $user->personal_info ? $user->personal_info->present_address : '' }},</span>
                                <span>{{ $user->personal_info ? $user->personal_info->present_city : '' }},</span>
                                <span>{{ $user->personal_info ? $user->personal_info->present_postal_code : '' }},</span>
                                <span>{{ $user->personal_info ? $user->personal_info->present_country : '' }}</span>
                            </div>
                    
                            <div class="">
                                <img src="{{ asset('assets/images/placeholder/spimg.png') }}" alt="contact phone icon">
                                <span>{{ $user->personal_info ? $user->personal_info->phone : '' }},</span>
                                <span>{{ $user->personal_info ? $user->personal_info->alternate_phone : '' }}</span>
                            </div>
                    
                            <div class="">
                                <img src="{{ asset('assets/images/placeholder/eimg.png') }}" alt="email icon">
                                <span>{{ $user->personal_info ? $user->personal_info->email : '' }},</span>
                                <span>{{ $user->personal_info ? $user->personal_info->alternate_email : '' }},</span>
                            </div>
                        </td>
                    
                        <td align="right">
                            <span>
                                @if($user->photo)
                                <img style="border: 1px solid #dddddd;" src="{{ asset('assets/images/profilephoto/'.$user->photo) }}"
                                    width="124" height="135" alt="{{ $user->name }}">
                                @else 

                                @endif
                            </span>
                        </td>
                    </tr>
                </table>
                {{-- motto  --}}
                <div class="bg-info" role="heading" aria-level="2" align="left">
                    <em class="text-white">{{ __('messages.Motto') }}:</em>
                </div>
                <div  align="left">
                    {{ $user->personal_info ? $user->personal_info->motto : '' }}
                </div>
                @endif

                {{-- work experience  --}}
                @if(count($user->educations) > 0)
                <div class="bg-info mt-3" role="heading" aria-level="2" align="left">
                    <em class="text-white">{{ __('messages.Educaiton') }}:</em>
                </div>    
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ __('messages.Levelofeducation') }}</th>
                            <th>{{ __('messages.InstituteName') }}</th>
                            <th> {{ __('messages.Result') }}</th>
                            <th>{{ __('messages.PassingYear') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($user?->educations as $education)
                        <tr>
                            <td>{{ $education?->degreeName?->name }}</td>
                            <td>{{ $education->institute_name }}</td>
                            <td>{{ $education->result }}</td>
                            <td>{{ $education->pass_year }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
                
                @if(count($user->work_experiences) > 0)
                <div class="bg-info mt-3" role="heading" aria-level="2" align="left">
                    <em class="text-white">{{ __('messages.WorkExperience') }}:</em>
                </div>    
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ __('messages.Possition') }}</th>
                            <th>{{ __('messages.Employer') }}</th>
                            <th>{{ __('messages.Location') }} </th>
                            <th>{{ __('messages.Responsibility') }}</th>
                            <th>{{ __('messages.Status') }} </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($user->work_experiences as $workExp)
                        <tr>
                            <td>{{ $workExp->position_name }}</td>
                            <td>{{ $workExp->employeer }}</td>
                            <td>{{ $workExp->city }} {{ $workExp->occupation_country }}</td>
                            <td>{{ $workExp->responsibilities }}</td>
                            <td>
                                {{ $workExp->from_date }} 
                                @if($workExp->continuing == 1) 
                                <span class="badge-info">{{ __('messages.Continuinghere') }} </span>
                                @else 
                                {{ $workExp->to_date }} 
                                @endif</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
                
                {{-- skills --}}
                @if(count($user->skills) > 0)
                <div class="bg-info mt-3" role="heading" aria-level="2" align="left">
                    <em class="text-white">{{ __('messages.Skills') }}:</em>
                </div>    
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ __('messages.SkillName') }} </th>
                            <th> {{ __('messages.SkillProficiency') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($user->skills as $skill)
                        <tr>
                            <td>{{ $skill->skill_name }}</td>
                            <td>{{ $skill->skill_proficiency }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
                
                {{-- Languages --}}
                @if(count($user->languages) > 0)
                <div class="bg-info mt-3" role="heading" aria-level="2" align="left">
                    <em class="text-white">{{ __('messages.Languages') }} :</em>
                </div>    
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th> {{ __('messages.LanguageName') }}</th>
                            <th> {{ __('messages.ListeningProficiency') }}</th>
                            <th> {{ __('messages.ReadingProficiency') }}</th>
                            <th>{{ __('messages.WrittingProficiency') }} </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($user->languages as $lang)
                        <tr>
                            <td>{{ $lang->language_name }}</td>
                            <td>{{ $lang->listening_proficiency }}</td>
                            <td>{{ $lang->reading_proficiency }}</td>
                            <td>{{ $lang->writting_proficiency }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
                
                {{-- Languages --}}
                @if(count($user->trainings) > 0)
                <div class="bg-info mt-3" role="heading" aria-level="2" align="left">
                    <em class="text-white"> {{ __('messages.TrainningandCertificate') }}:</em>
                </div>    
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ __('messages.TrainingTitle') }}</th>
                            <th>{{ __('messages.Topic') }}</th>
                            <th> {{ __('messages.Year') }}</th>
                            <th> {{ __('messages.TrainingInstitute') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($user->trainings as $training)
                        <tr>
                            <td>{{ $training->title }}</td>
                            <td>{{ $training->topic }}</td>
                            <td>{{ $training->training_year }}</td>
                            <td>{{ $training->training_institute }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
    <script src="{{ asset('frontend/assets/script/job_seeker.js') }}"></script>
@endpush
