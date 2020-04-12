@extends('layouts.app')

@section('content')
<div class="container" style="padding-bottom: 4rem;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if(session("success"))
                <div class="alert alert-success">
                    {{ session("success") }}
                </div>
            @endif
            @if(session("info"))
                <div class="alert alert-info">
                    {{ session("info") }}
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    {{ $location->title }}
                </div>

                <div class="card-body">

                    <p>{{ $location->description }}</p>

                    <div class="d-flex justify-content-between flex-wrap">
                        @foreach($location->egresses as $egress)
                            <form action="/{{ $gameId }}/go/{{ $egress->id }}" method="POST" class="action-button">
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-light btn-block" style="margin-bottom: 1rem;">
                                    Go to {{ $egress->label }}
                                </button>
                            </form>
                        @endforeach
                    </div>

                    @if($location->id === "the-street")
                        <form action="/new-game" method="POST" class="action-button">
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-light btn-block">
                                You have died. Try again?
                            </button>
                        </form>
                    @endif

                </div>

                <ul class="list-group list-group-flush" style="border-top: 1px solid rgba(0, 0, 0, 0.125);">
                    @foreach($location->objects as $object)
                        <li class="item list-group-item d-flex justify-content-between align-items-center js-inventory-item"
                            data-id="{{ $object->id }}"
                        >
                            <div class="item-label d-flex justify-content-start align-items-center">
                                {{ $object->label }}
                                @if(!$object->hasAllPortions)
                                    <div class="progress">
                                        <div class="progress-bar"
                                             style="width: {{ $object->remainingPortionsPercentage }}%;"
                                        ></div>
                                    </div>
                                @endif
                                @if($object->quantity > 1)
                                    <span class="badge badge-light">
                                    {{ $object->quantity }}
                                </span>
                                @endif
                            </div>
                            <div class="d-flex">
                                @if($object->isContainer)
                                    <button type="type" class="btn btn-light btn-sm mr-1" data-toggle="modal" data-target="#container-{{ $object->typeId }}">
                                        Open
                                    </button>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            @if($player->inventory or $player->xp)
                <div class="card" style="margin-top: 2rem;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>Player</div>
                        @if($player->xp)
                            <span class="badge badge-light">{{ $player->xp }} XP</span>
                        @endif
                    </div>

                    <ul class="list-group list-group-flush">
                        @foreach($player->inventory as $object)
                            <li class="item list-group-item d-flex justify-content-between align-items-center js-inventory-item"
                                data-id="{{ $object->id }}"
                            >
                                <div class="item-label d-flex justify-content-start align-items-center">
                                    {{ $object->label }}
                                    @if(!$object->hasAllPortions)
                                        <div class="progress">
                                            <div class="progress-bar"
                                                 style="width: {{ $object->remainingPortionsPercentage }}%;"
                                            ></div>
                                        </div>
                                    @endif
                                    @if($object->quantity > 1)
                                        <span class="badge badge-light">
                                            {{ $object->quantity }}
                                        </span>
                                    @endif
                                </div>
                                <div class="d-flex">
                                    @if($object->isContainer)
                                        <button type="button" class="btn btn-light btn-sm mr-1"  data-toggle="modal" data-target="#container-{{ $object->typeId }}">
                                            Open
                                        </button>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>
    </div>
</div>

<nav class="navbar fixed-bottom navbar-light bg-white" style="border-top: 1px solid rgba(0, 0, 0, 0.125);">
    <a class="navbar-brand d-none d-lg-block" href="{{ url('/') }}" style="position: absolute;">
        {{ config('app.name', 'Laravel') }}
    </a>
    <div class="container d-flex justify-content-center">
        <div class="">
            <button type="button"
                    class="js-pick-up btn btn-light btn-sm"
                    style="width: 6rem;"
            >
                Pick Up
            </button>
            <button type="button"
                    class="js-drop btn btn-light btn-sm"
                    style="width: 6rem;"
            >
                Drop
            </button>
            <button type="button"
                    class="js-use btn btn-light btn-sm"
                    style="width: 6rem;"
            >
                Use
            </button>
            <button type="button"
                    class="js-eat btn btn-light btn-sm"
                    style="width: 6rem;"
            >
                Eat
            </button>
            <button type="button"
                    class="btn btn-light btn-sm"
                    style="width: 6rem;"
                    data-toggle="modal"
                    data-target="#menu-make"
            >
                Make
            </button>
        </div>
    </div>
</nav>

@foreach($containers as $container)
    <div class="modal fade" id="container-{{ $container->typeId }}" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form action="/{{ $gameId }}/transfer/{{ $container->typeId }}" method="POST">
                    {{ csrf_field() }}
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">{{ $container->label }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <ul class="list-group list-group-flush" style="border-top: 1px solid rgba(0, 0, 0, 0.125);">
                        @foreach($container->contents as $object)
                            <div
                               class="item list-group-item d-flex justify-content-between align-items-center js-item"
                               data-available-quantity="{{ $object->quantity }}"
                               data-selected-quantity="0"
                            >
                                <div class="item-label d-flex justify-content-start align-items-center">
                                    {{ $object->label }}
                                    @if(!$object->hasAllPortions)
                                        <div class="progress">
                                            <div class="progress-bar"
                                                 style="width: {{ $object->remainingPortionsPercentage }}%;"
                                            ></div>
                                        </div>
                                    @endif
                                    @if($object->quantity > 1)
                                        <span class="badge badge-light">
                                            {{ $object->quantity }}
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    <span class="badge badge-light js-selected-quantity">
                                        0
                                    </span>
                                    <div class="btn-group js-item-quantities">
                                        <button type="button"
                                                class="btn btn-light btn-sm js-decrement"
                                        >
                                            <i class="fas fa-fw fa-minus"></i>
                                        </button>
                                        <button type="button"
                                                class="btn btn-light btn-sm js-take-all"
                                        >
                                            All
                                        </button>
                                        <button type="button"
                                                class="btn btn-light btn-sm js-increment"
                                        >
                                            <i class="fas fa-fw fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" class="js-quantity-input" name="containerItems[{{ $object->id }}]" value="0">
                            </div>
                        @endforeach
                    </ul>
                    <div class="modal-header" style="border-top: 1px solid rgba(0, 0, 0, 0.125);">
                        <h5 class="modal-title" id="staticBackdropLabel">Player Inventory</h5>
                    </div>

                    <ul class="list-group list-group-flush">
                        @foreach($player->inventory as $object)
                            <div
                                class="item list-group-item d-flex justify-content-between align-items-center js-item"
                                data-available-quantity="{{ $object->quantity }}"
                                data-selected-quantity="0"
                            >
                                <div class="item-label d-flex justify-content-start align-items-center">
                                    {{ $object->label }}
                                    @if(!$object->hasAllPortions)
                                        <div class="progress">
                                            <div class="progress-bar"
                                                 style="width: {{ $object->remainingPortionsPercentage }}%;"
                                            ></div>
                                        </div>
                                    @endif
                                    @if($object->quantity > 1)
                                        <span class="badge badge-light">
                                            {{ $object->quantity }}
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    <span class="badge badge-light js-selected-quantity">
                                        0
                                    </span>
                                    <div class="btn-group js-item-quantities">
                                        <button type="button"
                                                class="btn btn-light btn-sm js-decrement"
                                        >
                                            <i class="fas fa-fw fa-minus"></i>
                                        </button>
                                        <button type="button"
                                                class="btn btn-light btn-sm js-take-all"
                                        >
                                            All
                                        </button>
                                        <button type="button"
                                                class="btn btn-light btn-sm js-increment"
                                        >
                                            <i class="fas fa-fw fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" class="js-quantity-input" name="inventoryItems[{{ $object->id }}]" value="0">
                            </div>
                        @endforeach
                    </ul>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-light">Transfer</button>
                        <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<div class="modal fade" id="menu-make" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="/{{ $gameId }}/make" method="POST">
                {{ csrf_field() }}
                <div class="modal-header">
                    <h5 class="modal-title">Make</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <ul class="list-group list-group-flush">
                    @foreach($player->inventory as $object)
                        <div
                            class="item list-group-item d-flex justify-content-between align-items-center js-item"
                            data-available-quantity="{{ $object->quantity }}"
                            data-selected-quantity="0"
                            data-total-portions="{{ $object->totalPortions }}"
                            data-available-portions="{{ $object->remainingPortions }}"
                            data-selected-portions="0"
                        >
                            <div class="item-label d-flex justify-content-start align-items-center">
                                {{ $object->label }}
                                @if(!$object->hasAllPortions)
                                    <div class="progress">
                                        <div class="progress-bar"
                                             style="width: {{ $object->remainingPortionsPercentage }}%;"
                                        ></div>
                                    </div>
                                @endif
                                @if($object->quantity > 1)
                                    <span class="badge badge-light">
                                        {{ $object->quantity }}
                                    </span>
                                @endif
                            </div>
                            <div class="item-controls d-flex justify-content-start align-items-center">
                                @if($object->isMultiPortionItem)
                                    <div class="progress" style="margin-right: 0.6rem;">
                                        <div class="progress-bar bg-warning js-selected-portions"
                                             style="width: 0;"
                                        ></div>
                                        <div class="progress-bar js-unselected-portions"
                                             style="width: {{ $object->remainingPortionsPercentage }}%;"
                                        ></div>
                                    </div>
                                    <div class="btn-group js-item-portions" style="margin-right: 1rem;">
                                        <button type="button"
                                                class="btn btn-light btn-sm js-portion-decrement"
                                        >
                                            <i class="fas fa-fw fa-minus"></i>
                                        </button>
                                        <button type="button"
                                                class="btn btn-light btn-sm js-portion-increment"
                                        >
                                            <i class="fas fa-fw fa-plus"></i>
                                        </button>
                                    </div>
                                @endif
                                <span class="badge badge-light js-selected-quantity"
                                      style="margin-right: 0.6rem;"
                                >
                                    0
                                </span>
                                <div class="btn-group js-item-quantities">
                                    <button type="button"
                                            class="btn btn-light btn-sm js-decrement"
                                    >
                                        <i class="fas fa-fw fa-minus"></i>
                                    </button>
                                    <button type="button"
                                            class="btn btn-light btn-sm js-take-all"
                                    >
                                        All
                                    </button>
                                    <button type="button"
                                            class="btn btn-light btn-sm js-increment"
                                    >
                                        <i class="fas fa-fw fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" class="js-quantity-input" name="itemQuantities[{{ $object->id }}]" value="0">
                            <input type="hidden" class="js-portions-input" name="itemPortions[{{ $object->id }}]" value="0">
                        </div>
                    @endforeach
                </ul>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-light">Make</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form method="POST"
      id="js-action"
      data-game-id="{{ $gameId }}"
      data-current-location-id="{{ $location->id }}"
>
    {{ csrf_field() }}
</form>

@endsection
