<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CursosController extends Controller
{
    public function index()
    {
        // Variables
        $actual = null;
        $anterior = null;

        // Todas las filas de la base de datos
        $filas = DB::select('select * from cursos limit 4');
        
        // Diccionario donde se guardarán todos los cursos
        $cursos = [];

        // Guardamos todos los cursos en el diccionario
        foreach($filas as $fila)
        {
            // Reset variables
            $lun1 = null;
            $lun2 = null;
            $mar1 = null;
            $mar2 = null;
            $mie1 = null;
            $mie2 = null;
            $jue1 = null;
            $jue2 = null;
            $vie1 = null;
            $vie2 = null;
            $sab1 = null;
            $sab2 = null;
            $dom1 = null;
            $dom2 = null;

            // Guardando el NRC actual
            $actual = $fila->Nrc;

            // Extrayendo las horas en que se ve cada curso
            if($fila->Lunes)
            {
                // Recibo el texto de la celda que contiene el lapso de tiempo en que se da la clase
                $texto = $fila->Lunes;

                // Todas las horas tienen el mismo formato (HH:MM-HH:MM). Separamos con el guión.
                // Tomamos los primero dos números de cada parte.
                $partes = explode('-', $texto);
                $lun1 = substr($partes[0], 0, 2);
                $lun2 = substr($partes[1], 0, 2);

            }
            elseif($fila->Martes)
            {
                // Recibo el texto de la celda que contiene el lapso de tiempo en que se da la clase
                $texto = $fila->Martes;

                // Todas las horas tienen el mismo formato (HH:MM-HH:MM). Separamos con el guión.
                // Tomamos los primero dos números de cada parte.
                $partes = explode('-', $texto);
                $mar1 = substr($partes[0], 0, 2);
                $mar2 = substr($partes[1], 0, 2);
            }
            elseif($fila->Miercoles)
            {
                // Recibo el texto de la celda que contiene el lapso de tiempo en que se da la clase
                $texto = $fila->Miercoles;

                // Todas las horas tienen el mismo formato (HH:MM-HH:MM). Separamos con el guión.
                // Tomamos los primero dos números de cada parte.
                $partes = explode('-', $texto);
                $mie1 = substr($partes[0], 0, 2);
                $mie2 = substr($partes[1], 0, 2);
            }
            elseif($fila->Jueves)
            {
                // Recibo el texto de la celda que contiene el lapso de tiempo en que se da la clase
                $texto = $fila->Jueves;

                // Todas las horas tienen el mismo formato (HH:MM-HH:MM). Separamos con el guión.
                // Tomamos los primero dos números de cada parte.
                $partes = explode('-', $texto);
                $jue1 = substr($partes[0], 0, 2);
                $jue2 = substr($partes[1], 0, 2);
            }
            elseif($fila->Viernes)
            {
                // Recibo el texto de la celda que contiene el lapso de tiempo en que se da la clase
                $texto = $fila->Viernes;

                // Todas las horas tienen el mismo formato (HH:MM-HH:MM). Separamos con el guión.
                // Tomamos los primero dos números de cada parte.
                $partes = explode('-', $texto);
                $vie1 = substr($partes[0], 0, 2);
                $vie2 = substr($partes[1], 0, 2);
            }
            elseif($fila->Sabado)
            {
                // Recibo el texto de la celda que contiene el lapso de tiempo en que se da la clase
                $texto = $fila->Sabado;

                // Todas las horas tienen el mismo formato (HH:MM-HH:MM). Separamos con el guión.
                // Tomamos los primero dos números de cada parte.
                $partes = explode('-', $texto);
                $sab1 = substr($partes[0], 0, 2);
                $sab2 = substr($partes[1], 0, 2);
            }
            elseif($fila->Domingo)
            {
                // Recibo el texto de la celda que contiene el lapso de tiempo en que se da la clase
                $texto = $fila->Domingo;

                // Todas las horas tienen el mismo formato (HH:MM-HH:MM). Separamos con el guión.
                // Tomamos los primero dos números de cada parte.
                $partes = explode('-', $texto);
                $dom1 = substr($partes[0], 0, 2);
                $dom2 = substr($partes[1], 0, 2);
            }

            // Verificamos si el NRC actual es igual al anterior
            if($actual != $anterior)
            {
                
                // Llenamos la información del NRC
                $cursos[$fila->Nrc] = [
                    'materia' => $fila->Materia,
                    'curso' => $fila->Curso,
                    'seccion' => $fila->Seccion,
                    'creditos' => $fila->Creditos,
                    'nombre' => $fila->Nombre_asignatura,
                    'capacidad' => $fila->Capacidad,
                    'disponibles' => $fila->Disponibles,
                    'ocupados' => $fila->Ocupados,
                    'codigo' => $fila->Codigo_docente,
                    'docente' => $fila->Docente,
                    'lunes' => [$lun1, $lun2],
                    'martes' => [$mar1, $mar2],
                    'miercoles' => [$mie1, $mie2],
                    'jueves' => [$jue1, $jue2],
                    'viernes' => [$vie1, $vie2],
                    'sabado' => [$sab1, $sab2],
                    'domingo' => [$dom1, $dom2],
                    'edificio' => $fila->Edf,
                    'salon' => $fila->Salon,
                    'inicio' => $fila->Fecha_inicio,
                    'tipo' => $fila->Tipo,
                    'horas' => $fila->Hrs_sem
                ];

            }

            $anterior = $actual;

        }

        dd($cursos);
        return view('index');
    }
}