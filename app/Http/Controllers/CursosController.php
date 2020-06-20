<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CursosController extends Controller
{
    public function index()
    {

        // 1. Capturamos toda la información de los cursos
        $tabla = DB::select('select * from cursos');

        // 2. Inicializar array donde se va a organizar la información
        $cursos = [];

        // 3. Capturamos todos los nombres de la base de datos sin repetir.
        $nombres = DB::select('select distinct Nombre_asignatura from cursos');
        
        foreach ($nombres as $nombre) {

            $nombre = $nombre->Nombre_asignatura;
            
            // 4. Obtenemos la primera fila de cada curso.
            $fila = DB::select('select * from cursos where Nombre_asignatura = "'.$nombre.'" limit 1');
            $fila = $fila[0];

            // 5. Guardamos la información básica en el array de cursos.
            $cursos[$nombre] = [
                'materia' => $fila->Materia,
                'curso' => $fila->Curso,
                'campus' => $fila->Campus,
                'fecha_inicio' => $fila->Fecha_inicio,
                'creditos' => $fila->Creditos
            ];

            // 6. Obtenemos todos los NRC del curso
            $lista_nrc = DB::select('select distinct Nrc from cursos where Nombre_asignatura = "'.$nombre.'"');

            // 7. Guardamos los NRC en el array de cursos
            foreach ($lista_nrc as $nrc) {

                $nrc = $nrc->Nrc;
                
                // 8. Obtenemos la información de cada NRC
                $info = DB::select('select * from cursos where Nombre_asignatura = "'.$nombre.'" and Nrc = "'.$nrc.'"');
                

                $cursos[$nombre][$nrc] = [];

            }

        }

        dd($cursos);

        return view('index');
    }
}