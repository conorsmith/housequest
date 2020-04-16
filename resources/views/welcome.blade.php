@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">

                    <div class="card-body">

                        <h1 class="display-4" style="text-align: center; line-height: 0.9; margin-bottom: 2rem;">
                            House<wbr>Quest
                        </h1>

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
