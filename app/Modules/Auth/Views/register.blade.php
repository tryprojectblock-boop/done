@extends('layouts.guest')

@section('content')
    <div
        id="signup-app"
        data-options="{{ json_encode($options ?? []) }}"
    ></div>
@endsection
