@extends('layouts.app')

@section('contenido')

<div class="container-fluid">
    <div class="row">
        <div class="col">
            <h1>HORARIOS UTB</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <div class="form-group">
                <input type="text" class="form-control pull-right" id="buscador"
                    placeholder="Escribe el nombre del curso que quieras agregar a tu horario.">
            </div>
            <table class="table table-hover<" id="cursos">
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
                        <td><input class="btn btn-success btn-agregar" type="button" value="Agregar"
                                onclick='agregar("{{ $nombre }}", "{{ $creditos }}")'></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-6">
            <h3>CURSOS SELECCIONADOS</h3>
            <div class="row">
                <div class="col mb-2" id='errores'>
                    <ul id='lista' class='my-auto'>
                    </ul>
                </div>
            </div>
            <form action="{{ route('hill_climbing') }}" method="post">
                @csrf
                <table class="table table-hover" id='seleccionados'>
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
                    <div class="col">
                        <button type="submit" class="btn btn-primary btn-block btn-lg" id="enviar">Generar mi
                            Horario</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection