<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=yes">
    <title>Cover</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/bootstrap.min.css') }}">
    <style>
        body {
            font-family: 'Arial', sans-serif;
        }

        .cover-letter {
            padding: 30px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .address {
            margin-bottom: 20px;
        }

        .body-content {
            text-align: justify;
        }

        .signature {
            margin-top: 30px;
        }
    </style>
</head>
<body>

    <div class="container-fluid" style="padding-left: 0px">
        <table>
            <tr>
                <td width="100%">
                    <div class="container">
                        <div class="cover-letter">
                    
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="address">
                                        <p>Date : {{ $user->cover_letter->date }}</p>
                                        <p>Organization Name :  {{ $user->cover_letter->organization }}</p>
                                        <p>Address 1 :  {{ $user->cover_letter->address_line_1 }}</p>
                                        <p>Address 2 :  {{ $user->cover_letter->address_line_2 }}</p>
                                        <p>City : {{ $user->cover_letter->city }}</p>
                                        
                                        <p>Subject : {{ $user->cover_letter->subject }}</p>
                                    </div>
                                </div>
                            </div>
                    
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="body-content">
                                        <p>{{ $user->cover_letter->first_name }} {{ $user->cover_letter->last_name }},</p>
                                        {!! $user->cover_letter->content !!}
                                    </div>
                                </div>
                            </div>
                    
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="signature">
                                        <p>Sincerely,<br>{{ $user->cover_letter->your_name }}</p>
                                        <br>
                                        @if($user->cover_letter->signature)
                                        <img src="{{ asset('assets/images/signature/'.$user->cover_letter->signature) }}" style="height: 60px" alt="" srcset="">
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <script src="{{ asset('frontend/assets/js/bootstrap.min.js') }}"></script>
</body>
</html>
