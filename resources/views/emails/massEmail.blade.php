<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Template</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card mt-5">
                    <div class="card-header bg-primary text-white">
                        <h2 class="text-center">{{ $details['subject'] }}</h2>
                    </div>
                    <div class="card-body">
                        <p>Hello [{{ $details['name'] }}],</p>
                        <p>
                            {{ $details['body'] }}
                        </p>
                        <p>Thank you for using our service!</p>
                    </div>
                    <div class="card-footer bg-secondary text-white">
                        <p class="text-center">&copy; {{ date('Y') }} {{ gs()->business_name }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js (required for Bootstrap) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
