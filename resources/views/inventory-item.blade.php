<li class="item list-group-item d-flex justify-content-between align-items-center js-inventory-item"
    data-id="{{ $item->id }}"
    data-type-id="{{ $item->typeId }}"
    data-label="{{ $item->label }}"
    data-is-container="{{ $item->isContainer }}"
    style="{{ $item->depth > 0 ? "padding-left: " . (1.25 + ($item->depth * 1.25)) . "rem;" : ""}}"
>
    <div class="item-label d-flex justify-content-start align-items-center">
        {{ $item->label }}
        @if($item->state)
            <span class="badge badge-primary">{{ $item->state }}</span>
        @endif
        @if(!$item->hasAllPortions)
            <div class="progress">
                <div class="progress-bar"
                     style="width: {{ $item->remainingPortionsPercentage }}%;"
                ></div>
            </div>
        @endif
        @if($item->quantity > 1)
            <span class="badge badge-primary">
                {{ $item->quantity }}
            </span>
        @endif
    </div>
</li>
@foreach($item->surface as $surfaceItem)
    @include('inventory-item', ['item' => $surfaceItem])
@endforeach
