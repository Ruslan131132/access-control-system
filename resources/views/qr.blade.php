@extends('layouts.layout')

@section('css')
@endsection

@section('content')
    <img id="photo" src="{{$qr->path ?? false ? config('app.url') . $qr->path :  null}}" width="600px" height="600px" style="{{$qr ? 'display: block;' : 'display: none;' }}margin: auto;">
    <h1 id="no-photo" style="{{$qr ? 'display: none;' : 'display: block;' }}">Зайдите в приложение и QR-код появится</h1>
@endsection

@section('js')
    <script src="/js/libs/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            function fetchLatestPhoto() {
                $.ajax({
                    url: '{{route('qr.show')}}',
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    },
                    success: function(result) {
                        if (result.data && typeof result.data.path != 'undefined') {
                            $('#photo').attr('src', "{{config('app.url')}}" + result.data.path).show();
                            $('#no-photo').hide();
                        } else {
                            $('#photo').hide();
                            $('#no-photo').show();
                        }
                    }
                });
            }

            // Fetch the latest photo every 5 seconds
            setInterval(fetchLatestPhoto, 5000);

            // Initial fetch
            fetchLatestPhoto();
        });
    </script>
@endsection
