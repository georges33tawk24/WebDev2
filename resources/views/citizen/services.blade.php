@extends('layouts.admin')

@section('title', 'Browse Services')
@section('page-title', 'Browse Services')

@section('content')

<div style="display:flex; flex-direction:column; gap:24px;">

    <div>
        <h1 style="font-size:42px; font-weight:700; margin-bottom:10px;">
            Browse Services
        </h1>

        <p style="font-size:18px; color:#6b7280;">
            Search and apply for available government services.
        </p>
    </div>

    <form method="GET"
          action="{{ route('citizen.services') }}"
          class="card">

        <div style="display:grid;
                    grid-template-columns:2fr 1fr 1fr auto;
                    gap:16px;
                    align-items:end;">

            <div>
                <label style="font-weight:600; display:block; margin-bottom:8px;">
                    Search
                </label>

                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search services..."
                       style="width:100%;
                              padding:12px;
                              border:1px solid #d1d5db;
                              border-radius:10px;">
            </div>

            <div>
                <label style="font-weight:600; display:block; margin-bottom:8px;">
                    Office
                </label>

                <select name="office_id"
                        style="width:100%;
                               padding:12px;
                               border:1px solid #d1d5db;
                               border-radius:10px;">

                    <option value="">All Offices</option>

                    @foreach($offices as $office)
                        <option value="{{ $office->id }}"
                            @selected(request('office_id') == $office->id)>
                            {{ $office->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="font-weight:600; display:block; margin-bottom:8px;">
                    Category
                </label>

                <select name="category_id"
                        style="width:100%;
                               padding:12px;
                               border:1px solid #d1d5db;
                               border-radius:10px;">

                    <option value="">All Categories</option>

                    @foreach($categories as $category)
                        <option value="{{ $category->id }}"
                            @selected(request('category_id') == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit"
                    class="btn-primary"
                    style="height:48px;">
                Search
            </button>

        </div>

    </form>

    <div style="display:grid;
                grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
                gap:24px;">

        @forelse($services as $service)

            <div class="card">

                <div style="display:flex;
                            justify-content:space-between;
                            align-items:flex-start;
                            gap:12px;
                            margin-bottom:16px;">

                    <div>
                        <h2 style="font-size:24px;
                                   font-weight:700;
                                   margin-bottom:6px;">

                            {{ $service->name }}

                        </h2>

                        <p style="color:#6b7280;">
                            {{ $service->office->name ?? 'No office' }}

                            @if($service->category)
                                • {{ $service->category->name }}
                            @endif
                        </p>
                    </div>

                </div>

                <p style="color:#374151;
                          line-height:1.7;
                          margin-bottom:18px;">

                    {{ Str::limit($service->description, 120) }}

                </p>

                <div style="display:flex;
                            flex-direction:column;
                            gap:8px;
                            margin-bottom:22px;">

                    <p>
                        <strong>Price:</strong>
                        ${{ number_format($service->price, 2) }}
                    </p>

                    <p>
                        <strong>Duration:</strong>

                        {{ $service->estimated_duration_minutes
                            ? $service->estimated_duration_minutes . ' minutes'
                            : 'N/A' }}
                    </p>

                </div>

                <div style="display:flex; gap:12px;">

                    <a href="{{ route('citizen.services.show', $service) }}"
                       class="btn-secondary"
                       style="text-decoration:none; flex:1; text-align:center;">

                        View

                    </a>

                    <a href="{{ route('citizen.requests.create', $service) }}"
                       class="btn-primary"
                       style="text-decoration:none; flex:1; text-align:center;">

                        Apply

                    </a>

                </div>

            </div>

        @empty

            <div class="card"
                 style="grid-column:1/-1;
                        text-align:center;
                        color:#6b7280;">

                No services found.

            </div>

        @endforelse

    </div>

</div>

@endsection