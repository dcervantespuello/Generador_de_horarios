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

            // 5. Guardamos la información en el array de cursos.
            $cursos[$nombre] = [
                'materia' => $fila->Materia,
                'curso' => $fila->Curso,
                'campus' => $fila->Campus,
                'fecha_inicio' => $fila->Fecha_inicio,
                'creditos' => $fila->Creditos
            ];

            // 6. Obtenemos todos los NRC del curso
            $lista_nrc = DB::select('select distinct Nrc from cursos where Nombre_asignatura = "CREATIVIDAD Y EMPRENDIMIENTO"');
            
            // 7. Guardamos los NRC en el array de cursos
            foreach ($lista_nrc as $nrc) {

                $nrc = $nrc->Nrc;
                
                // 8. Obtenemos la primera fila de cada NRC.
                $info = DB::select('select * from cursos where Nombre_asignatura = "CREATIVIDAD Y EMPRENDIMIENTO" and Nrc = "'.$nrc.'" limit 1');
                $info = $info[0];
                
                // 9. Guardamos la información en el array de cursos.
                $cursos[$nombre][$nrc] = [
                    'seccion' => $info->Seccion,
                    'capacidad' => $info->Capacidad,
                    'disponibles' => $info->Disponibles,
                    'ocupados' => $info->Ocupados,
                    'codigo_docente' => $info->Codigo_docente,
                    'docente' => $info->Docente,
                    'tipo' => $info->Tipo
                ];

            }
            
        }

        dd($cursos['FUNDAMENTOS DE ADMINISTRACION'][1100]);

        return view('index');
    }
}