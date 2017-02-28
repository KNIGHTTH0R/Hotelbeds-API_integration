<!DOCTYPE HTML>

<html>
<head>
    <meta charset="utf-8">

    <title>Hotel Beds API</title>

    @include('includes.stylesheets')
    @include('includes.scripts')

</head>

<body>

    @include('includes.slider')

    @yield('content')


    <!-- Rooms -->
    <section class="rooms mt20">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    @yield('list')
                </div>
            </div>
        </div>
    </section>

</body>

</html>