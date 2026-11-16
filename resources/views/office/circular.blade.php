@extends('layouts.admin')

@section('title', 'Circular to MDAs')

@section('content')

<div class="max-w-4xl mx-auto bg-white shadow rounded-lg p-8">
    <p class="text-sm text-gray-500 mb-6">Draft for approval. Fill the placeholders in brackets before this is issued. The fourteen working days is a suggested deadline, not an existing government directive.</p>

    <div class="text-center mb-8">
        <p class="font-bold">GOVERNMENT OF ONDO STATE</p>
        <p class="font-bold mt-2">[NAME OF THE OFFICE]</p>
        <p class="mt-4 font-semibold">CIRCULAR TO ALL MINISTRIES, DEPARTMENTS AND AGENCIES (MDAs)</p>
    </div>

    <p class="font-semibold mb-4">SUBJECT: REQUEST FOR INFORMATION ON DEVELOPMENT PARTNERSHIPS, FUNDED PROGRAMMES, PROJECTS AND OTHER DEVELOPMENT INITIATIVES IN ONDO STATE</p>

    <div class="space-y-4 leading-7 text-gray-800">
        <p>The establishment of the [Name of Office] is part of the efforts of the Ondo State Government to strengthen development coordination, improve engagement with development partners and facilitate the alignment of development initiatives with the priorities and strategic objectives of the State Government.</p>
        <p>The Office is established to facilitate coordination and information sharing on development partnerships and initiatives involving Ministries, Departments and Agencies (MDAs), development partners and other relevant stakeholders. It is also intended to promote timely government engagement with emerging initiatives, support inter-agency collaboration and provide a consolidated overview of development programmes and their contributions to the development objectives of the State.</p>
        <p>For the avoidance of doubt, the mandate of the Office is primarily one of development coordination and facilitation. It is not intended to take over the statutory functions or project implementation responsibilities of any MDA or designated implementing agency. Rather, the Office seeks to complement existing institutional arrangements by facilitating communication, promoting coordination and providing relevant information to support informed decision-making at the appropriate levels of government.</p>
        <p>In furtherance of this mandate, all Ministries, Departments and Agencies are hereby requested to provide information on all existing, ongoing, approved, proposed or pipeline development projects, programmes, interventions and funding initiatives within their respective areas of responsibility that involve development partners, external assistance, grants, loans, counterpart funding or other forms of development support.</p>
        <p>The information to be submitted shall include, but not be limited to, the name and objectives of the initiative, participating and implementing agencies, development partner, funding arrangement, programme value, counterpart funding obligations and payments, disbursements, implementation status, beneficiaries and designated programme focal persons.</p>
        <p>All MDAs are requested to complete the attached standard reporting template and submit the duly completed return, together with relevant supporting information where available, to this Office within fourteen (14) working days of receipt of this circular.</p>
        <p>Each MDA is also requested to designate an appropriate officer to serve as its Development Partners Focal Person for subsequent communication, information updates and coordination with this Office. An alternate officer should also be named.</p>
        <p>Where an MDA has no such initiative to report, it is requested to submit a formal Nil Return within the stipulated period.</p>
        <p>The cooperation of all MDAs is solicited to ensure the establishment of a reliable and up-to-date register of development initiatives across the State. This exercise will support improved development coordination, facilitate timely government engagement, strengthen information sharing and enhance the availability of relevant information for planning, policy decisions and development reporting.</p>
        <p>Please accept the assurances of the Office's highest regards.</p>
    </div>

    <div class="mt-10">
        <p>[NAME OF AUTHORISED OFFICER]</p>
        <p>[DESIGNATION]</p>
        <p>[NAME OF OFFICE]</p>
    </div>

    <p class="mt-8">
        <a href="{{ route('office.mda-return') }}" class="text-green-800 font-semibold">Open the MDA return form</a>
    </p>
</div>

@endsection
