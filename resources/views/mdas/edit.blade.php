@extends('layouts.admin')

@section('title','Edit MDA')

@section('content')

<div class="max-w-3xl mx-auto bg-white shadow rounded-lg p-6">

<h2 class="text-2xl font-bold mb-6">
Edit MDA
</h2>

@if ($errors->any())
<div style="background:#fee2e2;padding:10px;margin-bottom:20px;border-radius:6px;color:#991b1b;">
<ul>
@foreach($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif

<form action="{{ route('mdas.update',$mda) }}" method="POST">

@csrf
@method('PUT')

<label>MDA Name</label>

<input
type="text"
name="name"
value="{{ old('name',$mda->name) }}"
style="width:100%;padding:10px;margin-bottom:15px;">

<label>Code</label>

<input
type="text"
name="code"
value="{{ old('code',$mda->code) }}"
style="width:100%;padding:10px;margin-bottom:15px;">

<label>Email</label>

<input
type="email"
name="email"
value="{{ old('email',$mda->email) }}"
style="width:100%;padding:10px;margin-bottom:15px;">

<label>Phone</label>

<input
type="text"
name="phone"
value="{{ old('phone',$mda->phone) }}"
style="width:100%;padding:10px;margin-bottom:15px;">

<label>Contact Person</label>

<input
type="text"
name="contact_person"
value="{{ old('contact_person',$mda->contact_person) }}"
style="width:100%;padding:10px;margin-bottom:15px;">

<label>Address</label>

<textarea
name="address"
style="width:100%;padding:10px;margin-bottom:15px;">{{ old('address',$mda->address) }}</textarea>

<label>Status</label>

<select
name="status"
style="width:100%;padding:10px;margin-bottom:20px;">

<option value="1" {{ $mda->status ? 'selected' : '' }}>Active</option>

<option value="0" {{ !$mda->status ? 'selected' : '' }}>Inactive</option>

</select>

<button
type="submit"
style="background:green;color:white;padding:10px 20px;border:none;border-radius:5px;">
Update MDA
</button>

<a
href="{{ route('mdas.index') }}"
style="background:gray;color:white;padding:10px 20px;border-radius:5px;text-decoration:none;">
Cancel
</a>

</form>

</div>

@endsection