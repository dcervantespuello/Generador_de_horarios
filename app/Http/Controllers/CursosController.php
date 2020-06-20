<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CursosController extends Controller
{
    public function index()
    {

        // 1. Capturamos toda la información de los cursos
        $filas = DB::select('select * from cursos');

        // 2. Inicializar array donde se va a organizar la información
        $cursos = [];

        // 3. Capturamos todos los nombres de la base de datos sin repetir.
        $nombres = DB::select('select distinct Nombre_asignatura from cursos');
        
        foreach ($nombres as $valor) {
            
            // 4. Obtenemos la primera fila de cada nombre de curso.
            $fila = DB::select('select * from cursos where Nombre_asignatura = "'.$valor->Nombre_asignatura.'" limit 1');
            $fila = $fila[0];

            // 5. Guardamos la información básica en el array de cursos.
            $cursos[$valor->Nombre_asignatura] = [
                'materia' => $fila->Materia,
                'curso' => $fila->Curso,
                'campus' => $fila->Campus,
                'fecha_inicio' => $fila->Fecha_inicio,
                'creditos' => $fila->Creditos
            ];

        }

        

        return view('index');
    }
}