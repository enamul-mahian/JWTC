<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Portal</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card mt-5">

                    <div class="card-body">
                        <p class="mb-1">Dear {{ $user->name }},</p>
                        <p>
                            Don't forget to apply for the latest job which will expire soon.
                        </p>
                        <a href="{{ route('job-details.show', $job->id) }}" class="text-decoration-none mb-5">
                            <ul class="list-group-disc">
                                <li class="text-primary"> {{ $job->title }}</li>
                                <li class="text-dark"> {{ company_name($job?->company?->id) }}</li>
                                <li class="text-danger">
                                    {{ Carbon\Carbon::parse($job->application_deadline)->format('d M Y') }}
                                </li>
                            </ul>
                        </a>
                        <p class="ms-2 mb-0">Find Latest Job at <a href="{{ route('home') }}"
                                class="text-decoration-none text-info">
                                jobportal.com</a></p>
                        <p class="ms-2">Thank You.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js (required for Bootstrap) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
