@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">

                    <div class="card-body">

                        <form action="/new-game" method="POST" class="action-button">
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-primary btn-block">
                                Start New Game
                            </button>
                        </form>

                    </div>

                </div>

            </div>
        </div>
    </div>
@endsection
