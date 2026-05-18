@extends('layouts.admin')

@section('title', __('ui.citizen.services_title'))
@section('page-title', __('ui.citizen.services_title'))

@section('content')

<div style="display:flex; flex-direction:column; gap:24px;">

    <div>
        <h1 style="font-size:42px; font-weight:700; margin-bottom:10px;">
            {{ __('ui.citizen.services_title') }}
        </h1>

        <p style="font-size:18px; color:#6b7280;">
            {{ __('ui.citizen.services_sub') }}
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
                    {{ __('ui.citizen.search_label') }}
                </label>

                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="{{ __('ui.citizen.search_placeholder') }}"
                       style="width:100%;
                              padding:12px;
                              border:1px solid #d1d5db;
                              border-radius:10px;">
            </div>

            <div>
                <label style="font-weight:600; display:block; margin-bottom:8px;">
                    {{ __('ui.citizen.office_filter') }}
                </label>

                <select name="office_id"
                        style="width:100%;
                               padding:12px;
                               border:1px solid #d1d5db;
                               border-radius:10px;">

                    <option value="">{{ __('ui.citizen.all_offices') }}</option>

                    @foreach($offices as $office)
                        <option value="{{ $office->id }}"
                            @selected(request('office_id') == $office->id)>
                            {{ $office->localized('name') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="font-weight:600; display:block; margin-bottom:8px;">
                    {{ __('ui.citizen.category_filter') }}
                </label>

                <select name="category_id"
                        style="width:100%;
                               padding:12px;
                               border:1px solid #d1d5db;
                               border-radius:10px;">

                    <option value="">{{ __('ui.citizen.all_categories') }}</option>

                    @foreach($categories as $category)
                        <option value="{{ $category->id }}"
                            @selected(request('category_id') == $category->id)>
                            {{ $category->localized('name') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit"
                    class="btn-primary"
                    style="height:48px;">
                {{ __('ui.search') }}
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

                            {{ $service->localized('name') }}

                        </h2>

                        <p style="color:#6b7280;">
                            {{ $service->office?->localized('name') ?? __('ui.citizen.no_office') }}

                            @if($service->category)
                                • {{ $service->category?->localized('name') }}
                            @endif
                        </p>
                    </div>

                </div>

                <p style="color:#374151;
                          line-height:1.7;
                          margin-bottom:18px;">

                    {{ Str::limit($service->localized('description') ?? '', 120) }}

                </p>

                <div style="display:flex;
                            flex-direction:column;
                            gap:8px;
                            margin-bottom:22px;">

                    <p>
                        <strong>{{ __('ui.citizen.price_colon') }}</strong>
                        {{ localized_money($service->price) }}
                    </p>

                    <p>
                        <strong>{{ __('ui.citizen.duration_colon') }}</strong>

                        @if($service->estimated_duration_minutes)
                            {{ localized_digits(__('ui.citizen.minutes_count', ['count' => $service->estimated_duration_minutes])) }}
                        @else
                            {{ __('ui.na') }}
                        @endif
                    </p>

                </div>

                <div style="display:flex; gap:12px;">

                    <a href="{{ route('citizen.services.show', $service) }}"
                       class="btn-secondary"
                       style="text-decoration:none; flex:1; text-align:center;">

                        {{ __('ui.citizen.view_details') }}

                    </a>

                    <a href="{{ route('citizen.requests.create', $service) }}"
                       class="btn-primary"
                       style="text-decoration:none; flex:1; text-align:center;">

                        {{ __('ui.citizen.apply') }}

                    </a>

                </div>

            </div>

        @empty

            <div class="card"
                 style="grid-column:1/-1;
                        text-align:center;
                        color:#6b7280;">

                {{ __('ui.citizen.no_services_found') }}

            </div>

        @endforelse

    </div>

</div>

@endsection
