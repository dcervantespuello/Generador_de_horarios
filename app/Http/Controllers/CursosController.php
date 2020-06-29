<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\CursosController;

class CursosController extends Controller
{
    public function index()
    {

        // 1. Inicializamos el array donde se va a organizar la información
        $cursos = [];

        // 2. Capturamos todos los nombres de la base de datos sin repetir.
        $nombres = DB::select('select distinct Nombre_asignatura from cursos');
        
        foreach ($nombres as $nombre) {

            $nombre = $nombre->Nombre_asignatura;
            
            // 3. Obtenemos la primera fila de cada curso.
            $fila = DB::select('select * from cursos where Nombre_asignatura = "'.$nombre.'" limit 1');
            $fila = $fila[0];

            // 4. Guardamos la información en el array de cursos.
            $cursos[$nombre] = [
                // 'materia' => $fila->Materia,
                'curso' => $fila->Curso,
                'campus' => $fila->Campus,
                'fecha_inicio' => $fila->Fecha_inicio,
                'creditos' => $fila->Creditos
            ];

            // 5. Obtenemos todos los NRC del curso
            $lista_nrc = DB::select('select distinct Nrc from cursos where Nombre_asignatura = "'.$nombre.'"');
            
            // 6. Guardamos los NRC en el array de cursos
            foreach ($lista_nrc as $nrc) {

                $nrc = $nrc->Nrc;
                
                // 7. Obtenemos la primera fila de cada NRC.
                
                $info = DB::select('select * from cursos where Nombre_asignatura = "'.$nombre.'" and Nrc = "'.$nrc.'" limit 1');
                $info = $info[0];
                
                // 8. Guardamos la información en el array de cursos.
                $cursos[$nombre][$nrc] = [
                    'materia' => $info->Materia,
                    'curso' => $info->Curso,
                    'seccion' => $info->Seccion,
                    'capacidad' => $info->Capacidad,
                    'disponibles' => $info->Disponibles,
                    'ocupados' => $info->Ocupados,
                    'codigo_docente' => $info->Codigo_docente,
                    'docente' => $info->Docente,
                    'tipo' => $info->Tipo
                ];

                // 9. Obtenemos las filas de cada NRC.
                $datos_nrc = DB::select('select * from cursos where Nombre_asignatura = "'.$nombre.'" and Nrc = "'.$nrc.'"');
                
                foreach ($datos_nrc as $dato) {

                    // 10. Arreglamos el Hrs_sem para que no tenga \r al final.
                    $texto_malo = $dato->Hrs_sem;
                    $subcadena = substr($texto_malo, 0, 1);
                    $hrs_sem = intval($subcadena);

                    // 11. Obtenemos el día de la semana que tiene la hora de clase
                    $dia = CursosController::obtenerDia($dato->Lunes, $dato->Martes, $dato->Miercoles, $dato->Jueves, $dato->Viernes, $dato->Sabado, $dato->Domingo);
                    
                    // 12. Agregamos la información en el array de cursos
                    $cursos[$nombre][$nrc][$dia[0]] = [
                        'hora1' => $dia[1],
                        'hora2' => $dia[2],
                        'edificio' => $dato->Edf,
                        'salon' => $dato->Salon,
                        'semanales' => $hrs_sem
                    ];

                }

            }
            
        }

        return view('index', ['cursos' => $cursos]);

    }


    public function hill_climbing(Request $request)
    {
        // Recibiendo variables
        $nombres = json_decode($request->contenido);
        
        // Preparando el array a retornar
        $arreglo = ['nombres' => $nombres];
        $json = json_encode($arreglo);

        // Enviando de regreso
        echo $json;
    }

    
    public function obtenerDia($lun, $mar, $mie, $jue, $vie, $sab, $dom)
    {

        // Array donde vamos a guardar el nombre del día y las horas
        $dia = [];

        if($lun) {

            // Sustraemos las horas del texto de la celda
            $partes = explode('-', $lun);
            $hora1 = substr($partes[0], 0, 2);
            $hora2 = substr($partes[1], 0, 2);

            // Guardamos y luego devolvemos
            $dia = ['lunes', $hora1, $hora2];
            
        } elseif($mar) {

            // Sustraemos las horas del texto de la celda
            $partes = explode('-', $mar);
            $hora1 = substr($partes[0], 0, 2);
            $hora2 = substr($partes[1], 0, 2);

            // Guardamos y luego devolvemos
            $dia = ['martes', $hora1, $hora2];
            
        } elseif($mie) {

            // Sustraemos las horas del texto de la celda
            $partes = explode('-', $mie);
            $hora1 = substr($partes[0], 0, 2);
            $hora2 = substr($partes[1], 0, 2);

            // Guardamos y luego devolvemos
            $dia = ['miercoles', $hora1, $hora2];
            
        } elseif($jue) {

            // Sustraemos las horas del texto de la celda
            $partes = explode('-', $jue);
            $hora1 = substr($partes[0], 0, 2);
            $hora2 = substr($partes[1], 0, 2);

            // Guardamos y luego devolvemos
            $dia = ['jueves', $hora1, $hora2];
            
        } elseif($vie) {

            // Sustraemos las horas del texto de la celda
            $partes = explode('-', $vie);
            $hora1 = substr($partes[0], 0, 2);
            $hora2 = substr($partes[1], 0, 2);

            // Guardamos y luego devolvemos
            $dia = ['viernes', $hora1, $hora2];
            
        } elseif($sab) {

            // Sustraemos las horas del texto de la celda
            $partes = explode('-', $sab);
            $hora1 = substr($partes[0], 0, 2);
            $hora2 = substr($partes[1], 0, 2);

            // Guardamos y luego devolvemos
            $dia = ['sabado', $hora1, $hora2];
            
        } elseif($dom) {

            // Sustraemos las horas del texto de la celda
            $partes = explode('-', $dom);
            $hora1 = substr($partes[0], 0, 2);
            $hora2 = substr($partes[1], 0, 2);

            // Guardamos y luego devolvemos
            $dia = ['domingo', $hora1, $hora2];
            
        }

        return $dia;
    }

}