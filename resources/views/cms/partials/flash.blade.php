@if(session('success'))
    <div class="mb-5 rounded-lg border border-green-400 bg-green-100 px-4 py-3 text-green-800" role="status">
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-5 rounded-lg border border-red-400 bg-red-100 px-4 py-3 text-red-800" role="alert">
        {{ session('error') }}
    </div>
@endif
@if($errors->any())
    <div class="mb-5 rounded-lg border border-red-400 bg-red-50 px-4 py-3 text-red-800" role="alert">
        <p class="font-semibold">Please correct the highlighted fields.</p>
        <ul class="mt-2 list-disc pl-5 text-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
