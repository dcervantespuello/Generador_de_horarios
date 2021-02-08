@extends('layouts.app')

@section('contenido')
<!-- <div class="text-center">
@foreach($sem as $key => $val)
    <h1>Semana {{ $key + 1 }}</h1>
    @foreach($val[0] as $key2 => $val2)
        <p>{{ $val2 }}</p>
    @endforeach
    <p>NRC1: {{ $val[1] }}</p>
    <p>ALEATORIO1: {{ $val[2] }}</p>
@endforeach
</div> -->

<div class="container-fluid">
    <div class="row">
        <div class="col">
            <h1>¡Felicitaciones!</h1>
            <h3>Su horario fue generado con éxito</h3>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <table class="table table-bordered table-sm text-white" id="generado">
                <thead>
                    <tr style="background-color: #2196f399;">
                        <th>Hora</th>
                        <th>Lunes</th>
                        <th>Martes</th>
                        <th>Miercoles</th>
                        <th>Jueves</th>
                        <th>Viernes</th>
                        <th>Sábado</th>
                        <th>Domingo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($filas as $fila => $dias)
                    <tr>
                        <td style="white-space:nowrap;">{{ $fila }}:00 - {{ $fila }}:50</td>
                        @foreach ($dias as $hora => $nrc)

                            @if(empty($nrc))
                            <td></td>
                            @else
                            <td>{{ $definitivos[$nrc] }} ({{ $nrc }})</td>
                            @endif

                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-3">
            <a class="btn btn-primary btn-lg btn-block mb-4" href="{{ URL::previous() }}" role="button">Ir al inicio</a>
        </div>
    </div>
</div>

@endsection