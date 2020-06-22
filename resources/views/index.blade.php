@extends('layouts.app')

@section('contenido')

<div class="container">
    <div class="row">
        <div class="col-6">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre del curso</th>
                        <th>Créditos</th>
                        <th>Agregar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cursos as $nombre => $curso)
                    <tr>
                        <th>{{ $nombre }}</th>
                        <td>{{ $curso['creditos'] }}</td>
                        <td>Agregar</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-6">
        </div>
    </div>
</div>

@endsection