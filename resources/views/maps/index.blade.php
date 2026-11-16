@extends('layouts.admin')

@section('title', 'Project Map')

@section('content')

<div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">

        <div class="flex justify-between items-center">

            <div>
                <h2 class="text-3xl font-bold text-gray-800">
                    Project Location Map
                </h2>

                <p class="text-gray-500 mt-1">
                    Geographic view of development projects across Ondo State.
                </p>
            </div>

            <div class="text-right">
                <div class="text-2xl font-bold text-green-700">
                    {{ $projects->count() }}
                </div>

                <div class="text-sm text-gray-500">
                    Mapped Projects
                </div>
            </div>

        </div>

    </div>


    <!-- Map -->
    <div class="bg-white shadow rounded-lg p-4">

        <div id="map"
             style="height: 600px; width: 100%; border-radius: 10px;">
        </div>

    </div>

</div>


<!-- Leaflet -->
<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


<script>

    /*
    |--------------------------------------------------------------------------
    | Initialize Map
    |--------------------------------------------------------------------------
    */

    const map = L.map('map').setView(
        [7.2508, 5.2103],
        9
    );


    /*
    |--------------------------------------------------------------------------
    | OpenStreetMap
    |--------------------------------------------------------------------------
    */

    L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }
    ).addTo(map);


    /*
    |--------------------------------------------------------------------------
    | Project Data
    |--------------------------------------------------------------------------
    */

    const projects = @json($projects);


    /*
    |--------------------------------------------------------------------------
    | Marker Group
    |--------------------------------------------------------------------------
    */

    const markers = [];


    /*
    |--------------------------------------------------------------------------
    | Get Marker Colour
    |--------------------------------------------------------------------------
    */

    function getMarkerColor(status)
    {
        switch (status) {

            case 'Completed':
                return '#16a34a';

            case 'Planning':
                return '#eab308';

            case 'Suspended':
                return '#f97316';

            case 'Cancelled':
                return '#dc2626';

            case 'Ongoing':
            default:
                return '#2563eb';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Add Projects
    |--------------------------------------------------------------------------
    */

    projects.forEach(project => {

        if (
            project.latitude !== null &&
            project.longitude !== null
        ) {

            const latitude = parseFloat(project.latitude);
            const longitude = parseFloat(project.longitude);

            const color = getMarkerColor(project.status);


            /*
            | Circle Marker
            */

            const marker = L.circleMarker(
                [latitude, longitude],
                {
                    radius: 9,
                    color: color,
                    fillColor: color,
                    fillOpacity: 0.9,
                    weight: 2
                }
            ).addTo(map);


            /*
            | Popup
            */

            marker.bindPopup(`

                <div style="
                    min-width:260px;
                    font-family:Arial,sans-serif;
                ">

                    <div style="
                        font-size:17px;
                        font-weight:bold;
                        margin-bottom:8px;
                        color:#14532d;
                    ">
                        ${project.project_title ?? '-'}
                    </div>


                    <div style="margin-bottom:5px;">
                        <b>Project Code:</b>
                        ${project.project_code ?? '-'}
                    </div>


                    <div style="margin-bottom:5px;">
                        <b>MDA:</b>
                        ${project.mda ? project.mda.name : '-'}
                    </div>


                    <div style="margin-bottom:5px;">
                        <b>Development Partner:</b>
                        ${
                            project.development_partner
                            ? project.development_partner.partner_name
                            : '-'
                        }
                    </div>


                    <div style="margin-bottom:5px;">
                        <b>Sector:</b>
                        ${project.sector ?? '-'}
                    </div>


                    <div style="margin-bottom:5px;">
                        <b>LGA:</b>
                        ${project.lga ?? '-'}
                    </div>


                    <div style="margin-bottom:5px;">
                        <b>Status:</b>
                        <span style="
                            color:${color};
                            font-weight:bold;
                        ">
                            ${project.status ?? '-'}
                        </span>
                    </div>


                    <div style="margin-bottom:10px;">
                        <b>Progress:</b>
                        ${project.progress ?? 0}%
                    </div>


                    <div style="
                        background:#e5e7eb;
                        height:8px;
                        border-radius:5px;
                        margin-bottom:12px;
                    ">

                        <div style="
                            background:${color};
                            height:8px;
                            width:${project.progress ?? 0}%;
                            border-radius:5px;
                        "></div>

                    </div>


                    <a href="/projects/${project.id}"
                       style="
                           display:inline-block;
                           background:#15803d;
                           color:white;
                           padding:7px 12px;
                           border-radius:5px;
                           text-decoration:none;
                           font-weight:bold;
                       ">

                        View Project

                    </a>

                </div>

            `);


            markers.push(marker);
        }

    });


    /*
    |--------------------------------------------------------------------------
    | Automatically Fit Map to Projects
    |--------------------------------------------------------------------------
    */

    if (markers.length > 0) {

        const group = L.featureGroup(markers);

        map.fitBounds(
            group.getBounds().pad(0.15)
        );

    }


</script>


<!-- Map Legend -->

<div class="max-w-7xl mx-auto mt-4">

    <div class="bg-white shadow rounded-lg p-4">

        <h3 class="font-bold text-gray-700 mb-3">
            Project Status Legend
        </h3>

        <div class="flex flex-wrap gap-6 text-sm">

            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded-full bg-blue-600"></span>
                Ongoing
            </div>

            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded-full bg-green-600"></span>
                Completed
            </div>

            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded-full bg-yellow-500"></span>
                Planning
            </div>

            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded-full bg-orange-500"></span>
                Suspended
            </div>

            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded-full bg-red-600"></span>
                Cancelled
            </div>

        </div>

    </div>

</div>

@endsection