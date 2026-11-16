@extends('layouts.admin')

@section('title', 'MDA return form')

@section('content')

<div class="max-w-4xl mx-auto bg-white shadow rounded-lg p-8">
    <p class="text-sm text-gray-500 mb-4">Print this form, or copy the same headings into Excel. One form for each initiative. If the MDA has none, write NIL RETURN at the top and still name the focal person.</p>

    <h2 class="text-xl font-bold mb-6">Development initiative return</h2>

    @foreach([
        'A. General' => ['Name of MDA','Name of initiative','Code, if any','Sector','Geographical coverage','Nature of initiative','Date of commencement','Expected completion date','Current status (proposed, under negotiation, approved, awaiting effectiveness, ongoing, delayed, suspended, completed or cancelled)'],
        'B. Partner and funding' => ['Development partner','Type of partner','Funding institution, if different','Nature of assistance','Funding arrangement','Agreement or approval date','Currency'],
        'C. Purpose and who implements' => ['Objectives','Key activities','Lead implementing MDA','Other delivering agencies','Other participating MDAs','Supervising political head, where applicable','How the programme is coordinated','State priority it supports'],
        'D. Beneficiaries' => ['Direct beneficiaries','Indirect beneficiaries','Beneficiary groups','LGAs covered','Expected outcomes','Are these beneficiary figures estimates? Yes or No'],
        'E. Money' => ['Total approved value','Development partner contribution','Federal contribution','Ondo State contribution','Other contributions','Counterpart funding required','Counterpart funding paid','Counterpart still unpaid','Funds drawn down','Cumulative expenditure','Date these figures are true up to','Source of the figures','Exchange rate and date, if the original amount is not in naira'],
        'F. Progress' => ['Current implementation status','Approved start and end','Physical progress (%)','Financial progress','Achievements so far','Activities still outstanding','Challenges','Government action required','Next milestone','Date of this progress update'],
        'G. Officers' => ['Programme officer','Designation','Department','Official phone','Official email','Supervising director','MDA focal person','Alternate focal person','Date of submission'],
    ] as $section => $lines)
        <h3 class="font-bold text-green-900 mt-8 mb-3">{{ $section }}</h3>
        <table class="w-full border border-gray-300">
            @foreach($lines as $line)
                <tr>
                    <td class="border border-gray-300 px-3 py-3 w-1/2">{{ $line }}</td>
                    <td class="border border-gray-300 px-3 py-3"></td>
                </tr>
            @endforeach
        </table>
    @endforeach

    <h3 class="font-bold text-green-900 mt-8 mb-3">H. Certification</h3>
    <p class="mb-4">I certify that the information in this return was compiled from the records available to this MDA and is, to the best of my knowledge, accurate as at the date of submission.</p>
    <table class="w-full border border-gray-300">
        @foreach(['Name of reporting officer','Designation','Signature','Date','Name of authorising officer','Designation','Signature or official approval','Date'] as $line)
            <tr>
                <td class="border border-gray-300 px-3 py-3 w-1/2">{{ $line }}</td>
                <td class="border border-gray-300 px-3 py-3"></td>
            </tr>
        @endforeach
    </table>

    <p class="mt-6 text-sm text-gray-600">After a return is checked, enter it on the project page under Development register. Mark it Reported until the figures are confirmed. Do not treat a submitted form as verified on its own.</p>
</div>

@endsection
