@extends('layouts.crm')

@section('styles')
    <style>
        .log-text span {
            font-weight: 600;
        }
    </style>
@endsection

@section('content')
    <form class="form-inline my-4 d-flex align-items-center" method="GET" action="{{route('logs')}}">
        <div class="form-group mb-2">
            <label for="created_from" class="sr-only">ОТ</label>
            <input type="date" class="form-control" name="created_from" value="{{request()->created_from ?? \Carbon\Carbon::now()->format('Y-m-d')}}">
        </div>
        <div class="form-group mx-sm-3 mb-2">
            <label for="created_to" class="sr-only">ДО</label>
            <input type="date" class="form-control" name="created_to" value="{{request()->created_to ?? \Carbon\Carbon::now()->format('Y-m-d')}}">
        </div>
        <div class="form-group mx-sm-3 mb-2">
            <label for="created_to" class="sr-only">ТЕКСТ СОДЕРЖИТ</label>
            <input type="text" class="form-control" name="text" value="{{request()->text ?? null}}">
        </div>
        <button type="submit" class="btn btn-primary mt-3" style="max-height: 40px">Подтвердить</button>
    </form>
    <table class="table">
        <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Дата</th>
            <th scope="col">Сообщение</th>
            <th scope="col">Данные об источнике</th>
        </tr>
        </thead>
        <tbody>
        @forelse($logs as $log)
            @php
                $color = match ($log->type) {
                    1 => 'rgba(23,246,77,0.2)',
                    2 => 'rgba(255, 206, 86, 0.2)',
                    3 => 'rgb(224,54,54, 0.2)',
                    default => null,
                };
                $background = $color ? 'background-color: ' . $color . ';' : null;
            @endphp
            <tr>
                <th scope="row" style="{{$background}}">{{$log->id}}</th>
                <td class="text-nowrap" style="{{$background}}">{{$log->created_at}}</td>
                <td class="log-text" style="{{$background}}">
                    {{ $log->text }}
                    @if($log->source)
                        <div class="text-secondary small">{{ $log->source }}</div>
                    @endif
                </td>
                <td style="{{$background}}">{{$log->ip}}{{!empty($log->ip) && !empty($log->user_agent) ? ': ' : ''}}{{$log->user_agent}}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="table-active text-center">Данные не найдены</td>
            </tr>

        @endforelse
        </tbody>
    </table>
@endsection

@section('js')

@endsection
