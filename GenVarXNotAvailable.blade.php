@php
    include resource_path() . '/views/system/config.blade.php';
    $organism = $info['organism'];
@endphp


@extends('system.header')


@section('content')
    <div class="title1">
        <h2>Genomic Variations Explorer</h2>
    </div>
    <br />

    <br />
    <p>Genomic Variations Explorer is not available for this organism.</p>
    <br />
    <br />
    <br />
    <br />
@endsection


@section('javascript')
    <script type="text/javascript"></script>
@endsection
