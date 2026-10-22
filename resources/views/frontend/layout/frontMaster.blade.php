<!DOCTYPE html>
<html lang="en">

<head>
    <style>
    #map {
        height: 600px;
    }

    .site-content {
        padding-top: 58px;
    }

    @media (max-width: 768px) {
        .site-content {
            padding-top: 64px;
        }
    }
</style>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    @if(config('jwtc_design.enabled'))
        <meta name="description" content="@yield('meta_description', 'Discover job opportunities and manage recruitment applications with Join Gallagher.')">
    @else
        <meta name="keywords" content="Bootstrap, Landing page, Template, Registration, Landing">
    @endif
    <meta name="author" content="Join Gallagher">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title> @yield('title') </title>
    @if(isset($setting?->favicon) && $setting->favicon)
        <link rel="icon" href="{{ asset('assets/images/setting/' . $setting->favicon) }}" type="image/x-icon">
    @endif
    @include('frontend.includes.style')
    @if(config('jwtc_design.enabled'))
        <link rel="stylesheet" href="{{ asset('theme/jwtc.css') }}">
    @endif
    @stack('style')
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map {
            height: 600px;
        }
    </style>
</head>

<body @if(config('jwtc_design.enabled'))class="jwtc-redesign"@endif>
    @if(config('jwtc_design.enabled'))
        <a href="#jw-main" class="jw-skip">Skip to main content</a>
    @endif

    <!-- Header Section Start -->
    <header>
        <!-- Navbar Start -->
        <nav class="navbar navbar-expand-lg fixed-top scrolling-navbar">
            @include('frontend.includes.navber')
        </nav>
        <!-- Navbar End -->
    </header>

    <!-- Header Section End -->
 <main class="site-content" id="jw-main">
    @yield('content')
</main>
    <!-- Footer Section Start -->
    {{-- <footer>
        @include('frontend.includes.footer')
    </footer> --}}
    <!-- Footer Section End -->

    <!-- Go To Top Link -->
    <a href="#" class="back-to-top">
        <i class="lni-arrow-up"></i>
    </a>

    <!-- Preloader -->
    <div id="preloader">
        <div class="loader" id="loader-1"></div>
    </div>
    <!-- End Preloader -->
    <style>
        .marker {
            width: 15px;
            height: 15px;
            background-color: #0038f1;
            border-radius: 50%;
            cursor: pointer;
        }
    </style>
    @include('frontend.includes.script')
    <script>
        // success message popup notification
        @if (Session::has('success'))
            toastr.success("{{ Session::get('success') }}");
        @endif

        // info message popup notification
        @if (Session::has('info'))
            toastr.info("{{ Session::get('info') }}");
        @endif

        // warning message popup notification
        @if (Session::has('warning'))
            toastr.warning("{{ Session::get('warning') }}");
        @endif

        // error message popup notification
        @if (Session::has('error'))
            toastr.error("{{ Session::get('error') }}");
        @endif
    </script>
    <script>
        @foreach ($errors->all() as $error)
            toastr.error("{{ $error }}");
        @endforeach
        $(document).ready(function() {
            // $('#summernote').summernote();
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>

    {{-- Language setting --}}
    <script type="text/javascript">
        var url = "{{ route('changeLang') }}";

        $(".changeLang").change(function() {
            window.location.href = url + "?lang=" + $(this).val();
        });
    </script>
    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBxJIqIIz-wJi4Wt3UtBYyMiW50JQy6yeY&loading=async&callback=initMap">
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let map;

            function initMap() {
                map = new google.maps.Map(document.getElementById("map"), {
                    center: {
                        lat: 52.1326,
                        lng: 5.2913
                    },
                    zoom: 8,
                });

                map.addListener("click", function(event) {
                    const clickedLocation = event.latLng;
                    geocodeLatLng(clickedLocation);
                });
                const geocoder = new google.maps.Geocoder();
                // Fetch job counts by country and add markers
                fetch('/job-counts-by-country')
                    .then(response => response.json())
                    .then(data => {
                        data.countryData.forEach(country => {
                            console.log(country);

                            // Geocode each country name to get its latitude and longitude
                            geocoder.geocode({
                                'address': country.name
                            }, function(results, status) {
                                if (status === google.maps.GeocoderStatus.OK) {
                                    const latLng = results[0].geometry.location;

                                    // Create a marker with the country name, job count, and jobseeker count
                                    const marker = new google.maps.Marker({
                                        position: latLng,
                                        map: map,
                                        title: `${country.name}: ${country.jobs_count} jobs, ${country.job_seeker_count} job seekers`
                                    });

                                    // Add an info window to show details on marker click
                                    const infowindow = new google.maps.InfoWindow({
                                        content: `<a href="/get-jobs/${country.name}"> <strong>${country.name}</strong> <br/> ${country.jobs_count} jobs <br/> ${country.job_seeker_count} job seekers </a>`
                                    });

                                    // Add a click listener to open the info window
                                    marker.addListener('click', function() {
                                        infowindow.open(map, marker);
                                    });
                                } else {
                                    console.log(
                                        "Geocode was not successful for the following reason: " +
                                        status);
                                }
                            });
                        });
                    })

                    .catch(error => console.log('Error fetching job counts: ', error));
            }

            function geocodeLatLng(latLng) {
                const geocoder = new google.maps.Geocoder();

                geocoder.geocode({
                    location: latLng
                }, function(results, status) {
                    if (status === "OK") {
                        if (results[0]) {
                            const addressComponents = results[0].address_components;
                            let country = "";

                            for (let component of addressComponents) {
                                if (component.types.includes("country")) {
                                    country = component.long_name;
                                    break;
                                }
                            }

                            if (country) {
                                $.ajax({
                                    type: 'GET',
                                    url: '/get-job-by-country',
                                    data: {
                                        country: country
                                    },
                                    dataType: "JSON",
                                    success: function(res) {
                                        if (res) {
                                            console.log(res.name);
                                            window.location.href = '/get-jobs/' + res.name;
                                        } else {
                                            console.log("No job data returned.");
                                        }
                                    },
                                    error: function(error) {
                                        console.error("Error fetching jobs:", error);
                                    }
                                });
                            } else {
                                alert("Country information not found.");
                            }
                        } else {
                            alert("No address found for this location.");
                        }
                    } else {
                        alert("Geocoder failed due to: " + status);
                    }
                });
            }

            // Call initMap on window load
            window.onload = initMap;
        });
    </script>

    {{-- Shamim  Code --}}
    {{-- 
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let map;

            function initMap() {
                map = new google.maps.Map(document.getElementById("map"), {
                    center: {
                        lat: 52.1326, // Central coordinates for initial map view
                        lng: 5.2913
                    },
                    zoom: 2, // Zoom level to show the whole world
                });

                const geocoder = new google.maps.Geocoder();

                // Fetch job counts by country and add markers
                fetch('/job-counts-by-country')
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(country => {
                            // Use the Geocoder to get the latitude and longitude from the country name
                            geocoder.geocode({
                                'address': country.name
                            }, function(results, status) {
                                if (status === google.maps.GeocoderStatus.OK) {
                                    const latLng = results[0].geometry.location;

                                    // Create a marker with the country name and job count
                                    const marker = new google.maps.Marker({
                                        position: latLng,
                                        map: map,
                                        title: `${country.name}: ${country.jobs_count} jobs`
                                    });

                                    // Add an info window to show details on marker click
                                    const infowindow = new google.maps.InfoWindow({
                                        content: `<a href=""> <strong> ${country.name} </strong> <br/> ${country.jobs_count} jobs </a>`
                                    });

                                    // Optional: Add a click listener to the marker to open the info window
                                    marker.addListener('click', function() {
                                        infowindow.open(map, marker);
                                    });
                                } else {
                                    console.log(
                                        "Geocode was not successful for the following reason: " +
                                        status);
                                }
                            });
                        });
                    })
                    .catch(error => console.log('Error fetching job counts: ', error));
            }


            // Call initMap on window load
            window.onload = initMap;
        });
    </script> --}}

    {{-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            let map;

            function initMap() {
                map = new google.maps.Map(document.getElementById("map"), {
                    center: {
                        lat: 52.1326,
                        lng: 5.2913
                    },
                    zoom: 8, // Global view to show all countries
                });

                const geocoder = new google.maps.Geocoder();

                // Fetch job counts by country and add markers
                fetch('/job-counts-by-country')
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(country => {
                            // Geocode each country name to get its latitude and longitude
                            geocoder.geocode({
                                'address': country.name
                            }, function(results, status) {
                                if (status === google.maps.GeocoderStatus.OK) {
                                    const latLng = results[0].geometry.location;

                                    // Create a marker with the country name and job count
                                    const marker = new google.maps.Marker({
                                        position: latLng,
                                        map: map,
                                        title: `${country.name}: ${country.jobs_count} jobs`
                                    });

                                    // Add an info window to show details on marker click
                                    const infowindow = new google.maps.InfoWindow({
                                        content: `<a href="/get-jobs/${country.name} "> <strong> ${country.name} </strong> <br/> ${country.jobs_count} jobs </a>`
                                    });

                                    // Add a click listener to open the info window
                                    marker.addListener('click', function() {
                                        infowindow.open(map, marker);
                                    });
                                } else {
                                    console.log(
                                        "Geocode was not successful for the following reason: " +
                                        status);
                                }
                            });
                        });
                    })
                    .catch(error => console.log('Error fetching job counts: ', error));

                // Add a listener for clicks on the map
                map.addListener("click", function(event) {
                    const clickedLocation = event.latLng;
                    geocodeLatLng(clickedLocation);
                });
            }

            // Function to reverse geocode clicked location and fetch job data by country
            function geocodeLatLng(latLng) {
                const geocoder = new google.maps.Geocoder();

                geocoder.geocode({
                    location: latLng
                }, function(results, status) {
                    if (status === "OK") {
                        if (results[0]) {
                            const addressComponents = results[0].address_components;
                            let country = "";

                            for (let component of addressComponents) {
                                if (component.types.includes("country")) {
                                    country = component.long_name;
                                    break;
                                }
                            }

                            if (country) {
                                $.ajax({
                                    type: 'GET',
                                    url: '/get-job-by-country',
                                    data: {
                                        country: country
                                    },
                                    dataType: "JSON",
                                    success: function(res) {
                                        if (res) {
                                            console.log(res.name);
                                            window.location.href = '/get-jobs/' + res.name;
                                        } else {
                                            console.log("No job data returned.");
                                        }
                                    },
                                    error: function(error) {
                                        console.error("Error fetching jobs:", error);
                                    }
                                });
                            } else {
                                alert("Country information not found.");
                            }
                        } else {
                            alert("No address found for this location.");
                        }
                    } else {
                        alert("Geocoder failed due to: " + status);
                    }
                });
            }

            // Initialize the map on window load
            window.onload = initMap;
        });
    </script> --}}


<!--Start of Tawk.to Script-->
<script type="text/javascript">
    var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
    (function(){
    var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
    s1.async=true;
    s1.src='https://embed.tawk.to/6731126e4304e3196adfe7dd/1icbr00au';
    s1.charset='UTF-8';
    s1.setAttribute('crossorigin','*');
    s0.parentNode.insertBefore(s1,s0);
    })();
</script>
<!--End of Tawk.to Script-->

    @stack('js')
    @stack('script')
</body>

</html>
