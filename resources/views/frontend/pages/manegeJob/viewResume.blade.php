<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=yes">
    <title>Your Resume</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/bootstrap.min.css') }}">
    <style>
    body { margin: 0px; }
    @page {
        margin: 0 !important;
        padding: 0 !important;
    }
        
    .info-container img {
        margin-right: 10px;
        margin-top: 8px;
    }

    .text-left {
        text-align: left;
    }
    </style>
</head>
<body>

    <div class="container mt-4" style="padding-left: 0px">
        <table class="table">
            <tr>
                <td width="100%">
                    <table class="table">
                        <tr>
                            <td class="text-left">
                                <h3>{{ $user->name }}</h3>
                                <div class="info-container">
                                    <img src="{{ asset('assets/images/placeholder/limg.png') }}" alt="">
                                    {{ $user->personal_info?->present_address_line_1 }}
                                </div>
                            
                                <div class="info-container">
                                    <img src="{{ asset('assets/images/placeholder/spimg.png') }}" alt="">
                                    {{ $user->personal_info?->phone }}, {{ $user->personal_info?->alternate_phone }}
                                </div>
                            
                                <div class="info-container">
                                    <img src="{{ asset('assets/images/placeholder/eimg.png') }}" alt="">
                                    {{ $user->personal_info?->email }},
                                    {{ $user->personal_info?->alternate_email }}
                                </div>
                            </td>

                            
                            <td>
                                <img id="userImage" style="border: 1px solid #dddddd;" src="{{ asset('assets/images/profilephoto/'.$user->photo) }}" width="124"height="135" alt="job seeker photo">
                            </td>
                        </tr>
                    </table>

                    <p class="bg-info">{{ __('messages.Motto') }}</p>
                    <p>{{ $user->personal_info?->motto }}</p>

                    <p class="bg-info">{{ __('messages.WorkExperience') }}</p>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('messages.Occupationorpositionheld') }}</th>
                                <th>{{ __('messages.Employer') }}</th>
                                <th>{{ __('messages.City') }}</th>
                                <th>{{ __('messages.Country') }}</th>
                                <th>{{ __('messages.FromDate') }}</th>
                                <th>{{ __('messages.ToDate') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($user->work_experiences as $index => $work)
                            <tr>
                                <td>{{ $work->position_name ? $work->position_name : null }}</td>
                                <td>{{ $work->employeer ? $work->employeer : null }}</td>
                                <td>{{ $work->city ? $work->city : null }}</td>
                                <td>{{ $work->occupation_country }}</td>
                                <td>{{ $work->from_date ? $work->from_date : null }}</td>
                                <td>{{ $work->to_date && $work->continuing == 1 ? 'Continuing' : $work->to_date}}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6">{{ __('messages.Nodatafound') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <p class="bg-info">{{ __('messages.Educaiton') }}</p>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('messages.Levelofeducation') }}</th>
                                <th>{{ __('messages.InstituteName') }}</th>
                                <th>{{ __('messages.Result') }}</th>
                                <th>{{ __('messages.PassingYear') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($user->educations as $index => $education)
                            <tr>
                                <td>{{ $education?->degreeName?->name }}</td>
                                <td>{{ $education?->institute_name ?? null }}</td>
                                <td>{{ $education->result ?? null }}</td>
                                <td>{{ $education->pass_year ?? null }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4">{{ __('messages.Nodatafound') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="container" style="padding-left: 0px">
        <table class="table">
            <tr>
                <td width="100%">
                    <p class="bg-info">{{ __('messages.Language') }}</p>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('messages.Language') }}</th>
                                <th>{{ __('messages.Listening') }}</th>
                                <th>{{ __('messages.Reading') }}</th>
                                <th>{{ __('messages.Writting') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($user->languages as $index => $lang)
                            <tr>
                                <td>{{ $lang ? $lang->language_name : null }}</td>
                                <td>{{ $lang->listening_proficiency }}</td>
                                <td>{{ $lang->reading_proficiency }}</td>
                                <td>{{ $lang->writting_proficiency }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4">{{ __('messages.Nodatafound') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <p class="bg-info">{{ __('messages.Training') }}</p>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('messages.Country') }}</th>
                                <th>{{ __('messages.TrainingTitle') }}</th>
                                <th>{{ __('messages.TopicsCovered') }}</th>
                                <th>{{ __('messages.TrainingYear') }}</th>
                                <th>{{ __('messages.Institute') }}</th>
                                <th>{{ __('messages.Duration') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($user->trainings as $index => $training)
                        <tr>
                            <td>{{ $training->country }}</td>
                            <td>{{ $training->title }}</td>
                            <td>{{ $training->topic }}</td>
                            <td>{{ $training->training_year }}</td>
                            <td>{{ $training->training_institute }}</td>
                            <td>{{ $training->training_duration }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6">{{ __('messages.Nodatafound') }}</td>
                        </tr>
                        @endforelse
                        </tbody>
                    </table>

                    <p class="bg-info"> {{ __('messages.Skills') }}</p>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('messages.SkillName') }}</th>
                                <th>{{ __('messages.Proficiency') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($user->skills as $index => $skill)
                            <tr>
                                <td>{{ $skill ? $skill->skill_name : null }}</td>
                                <td>{{ $skill ? $skill->skill_proficiency : null }}</td>
                            </tr>
                            @empty 
                            <tr>
                                <td colspan="2">{{ __('messages.Nodatafound') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="{{ asset('frontend/assets/js/bootstrap.min.js') }}"></script>
</body>
</html>
