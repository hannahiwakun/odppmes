<form method="GET" action="{{ $action }}" class="flex flex-col gap-3 border-b border-gray-200 p-4 sm:flex-row sm:items-end">
    <p class="sr-only">Filter this list</p>
    <div class="flex-1">
        <label for="cms-search" class="mb-1 block text-sm font-medium text-gray-700">Search</label>
        <input id="cms-search" type="search" name="q" value="{{ request('q') }}"
               class="w-full rounded-lg border-gray-300 focus:border-green-700 focus:ring-green-700">
    </div>
    <div>
        <label for="cms-status" class="mb-1 block text-sm font-medium text-gray-700">Status</label>
        <select id="cms-status" name="status" class="rounded-lg border-gray-300 focus:border-green-700 focus:ring-green-700">
            <option value="">All</option>
            @foreach(\App\Cms\CmsWorkflowStatus::all() as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </div>
    @if (! empty($categoryOptions))
        <div>
            <label for="cms-category" class="mb-1 block text-sm font-medium text-gray-700">Category</label>
            <select id="cms-category" name="category" class="rounded-lg border-gray-300 focus:border-green-700 focus:ring-green-700">
                <option value="">All</option>
                @foreach ($categoryOptions as $categoryOption)
                    <option value="{{ $categoryOption }}" @selected(request('category') === $categoryOption)>{{ $categoryOption }}</option>
                @endforeach
            </select>
        </div>
    @endif
    <button type="submit" class="cms-btn cms-btn-primary">Filter</button>
</form>
