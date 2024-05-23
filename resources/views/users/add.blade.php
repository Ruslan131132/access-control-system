@extends('layouts.crm')

@section('styles')
    <style>
        .log-text span {
            font-weight: 600;
        }
    </style>
@endsection

@section('content')
    <h1 class="mt-3">Добавление пользователя</h1>
    <form class="row g-3 needs-validation mt-4" action="{{route('users.create')}}" method="POST">
         @csrf
        <div class="col-md-4">
            <label for="name" class="form-label">Имя</label>
            <input type="text" class="form-control @if($errors->has('name')) is-invalid @endif" name="name" placeholder="Имя" value="{{ old('name') }}">
            @if($errors->has('name'))
                <div class="invalid-feedback">
                    {{$errors->first('name')}}
                </div>
            @endif
        </div>
        <div class="col-md-4">
            <label for="email" class="form-label">Почта</label>
            <div class="input-group has-validation">
                <span class="input-group-text" id="inputGroupPrepend">@</span>
                <input type="text" class="form-control @if($errors->has('email')) is-invalid @endif" name="email" aria-describedby="inputGroupPrepend" value="{{ old('email') }}">
                @if($errors->has('email'))
                    <div class="invalid-feedback">
                        {{$errors->first('email')}}
                    </div>
                @endif
            </div>
        </div>
        <div class="col-md-3">
            <label for="validationCustom01" class="form-label">Роль</label>
            <select class="form-select @if($errors->has('role')) is-invalid @endif" aria-label="Default select example" name="role">
                <option selected disabled >Роль...</option>
                @foreach($roles as $role)
                    <option @if($role->id == old('role')) selected @endif value="{{$role->id}}">{{$role->ru ?? $role->name}}</option>
                @endforeach
            </select>
            @if($errors->has('role'))
                <div class="invalid-feedback">
                    {{$errors->first('role')}}
                </div>
            @endif
        </div>
        <div class="col-md-4">
            <label for="password" class="form-label">Пароль</label>
            <input type="password" class="form-control @if($errors->has('password') || $errors->has('password_confirmation')) is-invalid @endif" name="password" placeholder="Пароль" value="{{ old('password') }}">
            @if($errors->has('password') || $errors->has('password_confirmation'))
                <div class="invalid-feedback">
                    {{$errors->first('password') ?? $errors->first('password_confirmation')}}
                </div>
            @endif
        </div>
        <div class="col-md-4">
            <label for="password_confirmation" class="form-label">Подтвердите пароль</label>
            <input type="password" class="form-control @if($errors->has('password') || $errors->has('password_confirmation')) is-invalid @endif" name="password_confirmation" placeholder="Введите пароль повторно" value="{{ old('password_confirmation') }}">
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit">Добавить</button>
        </div>
    </form>
@endsection

@section('js')

@endsection
