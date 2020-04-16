@extends('layouts.app')

@section('content')
<div class="fixed-top fixed-alert-container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="js-alert alert alert-secondary" style="display: none;">
                ⚠ <span class="js-alert-message"></span>
                <button type="button" class="close" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @if(session("success"))
                <div class="alert alert-secondary alert-dismissible fade show">
                    ✔ {{ session("success") }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if(session("info"))
                <div class="alert alert-secondary alert-dismissible fade show">
                    ⚠ {{ session("info") }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if(session("infoRaw"))
                <div class="alert alert-secondary alert-dismissible fade show">
                    ⚠ {!!  session("infoRaw") !!}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if(session("achievements"))
                @foreach (session("achievements") as $achievement)
                    <div class="alert alert-secondary alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <p style="font-size: 0.75rem; opacity: 0.75; margin-bottom: 0.4rem;">Achievement Unlocked</p>
                        <strong>{{ $achievement['title'] }}</strong>
                        <p class="mb-0">{{ $achievement['body'] }}</p>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>

<div class="container" style="padding-bottom: 8rem;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if(session("message"))
                <div class="alert alert-secondary">
                    {{ session("message") }}
                </div>
            @endif
            @if(session("messageRaw"))
                <div class="alert alert-secondary">
                    {!! session("messageRaw") !!}
                </div>
            @endif
            <div class="card">
                <div class="card-header">
                    {{ $location->title }}
                </div>

                <div class="card-body">

                    {{--
                    <p>{{ $location->description }}</p>
                    --}}

                    <div class="d-flex justify-content-center flex-wrap">
                        @foreach($location->egresses as $egress)
                            <form action="/{{ $gameId }}/go/{{ $egress->id }}" method="POST" class="action-button" style="margin: 0 0.2rem;">
                                {{ csrf_field() }}
                                <button type="submit"
                                        class="btn btn-light btn-block"
                                        style="margin-bottom: 0.4rem;"
                                        {{ $player->isDead ? "disabled" : "" }}
                                >
                                    Go to {{ $egress->label }}
                                </button>
                            </form>
                        @endforeach
                    </div>

                </div>

                <ul class="list-group list-group-flush" style="border-top: 1px solid rgba(0, 0, 0, 0.125);">
                    @foreach($location->objects as $object)
                        <li class="item list-group-item d-flex justify-content-between align-items-center js-inventory-item"
                            data-id="{{ $object->id }}"
                            data-type-id="{{ $object->typeId }}"
                            data-label="{{ $object->label }}"
                            data-is-container="{{ $object->isContainer }}"
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
                        </li>
                    @endforeach
                </ul>
            </div>

            @if($player->inventory or $player->xp or $player->isDead)
                <div class="card" style="margin-top: 2rem;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>Player</div>
                        {{--
                        @if($player->xp)
                            <span class="badge badge-light">{{ $player->xp }} XP</span>
                        @endif
                        --}}
                    </div>

                    @if($player->isDead)
                        <div class="card-body">
                            <form action="/new-game" method="POST" class="action-button">
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-light btn-block">
                                    You have died. Try again?
                                </button>
                            </form>
                        </div>
                    @endif

                    <ul class="list-group list-group-flush">
                        @foreach($player->inventory as $object)
                            <li class="item list-group-item d-flex justify-content-between align-items-center js-inventory-item"
                                data-id="{{ $object->id }}"
                                data-type-id="{{ $object->typeId }}"
                                data-label="{{ $object->label }}"
                                data-is-container="{{ $object->isContainer }}"
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
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>
    </div>
</div>

<nav class="navbar fixed-bottom navbar-light bg-white" style="border-top: 1px solid rgba(0, 0, 0, 0.125);">
    <a class="navbar-brand d-none d-lg-block"
       href="#"
       style="position: absolute;"
       data-toggle="modal"
       data-target="#menu-game"
    >
        {{ config('app.name') }}
    </a>
    <div class="container">
        <div class="d-flex justify-content-center flex-wrap" style="width: 100%;">
                <button type="button"
                        class="js-pick-up btn btn-light btn-sm"
                        style="width: 6rem; margin: 0 0.1rem 0.2rem;"
                        {{ $player->isDead ? "disabled" : "" }}
                >
                    Pick Up
                </button>
                <button type="button"
                        class="js-drop btn btn-light btn-sm"
                        style="width: 6rem; margin: 0 0.1rem 0.2rem;"
                        {{ $player->isDead ? "disabled" : "" }}
                >
                    Drop
                </button>
                <button type="button"
                        class="js-use btn btn-light btn-sm"
                        style="width: 6rem; margin: 0 0.1rem 0.2rem;"
                        {{ $player->isDead ? "disabled" : "" }}
                >
                    Use
                </button>
                <button type="button"
                        class="js-eat btn btn-light btn-sm"
                        style="width: 6rem; margin: 0 0.1rem 0.2rem;"
                        {{ $player->isDead ? "disabled" : "" }}
                >
                    Eat
                </button>
                <button type="button"
                        class="js-open btn btn-light btn-sm"
                        style="width: 6rem; margin: 0 0.1rem 0.2rem;"
                        {{ $player->isDead ? "disabled" : "" }}
                >
                    Open
                </button>
                <button type="button"
                        class="btn btn-light btn-sm"
                        style="width: 6rem; margin: 0 0.1rem 0.2rem;"
                        data-toggle="modal"
                        data-target="#menu-make"
                        {{ $player->isDead ? "disabled" : "" }}
                >
                    Make
                </button>
        </div>
        <div class="d-flex justify-content-center w-100 d-lg-none">
            <a class="navbar-brand"
               style="font-size: 0.8rem; margin-right: 0;"
               href="#"
               data-toggle="modal"
               data-target="#menu-game"
            >{{ config('app.name') }}</a>
        </div>
    </div>
</nav>

@foreach($containers as $container)
    <div class="modal fade js-open-modal" data-id="{{ $container->id }}" id="container-{{ $container->typeId }}" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
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
                                        <div class="progress-bar bg-selected js-selected-portions"
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

<div class="modal fade" id="menu-telephone" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Telephone</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="number-display"></div>
                <div class="keypad">
                    <div class="keypad-row">
                        <button type="button" class="btn btn-light" data-symbol="1">1</button>
                        <button type="button" class="btn btn-light" data-symbol="2">2</button>
                        <button type="button" class="btn btn-light" data-symbol="3">3</button>
                    </div>
                    <div class="keypad-row">
                        <button type="button" class="btn btn-light" data-symbol="4">4</button>
                        <button type="button" class="btn btn-light" data-symbol="5">5</button>
                        <button type="button" class="btn btn-light" data-symbol="6">6</button>
                    </div>
                    <div class="keypad-row">
                        <button type="button" class="btn btn-light" data-symbol="7">7</button>
                        <button type="button" class="btn btn-light" data-symbol="8">8</button>
                        <button type="button" class="btn btn-light" data-symbol="9">9</button>
                    </div>
                    <div class="keypad-row">
                        <button type="button" class="btn btn-light" data-symbol="0">0</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="call-button btn btn-light">Call</button>
                <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="menu-game" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Menu</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <button type="button" class="btn btn-light btn-block" data-toggle="modal" data-target="#menu-events" data-dismiss="modal">Event Log</button>
                <button type="button" class="btn btn-light btn-block" data-toggle="modal" data-target="#menu-achievements" data-dismiss="modal">Achievements</button>
            </div>
            <div class="modal-body" style="border-top: 1px solid rgba(0, 0, 0, 0.125);">
                <form action="/new-game" method="POST" class="action-button">
                    {{ csrf_field() }}
                    <button type="submit" class="btn btn-light btn-block">Start New Game</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-block" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="menu-events" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Event Log</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @foreach($player->events as $event)
                    <div class="review-event">
                        <div class="review-event-title">{{ $event->location }}</div>
                        {!! $event->message !!}
                    </div>
                @endforeach
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="menu-achievements" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Achievements</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if(count($player->achievements) === 0)
                    You have not yet unlocked any achievements
                @endif
                @foreach($player->achievements as $achievement)
                    <div class="achievement">
                        <div class="achievement-title">{{ $achievement->title }}</div>
                        <div class="achievement-body">{{ $achievement->body }}</div>
                    </div>
                @endforeach
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
            </div>
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
