@extends('layouts.admin')

@section('title','Development Partners')

@section('content')

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-5">
        {{ session('success') }}
    </div>
@endif

<div class="flex justify-between items-center mb-6">

    <h2 class="text-2xl font-bold">
        Development Partners
    </h2>

    <a href="{{ route('development-partners.create') }}"
       class="bg-green-700 hover:bg-green-800 text-white px-5 py-2 rounded">
        + Add Development Partner
    </a>

</div>

<table class="min-w-full bg-white shadow rounded-lg">

    <thead class="bg-green-700 text-white">

        <tr>
            <th class="py-3 px-4 text-left">ID</th>
            <th class="py-3 px-4 text-left">Partner Name</th>
            <th class="py-3 px-4 text-left">Acronym</th>
            <th class="py-3 px-4 text-left">Type</th>
            <th class="py-3 px-4 text-left">Country</th>
            <th class="py-3 px-4 text-left">Status</th>
            <th class="py-3 px-4 text-center">Action</th>
        </tr>

    </thead>

    <tbody>

    @forelse($partners as $partner)

        <tr class="border-b">

            <td class="py-3 px-4">{{ $partner->id }}</td>

            <td class="py-3 px-4">{{ $partner->partner_name }}</td>

            <td class="py-3 px-4">{{ $partner->acronym }}</td>

            <td class="py-3 px-4">{{ $partner->partner_type }}</td>

            <td class="py-3 px-4">{{ $partner->country }}</td>

            <td class="py-3 px-4">
                @if($partner->status)
                    <span class="text-green-600 font-semibold">Active</span>
                @else
                    <span class="text-red-600 font-semibold">Inactive</span>
                @endif
            </td>

            <td class="py-3 px-4 text-center">

                <a href="{{ route('development-partners.edit',$partner->id) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">
                    Edit
                </a>

                <form action="{{ route('development-partners.destroy',$partner->id) }}"
                      method="POST"
                      class="inline">

                    @csrf
                    @method('DELETE')

                    <button
                        onclick="return confirm('Delete this Development Partner?')"
                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">

                        Delete

                    </button>

                </form>

            </td>

        </tr>

    @empty

        <tr>

            <td colspan="7" class="text-center py-5 text-gray-500">
                No Development Partners Found
            </td>

        </tr>

    @endforelse

    </tbody>

</table>

@endsection