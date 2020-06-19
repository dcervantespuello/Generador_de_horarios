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
        $fila = DB::select('select * from cursos limit 4');
        
        // Diccionario donde se guardarán todos los cursos
        $cursos = [];

        // Guardamos todos los cursos en el diccionario
        for($i = 0; $i < count($fila); $i++)
        {
            // Guardando el NRC actual
            $actual = $fila[$i]->Nrc;

            if($actual != $anterior)
            {
                // Reset variables
                $variables = [];
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
            }

            // Limpiando las horas semanales
            $hrs_sem = $fila[$i]->Hrs_sem;
            $hrs_sem_cadena = substr($hrs_sem, 0, 1);
            $Hrs_sem = intval($hrs_sem_cadena);

            // Extrayendo las horas en que se ve cada curso
            if($fila[$i]->Lunes)
            {
                // Recibo el texto de la celda que contiene el lapso de tiempo en que se da la clase
                $texto = $fila[$i]->Lunes;

                // Todas las horas tienen el mismo formato (HH:MM-HH:MM). Separamos con el guión.
                // Tomamos los primero dos números de cada parte.
                $partes = explode('-', $texto);
                $lun1 = substr($partes[0], 0, 2);
                $lun2 = substr($partes[1], 0, 2);

                // Se agrega a las variables
                $variables[] = 'lunes';
            }
            elseif($fila[$i]->Martes)
            {
                // Recibo el texto de la celda que contiene el lapso de tiempo en que se da la clase
                $texto = $fila[$i]->Martes;

                // Todas las horas tienen el mismo formato (HH:MM-HH:MM). Separamos con el guión.
                // Tomamos los primero dos números de cada parte.
                $partes = explode('-', $texto);
                $mar1 = substr($partes[0], 0, 2);
                $mar2 = substr($partes[1], 0, 2);

                // Se agrega a las variables
                $variables[] = 'martes';
            }
            elseif($fila[$i]->Miercoles)
            {
                // Recibo el texto de la celda que contiene el lapso de tiempo en que se da la clase
                $texto = $fila[$i]->Miercoles;

                // Todas las horas tienen el mismo formato (HH:MM-HH:MM). Separamos con el guión.
                // Tomamos los primero dos números de cada parte.
                $partes = explode('-', $texto);
                $mie1 = substr($partes[0], 0, 2);
                $mie2 = substr($partes[1], 0, 2);

                // Se agrega a las variables
                $variables[] = 'miercoles';
            }
            elseif($fila[$i]->Jueves)
            {
                // Recibo el texto de la celda que contiene el lapso de tiempo en que se da la clase
                $texto = $fila[$i]->Jueves;

                // Todas las horas tienen el mismo formato (HH:MM-HH:MM). Separamos con el guión.
                // Tomamos los primero dos números de cada parte.
                $partes = explode('-', $texto);
                $jue1 = substr($partes[0], 0, 2);
                $jue2 = substr($partes[1], 0, 2);

                // Se agrega a las variables
                $variables[] = 'jueves';
            }
            elseif($fila[$i]->Viernes)
            {
                // Recibo el texto de la celda que contiene el lapso de tiempo en que se da la clase
                $texto = $fila[$i]->Viernes;

                // Todas las horas tienen el mismo formato (HH:MM-HH:MM). Separamos con el guión.
                // Tomamos los primero dos números de cada parte.
                $partes = explode('-', $texto);
                $vie1 = substr($partes[0], 0, 2);
                $vie2 = substr($partes[1], 0, 2);

                // Se agrega a las variables
                $variables[] = 'viernes';
            }
            elseif($fila[$i]->Sabado)
            {
                // Recibo el texto de la celda que contiene el lapso de tiempo en que se da la clase
                $texto = $fila[$i]->Sabado;

                // Todas las horas tienen el mismo formato (HH:MM-HH:MM). Separamos con el guión.
                // Tomamos los primero dos números de cada parte.
                $partes = explode('-', $texto);
                $sab1 = substr($partes[0], 0, 2);
                $sab2 = substr($partes[1], 0, 2);

                // Se agrega a las variables
                $variables[] = 'sabado';
            }
            elseif($fila[$i]->Domingo)
            {
                // Recibo el texto de la celda que contiene el lapso de tiempo en que se da la clase
                $texto = $fila[$i]->Domingo;

                // Todas las horas tienen el mismo formato (HH:MM-HH:MM). Separamos con el guión.
                // Tomamos los primero dos números de cada parte.
                $partes = explode('-', $texto);
                $dom1 = substr($partes[0], 0, 2);
                $dom2 = substr($partes[1], 0, 2);

                // Se agrega a las variables
                $variables[] = 'domingo';
            }


            // Verificamos si el NRC actual es igual al anterior
            if($actual != $anterior)
            {
                
                // Llenamos la información del NRC
                $cursos[$i] = [
                    'nrc' => $fila[$i]->Nrc,
                    'materia' => $fila[$i]->Materia,
                    'curso' => $fila[$i]->Curso,
                    'seccion' => $fila[$i]->Seccion,
                    'creditos' => $fila[$i]->Creditos,
                    'nombre' => $fila[$i]->Nombre_asignatura,
                    'capacidad' => $fila[$i]->Capacidad,
                    'disponibles' => $fila[$i]->Disponibles,
                    'ocupados' => $fila[$i]->Ocupados,
                    'codigo' => $fila[$i]->Codigo_docente,
                    'docente' => $fila[$i]->Docente,
                    // 'lunes' => [$lun1, $lun2],
                    // 'martes' => [$mar1, $mar2],
                    // 'miercoles' => [$mie1, $mie2],
                    // 'jueves' => [$jue1, $jue2],
                    // 'viernes' => [$vie1, $vie2],
                    // 'sabado' => [$sab1, $sab2],
                    // 'domingo' => [$dom1, $dom2],
                    // 'edificio' => $fila[$i]->Edf,
                    // 'salon' => $fila[$i]->Salon,
                    'inicio' => $fila[$i]->Fecha_inicio,
                    'tipo' => $fila[$i]->Tipo,
                    'horas' => $Hrs_sem
                ];

            }
            // else
            // {
            //     dd($fila[$i]->Creditos);
            //     // $cursos[$fila[$i]->Nrc]['horas'] += $fila[$i]->Hrs_sem;
                
            //     // Llenamos la información del NRC
            //     $cursos[$fila[$i]->Nrc] = [
            //         'lunes' => [$lun1, $lun2],
            //         'martes' => [$mar1, $mar2],
            //         'miercoles' => [$mie1, $mie2],
            //         'jueves' => [$jue1, $jue2],
            //         'viernes' => [$vie1, $vie2],
            //         'sabado' => [$sab1, $sab2],
            //         'domingo' => [$dom1, $dom2],
            //         'edificio' => $fila[$i]->Edf,
            //         'salon' => $fila[$i]->Salon
            //     ];

            // }

            $anterior = $actual;

        }

        dd($cursos);
        return view('index');
    }
}