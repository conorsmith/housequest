<li class="item js-inventory-item item-placement-{{ $item->depth }} {{ isset($isContents) ? "item-contents item-hidden" : "" }}"
    data-id="{{ $item->id }}"
    data-type-id="{{ $item->typeId }}"
    data-label="{{ $item->label }}"
    data-is-container="{{ $item->isContainer }}"
    data-whereabouts-id="{{ $item->whereabouts->id }}"
    data-whereabouts-type="{{ $item->whereabouts->type }}"
>
    <div class="item-label d-flex justify-content-start align-items-center">
        @if($item->depth > 3)
            <span class="item-indent-indicator badge badge-primary">+{{ $item->depth - 3 }}</span>
        @endif
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
@foreach($item->contents as $contentsItem)
    @include('inventory-item', ['item' => $contentsItem, 'isContents' => true])
@endforeach
@foreach($item->surface as $surfaceItem)
    @include('inventory-item', ['item' => $surfaceItem])
@endforeach
