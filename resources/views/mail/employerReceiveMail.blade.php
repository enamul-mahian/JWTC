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
                        <p>Dear Employer, {{ $employer->name }},</p>
                        <p class="ms-2">We are pleased to inform you that the job seeker profile for
                            <strong><a href="{{ route('cv.view', $jobSeeker->id) }}" class="text-decoration-none">
                                    {{ $jobSeeker->name }}</a></strong> has been completed. You can now view their CV by
                            clicking the link below:
                        </p>
                        <div class="text-center">
                            <a href="{{ route('cv.view', $jobSeeker->id) }}" class="text-decoration-none">
                                <button class="btn btn-sm btn-outline-success px-2">view cv</button>
                            </a>
                        </div>

                        <p class="ms-2 mt-3">
                            Thank you for using our service. If you have any questions, please feel free to contact us.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js (required for Bootstrap) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
