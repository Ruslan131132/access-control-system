@extends('layouts.crm')

@section('styles')
    <style>
        .log-text span {
            font-weight: 600;
        }
    </style>
@endsection

@section('content')
    @if(session()->has('success'))
        <div class="alert alert-success mt-5 text-center">
            {{ session()->get('success') }}
        </div>
    @endif
    @if($user)
        <form class="row g-3 needs-validation mt-4">
            @csrf
            <div class="col-md-4">
                <label for="name" class="form-label">Имя</label>
                <input type="text" class="form-control" name="name" placeholder="Имя" value="{{ $user->name }}" disabled>
            </div>
            <div class="col-md-4">
                <label for="email" class="form-label">Почта</label>
                <div class="input-group has-validation">
                    <span class="input-group-text" id="inputGroupPrepend">@</span>
                    <input type="text" class="form-control" value="{{ $user->email }}" disabled>
                </div>
            </div>
            <div class="col-md-3">
                <label for="validationCustom01" class="form-label">Роль</label>
                <input type="text" class="form-control" name="role" placeholder="Роль" value="{{ $user->roles->pluck('name')->implode(', ') }}" disabled>
            </div>
        </form>

        <h2 class="h2 text-center my-5">Журнал событий</h2>
        <div class="d-flex align-items-top " style="max-width: 600px; gap: 40px">
            <canvas id="myPieChart" width="400" height="400"></canvas>
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

                                @if($log->user_agent || $log->ip)
                                    <small class="text-body-secondary">{{!empty($log->ip) && !empty($log->user_agent) ? ': ' : ''}}</small>
                                @endif
                            </div>
                            <span class="text-body-secondary">{{\Illuminate\Support\Carbon::parse($log->created_at)->format('H:i')}}</span>
                        </li>
                    @endforeach

                </ul>

            </div>
        </div>
    @else
        <h1 class="text-center mt-5">404. Пользователь не найден</h1>
    @endif
@endsection

@section('js')
    @if($user)
        <script src="/js/libs/Chart.min.js"></script>
        <script>
            var ctx = document.getElementById('myPieChart').getContext('2d');
            let chartData = {!! json_encode($logs->toArray()) !!};
            var myPieChart = new Chart(ctx, {
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
    @endif
@endsection
