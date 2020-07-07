@extends(backpack_view('blank'))

@php
@endphp

@section('content')
    <div id="app"></div>
@endsection

@section('after_scripts')
    <script src="{{ (env('APP_ENV') === 'local') ? mix('js/app.js') : asset('js/app.js') }}"></script>
@endsection
