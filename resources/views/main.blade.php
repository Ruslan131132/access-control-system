@extends('layouts.crm')

@section('styles')
    <style>
        /*  СТИЛИ ДЛЯ КАРТОЧКИ ЛОГОВ  */
        .logs-list-group li h6 span {
            font-weight: 600;
        }
    </style>
@endsection

@section('content')
    <div style="max-width: 1000px; margin: 40px 0 0">
        <canvas id="mainChart" width="400" height="200"></canvas>
    </div>
    <h2 class="h2 text-center my-5">Журнал событий</h2>
    <div class="d-flex align-items-top " style="max-width: 600px; gap: 40px">
        <canvas id="mainPieChart" width="400" height="400"></canvas>
        <div class="col-md-5 col-lg-4 order-md-last" style="min-width: 400px">
            <h4 class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-primary">События</span>
                <span class="badge bg-primary rounded-pill">{{$logs->count()}}</span>
            </h4>
            <ul class="list-group mb-3 logs-list-group">
                @foreach($logs->take(10) as $log)
                    @php
                        $color = match ($log->type) {
                            1 => 'rgba(23,246,77,0.2)',
                            2 => 'rgba(255, 206, 86, 0.2)',
                            3 => 'rgb(224,54,54, 0.2)',
                            default => null,
                        };
                    @endphp
                    <li class="list-group-item d-flex justify-content-between lh-sm" @if($color) style="background-color: {{$color}}" @endif>
                        <div>
                            <h6 class="my-0">{{ $log->text }}</h6>
                            @if($log->source !== 'app')
                                <small class="text-body-secondary">источник: {{ $log->source }}</small>
                            @endif

                            @if($log->user_agent || $log->ip)
                                <small class="text-body-secondary">{{!empty($log->ip) && !empty($log->user_agent) ? ': ' : ''}}</small>
                            @endif
                        </div>
                        <span class="text-body-secondary">{{\Illuminate\Support\Carbon::parse($log->created_at)->format('H:i')}}</span>
                    </li>
                @endforeach
                @if(!$logs->isEmpty())
                    <li class="list-group-item d-flex justify-content-between">
                        <a href="{{route('logs')}}" class="ms-auto text-primary">Посмотреть все -></a>
                    </li>
                @endif

            </ul>

        </div>
    </div>

    <div class="chart-container" style="max-width: 800px">
        <canvas id="employeeVisitsChart"></canvas>
    </div>
@endsection

@section('js')
    <script src="/js/libs/Chart.min.js"></script>
    <script>
        var ctx = document.getElementById('mainChart').getContext('2d');
        let chartActivationsData = {!! json_encode($qrActivations) !!};
        let chartGenerationsData = {!! json_encode($qrGenerations) !!};
        var mainChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: Object.keys(chartActivationsData),
                datasets: [
                    {
                        label: 'Запрос на генерацию QR-кода',
                        data: Object.values(chartGenerationsData),
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Вход в систему по QR-коду',
                        data: Object.values(chartActivationsData),
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }],
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });



        var ctx = document.getElementById('mainPieChart').getContext('2d');
        let chartData = {!! json_encode($logs->toArray()) !!};
        var mainPieChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Ошибки', 'Внимание', 'Обычные'],
                datasets: [{
                    label: 'Цвета',
                    data: [
                        chartData.filter(item => item.type == 2).length,
                        chartData.filter(item => item.type == 3).length,
                        chartData.filter(item => item.type == 1).length
                    ],
                    backgroundColor: [
                        'rgba(255, 206, 86, 0.2)',
                        'rgb(224,54,54, 0.2)',
                        'rgba(23,246,77,0.2)',
                    ],
                    borderColor: [
                        'rgba(255, 206, 86)',
                        'rgb(224,54,54)',
                        'rgba(23,246,77)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw;
                            }
                        }
                    }
                }
            }
        });
    </script>
@endsection
