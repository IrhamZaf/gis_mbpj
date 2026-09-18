{{-- Shared expandable unit menus for all roles — all units visible (cross-unit read) --}}
@php
  use App\Support\UnitModule;
  $navUnits = $navUnits ?? UnitModule::navUnits();
@endphp

@foreach ($navUnits as $navUnit)
  @php
    $unitSlug = UnitModule::unitSlugFromCode($navUnit->code);
    if (! $unitSlug) {
      continue;
    }
    $isOwnUnit = $user->unit_id && (int) $user->unit_id === (int) $navUnit->id;
    $isUnitActive = UnitModule::isUnitRouteActive($unitSlug, $currentRouteName);
    $cats = $navUnit->categories->filter(fn ($c) => in_array($c->code, UnitModule::CATEGORY_CODES, true)
      || $c->code === 'CERUN_RUNTUH');
  @endphp
  <li class="menu-item {{ $isUnitActive ? 'active open' : '' }}">
    <a href="javascript:void(0);" class="menu-link menu-toggle">
      <i class="icon-base ti {{ UnitModule::unitIcon($navUnit->code) }}"></i>
      <div>
        {{ $navUnit->name }}
        @if (! $user->isSuperadmin() && ! $user->isDirector() && ! $isOwnUnit)
          <span class="badge bg-label-secondary ms-1" style="font-size:.65rem;">{{ __('app.read_only_badge') }}</span>
        @endif
      </div>
    </a>
    <ul class="menu-sub">
      <li class="menu-item {{ $currentRouteName === $unitSlug.'.dashboard' ? 'active' : '' }}">
        <a href="{{ route($unitSlug.'.dashboard') }}" class="menu-link">
          <i class="icon-base ti tabler-smart-home me-1" style="font-size:1rem;"></i>
          <div>{{ __('app.dashboard') }}</div>
        </a>
      </li>
      @foreach ($cats as $navCat)
        @php
          $catSlug = UnitModule::categorySlugFromCode($navCat->code);
          if (! $catSlug) {
            continue;
          }
          $catRoute = $unitSlug.'.'.$catSlug;
          $isCatActive = UnitModule::isCategoryRouteActive($unitSlug, $catSlug, $currentRouteName);
        @endphp
        <li class="menu-item {{ $isCatActive ? 'active' : '' }}">
          <a href="{{ route($catRoute) }}" class="menu-link">
            <i class="icon-base ti {{ UnitModule::categoryIcon($navCat->code) }} me-1" style="font-size:1rem;"></i>
            <div>{{ $navCat->display_name }}</div>
          </a>
        </li>
      @endforeach
    </ul>
  </li>
@endforeach
