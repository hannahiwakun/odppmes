@extends('layouts.admin')

@section('title','MDAs')

@section('content')

@if(session('success'))
<div style="background:#d1fae5;padding:12px;border-radius:6px;margin-bottom:20px;color:#065f46;">
    {{ session('success') }}
</div>
@endif

<div class="flex justify-between mb-5">

    <h2 class="text-2xl font-bold">
        Ministries, Departments & Agencies
    </h2>

    <a href="{{ route('mdas.create') }}"
       style="background:#15803d;color:white;padding:10px 18px;border-radius:6px;text-decoration:none;">
        + Add New MDA
    </a>

</div>

<table border="1" width="100%" cellpadding="10" cellspacing="0">

<thead style="background:#166534;color:white;">

<tr>

<th>ID</th>
<th>Name</th>
<th>Code</th>
<th>Email</th>
<th>Phone</th>
<th>Status</th>
<th width="180">Action</th>

</tr>

</thead>

<tbody>

@forelse($mdas as $mda)

<tr>

<td>{{ $mda->id }}</td>
<td>{{ $mda->name }}</td>
<td>{{ $mda->code }}</td>
<td>{{ $mda->email }}</td>
<td>{{ $mda->phone }}</td>

<td>

@if($mda->status)

Active

@else

Inactive

@endif

</td>

<td>

<a href="{{ route('mdas.edit',$mda) }}">Edit</a>

|

<form action="{{ route('mdas.destroy',$mda) }}"
      method="POST"
      style="display:inline;">

@csrf
@method('DELETE')

<button onclick="return confirm('Delete this MDA?')">
Delete
</button>

</form>

</td>

</tr>

@empty

<tr>

<td colspan="7" align="center">

No records found.

</td>

</tr>

@endforelse

</tbody>

</table>

@endsection