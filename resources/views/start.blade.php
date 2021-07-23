@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="col-sm-offset-2 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Home
                </div>

                <div class="panel-body">
                    @if (!Cache::has(\App\Http\Constants::ZOHO_ACCESS_KEY_CACHE))
                        <a
                            href="{{ route('zoho-auth-init') }}"
                            class="btn btn-primary"
                        >Authenticate With Zoho</a>
                    @else
                        <a
                            href="{{ route('sync-projects') }}"
                            class="btn btn-primary"
                        >Sync Projects, Tasklist & Task </a>
                    @endif
                </div>
            </div>
        </div>
    </div>


@endsection
