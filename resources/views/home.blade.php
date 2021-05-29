@extends('layouts.app')

@section('content')



<div class="container-fluid">
    <!-- <div class="row justify-content-center mt-3">
        <div class="col-1 mr-5">
            <img src="{{ asset('img/logo.png') }}" alt="logo_horarios_utb" style="width: 150px;">
        </div>
        <div class="col-4">
            <h1 class="mt-4" style="font-size: 70px;">HORARIOS UTB</h1>
        </div>
    </div> -->
    <div class="row mt-3">
        <div class="col-lg-7">
            <!-- <div class="form-group">
                <input type="text" class="form-control pull-right" id="buscador"
                    placeholder="Escribe el nombre del curso que quieras agregar a tu horario.">
            </div> -->
            <table class="table" width="100%" id="cursos">
                <thead>
                    <tr>
                        <th>Nombre del curso</th>
                        <th>Créditos</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cursos as $nombre => $curso)

                    <!-- Acortando textos -->
                    @php
                    $creditos = $curso['creditos'];
                    @endphp

                    <tr>
                        <td>{{ $nombre }}</td>
                        <td>{{ $curso['creditos'] }}</td>
                        <td><input class="btn btn-success btn-agregar" type="button" value="Agregar" onclick='agregar("{{ $nombre }}", "{{ $creditos }}")'></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-lg-5 mt-4 mt-lg-0">
            <h3 id="t_seleccion">CURSOS SELECCIONADOS</h3>
            @if (session('error'))
            <div class="alert alert-danger">
                <strong>
                    <p style="text-align:center;" class='my-auto'>{{ session('error') }}</p>
                </strong>
            </div>
            @endif
            <div class="alert alert-danger" id="errores">
                <strong>
                    <p id='lista' style="text-align:center;" class='my-auto'></p>
                </strong>
            </div>
            <form action="{{ route($meta) }}" method="post">
                @csrf
                <table class="table" id='seleccionados'>
                    <thead>
                        <tr>
                            <th>Nombre de curso</th>
                            <th>Créditos</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot id='pie'>
                        <tr>
                            <th>Total Créditos</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
                <p id="fila_cero">¡No hay cursos seleccionados!</p>
                <div class="row justify-content-center">
                    <div class="col mb-3">
                        <button type="submit" class="btn btn-primary btn-block btn-lg" id="enviar">Generar mi
                            Horario</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection