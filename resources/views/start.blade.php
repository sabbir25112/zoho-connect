@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="col-sm-offset-2 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Home
                </div>

                <div class="panel-body">
                    <div class="col-md-8-offset-2">
                        <a
                            href="{{ route('sync-projects') }}"
                            class="btn btn-primary"
                        >Sync Projects, Tasklist & Task </a>

                        <a class="btn btn-primary"
                           data-toggle="collapse"
                           data-target="#timesheet_form"
                           aria-expanded="false"
                           aria-controls="timesheet_form"
                        >Sync TimeSheet</a>
                    </div>

                    <div id="timesheet_form" class="collapse">
                        @php
                            $start_date = Carbon\Carbon::today()->subDays(40)->format('m-d-Y');
                            $end_date   = Carbon\Carbon::today()->format('m-d-Y');
                        @endphp
                        <form action="{{ route('sync-timesheet') }}" method="POST">
                            @csrf
                            <div class="col-md-4" style="margin-top: 3%;">
                                <div class="col">
                                    <input type="text" class="form-control" name="daterange"
                                           value="{{ $start_date }} - {{ $end_date }}"/>
                                </div>
                            </div>

                            <div class="col-md-4" style="margin-top: 3%;">
                                <div class="col">
                                    <button type="submit" class="btn btn-success">Submit</button>
                                </div>
                            </div>
                            <input type="hidden" name="start_date" id="start_date" value="{{ $start_date }}">
                            <input type="hidden" name="end_date" id="end_date" value="{{ $end_date }}">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('javascript')
    <script>
        $(function () {
            $('input[name="daterange"]').daterangepicker({
                opens: 'left'
            }, function (start, end, label) {
                $('#start_date').val(start.format('MM-DD-YYYY'))
                $('#end_date').val(end.format('MM-DD-YYYY'))
                console.log("A new date selection was made: " + start.format('MM-DD-YYYY') + ' to ' + end.format('MM-DD-YYYY'));
            });
        });
    </script>
@endpush
