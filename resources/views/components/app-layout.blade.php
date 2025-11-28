@extends('layouts.app')

@section('content')
    @isset($header)
        <div class="mb-4">
            {{ $header }}
        </div>
    @endisset

    {{ $slot }}
@endsection