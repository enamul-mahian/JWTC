<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> {{ $user->name }} - Cv</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;1,400;1,500&display=swap"
        rel="stylesheet">

    <style>
        .roboto-light {
            font-family: "Roboto", sans-serif;
            font-weight: 300;
            font-style: normal;
        }

        .roboto-regular {
            font-family: "Roboto", sans-serif;
            font-weight: 400;
            font-style: normal;
        }

        .roboto-medium {
            font-family: "Roboto", sans-serif;
            font-weight: 500;
            font-style: normal;
        }

        .roboto-regular-italic {
            font-family: "Roboto", sans-serif;
            font-weight: 400;
            font-style: italic;
        }

        .roboto-medium-italic {
            font-family: "Roboto", sans-serif;
            font-weight: 500;
            font-style: italic;
        }
    </style>
</head>

<body style="background-color: #f8f7f7cb" class="roboto-regular">

    <div class="container  py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-10 col-xs-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-xs-12">
                                @if ($user->personal_info)
                                    <table class="table">
                                        <tr>
                                            <td align="left">
                                                <h5 class="alerts-title">{{ $user->name }}</h5>

                                                <div class="">
                                                    <img src="{{ asset('assets/images/placeholder/limg.png') }}"
                                                        alt="">
                                                    <span>{{ $user->personal_info ? $user->personal_info->present_address : '' }},</span>
                                                    <span>{{ $user->personal_info ? $user->personal_info->present_city : '' }},</span>
                                                    <span>{{ $user->personal_info ? $user->personal_info->present_postal_code : '' }},</span>
                                                    <span>{{ $user->personal_info ? $user->personal_info->present_country : '' }}</span>
                                                </div>

                                                <div class="">
                                                    <img src="{{ asset('assets/images/placeholder/spimg.png') }}"
                                                        alt="contact phone icon">
                                                    <span>{{ $user->personal_info ? $user->personal_info->phone : '' }},</span>
                                                    <span>{{ $user->personal_info ? $user->personal_info->alternate_phone : '' }}</span>
                                                </div>

                                                <div class="">
                                                    <img src="{{ asset('assets/images/placeholder/eimg.png') }}"
                                                        alt="email icon">
                                                    <span>{{ $user->personal_info ? $user->personal_info->email : '' }},</span>
                                                    <span>{{ $user->personal_info ? $user->personal_info->alternate_email : '' }}
                                                        @if (!last($user->personal_info))
                                                            ,
                                                        @endif
                                                    </span>
                                                </div>
                                            </td>

                                            <td align="right">
                                                <span>
                                                    @if ($user->photo)
                                                        <img style="border: 1px solid #dddddd;"
                                                            src="{{ asset('assets/images/profilephoto/' . $user->photo) }}"
                                                            width="124" height="135" alt="{{ $user->name }}">
                                                    @else
                                                    @endif
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                    {{-- motto  --}}
                                    <div style="background-color: #5690d9" role="heading" aria-level="2" align="left">
                                        <em class="text-white px-2">{{ __('messages.Motto') }}:</em>
                                    </div>
                                    <div align="left" class="bg-white py-2 px-2">
                                        {{ $user->personal_info ? $user->personal_info->motto : '' }}
                                    </div>
                                @endif

                                {{-- work experience  --}}
                                @if (count($user->educations) > 0)
                                    <div class="px-2 mt-3" style="background-color: #5690d9" role="heading"
                                        aria-level="2" align="left">
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

                                @if (count($user->work_experiences) > 0)
                                    <div class="px-2 mt-3" style="background-color: #5690d9" role="heading"
                                        aria-level="2" align="left">
                                        <em class="text-white">{{ __('messages.WorkExperience') }}:</em>
                                    </div>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>{{ __('messages.Possition') }}</th>
                                                <th>{{ __('messages.Employer') }}</th>
                                                <th>{{ __('messages.Location') }} </th>
                                                <th>{{ __('messages.Responsibility') }}</th>
                                                <th style="width:130px">{{ __('messages.Status') }} </th>
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
                                                        <p> {{ Carbon\Carbon::parse($workExp->from_date)->format('d M Y') }}
                                                        </p>
                                                        @if ($workExp->continuing == 1)
                                                            <span
                                                                class="badge-info">{{ __('messages.Continuinghere') }}
                                                            </span>
                                                        @else
                                                            <p> {{ Carbon\Carbon::parse($workExp->to_date)->format('d M Y') }}
                                                            </p>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif

                                {{-- skills --}}
                                @if (count($user->skills) > 0)
                                    <div class="px-2 mt-3" style="background-color: #5690d9" role="heading"
                                        aria-level="2" align="left">
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
                                @if (count($user->languages) > 0)
                                    <div class="px-2 mt-3" style="background-color: #5690d9" role="heading"
                                        aria-level="2" align="left">
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
                                @if (count($user->trainings) > 0)
                                    <div class="px-2 mt-3" style="background-color: #5690d9" role="heading"
                                        aria-level="2" align="left">
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
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
        </script>
</body>

</html>
