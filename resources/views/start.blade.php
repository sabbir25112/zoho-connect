@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="col-sm-offset-1 col-sm-10">
            @if (session()->has('error'))
                <div class="alert alert-danger" role="alert">
                    {{ session()->get('error') }}
                </div>
            @endif
            @if (session()->has('success'))
                <div class="alert alert-success" role="alert">
                    {{ session()->get('success') }}
                </div>
            @endif
            <div class="panel panel-default">
                <div class="panel-heading">
                    Home
                </div>

                <div class="panel-body">
                    <div class="col-md-10-offset-1">
                        <a
                            href="{{ route('sync-projects') }}"
                            class="btn btn-primary"
                        > Projects <span class="badge badge-light">{{ $count['projects'] }}</span>
                        </a>

                        <a
                            href="{{ route('sync-users') }}"
                            class="btn btn-primary"
                        > Users <span class="badge badge-light">{{ $count['users'] }}</span>
                        </a>

                        <a href="{{ route('sync-tasklists') }}"
                           class="btn btn-primary"
                        > Tasklist <span class="badge badge-light">{{ $count['tasklists'] }}</span>
                        </a>

                        <a href="{{ route('sync-tasks') }}"
                            class="btn btn-primary"
                        > Task <span class="badge badge-light">{{ $count['tasks'] }}</span>
                        </a>

                        <a href="{{ route('sync-sub-tasks') }}"
                            class="btn btn-primary"
                        > Sub-Task <span class="badge badge-light">{{ $count['subtasks'] }}</span>
                        </a>
                        <a class="btn btn-primary"
                           data-toggle="collapse"
                           data-target="#project_form"
                           aria-expanded="false"
                           aria-controls="project_form"
                        > Bug <span class="badge badge-light">{{ $count['bugs'] }}</span>
                        </a>

                        <a class="btn btn-primary"
                           data-toggle="collapse"
                           data-target="#timesheet_form"
                           aria-expanded="false"
                           aria-controls="timesheet_form"
                        > Sync TimeSheet <span class="badge badge-light">{{ $count['timesheets'] }}</span>
                        </a>
                    </div>
                    <div id="project_form" class="collapse">
                        <form action="{{ route('sync-tasks') }}">
                            <div class="col-md-4" style="margin-top: 3%;">
                                <div class="col">
                                    <select class="form-control" aria-label="Default select example" name="project">
                                        <option disabled selected>Select Project to Sync Task</option>
                                        @foreach($projects as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4" style="margin-top: 3%;">
                                <div class="col">
                                    <button type="submit" class="btn btn-success">Submit</button>
                                </div>
                            </div>
                        </form>
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
