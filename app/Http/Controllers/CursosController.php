<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\CursosController;
use PhpParser\Node\Stmt\Foreach_;

class CursosController extends Controller
{
	public function index()
	{
		// Obteniendo los cursos de la base de datos
		$cursos = CursosController::obtenerCursos();

		return view('index', ['cursos' => $cursos]);
	}

	public function obtenerCursos()
	{
		// 1. Inicializamos el array donde se va a organizar la información
		$cursos = [];

		// 2. Capturamos todos los nombres de la base de datos sin repetir.
		$nombres = DB::select("select distinct Nombre_asignatura from cursos");

		foreach ($nombres as $nombre) {

			$nombre = $nombre->Nombre_asignatura;

			// 3. Obtenemos la primera fila de cada curso.
			$fila = DB::select("select * from cursos where Nombre_asignatura = '$nombre' limit 1")[0];

			// 4. Guardamos la información en el array de cursos.
			$cursos[$nombre] = [
				'campus' => $fila->Campus,
				'fecha_inicio' => $fila->Fecha_inicio,
				'creditos' => $fila->Creditos,
				'nrc' => []
			];

			// 5. Obtenemos todos los NRC del curso
			$lista_nrc = DB::select("select distinct Nrc from cursos where Nombre_asignatura = '$nombre'");

			// 6. Guardamos los NRC en el array de cursos
			foreach ($lista_nrc as $nrc) {

				$nrc = $nrc->Nrc;

				// 7. Obtenemos las filas de cada NRC.
				$datos_nrc = DB::select("select * from cursos where Nombre_asignatura = '$nombre' and Nrc = '$nrc'");

				// 8. Obtenemos la primera fila de cada NRC.
				$info = $datos_nrc[0];

				// 9. Guardamos la información en el array de cursos.
				$cursos[$nombre]['nrc'][$nrc] = [
					'materia' => $info->Materia,
					'curso' => $info->Curso,
					'seccion' => $info->Seccion,
					'capacidad' => $info->Capacidad,
					'disponibles' => $info->Disponibles,
					'ocupados' => $info->Ocupados,
					'codigo_docente' => $info->Codigo_docente,
					'docente' => $info->Docente,
					'tipo' => $info->Tipo,
					'dias' => []
				];

				/* 
					* En la base de datos un NRC puede estar 
					* varias veces porque hay una fila por día de la semana.
					* Aquí se está guardando de cada NRC cada día de la semana en cada fila
				*/
				foreach ($datos_nrc as $dato) {

					// 10. Arreglamos el Hrs_sem para que no tenga \r al final.
					$texto_malo = $dato->Hrs_sem;
					$subcadena = substr($texto_malo, 0, 1);
					$hrs_sem = intval($subcadena);

					$fechas['lunes'] = $dato->Lunes;
					$fechas['martes'] = $dato->Martes;
					$fechas['miercoles'] = $dato->Miercoles;
					$fechas['jueves'] = $dato->Jueves;
					$fechas['viernes'] = $dato->Viernes;
					$fechas['sabado'] = $dato->Sabado;
					$fechas['domingo'] = $dato->Domingo;

					// 11. Obtenemos el día de la semana que tiene la hora de clase
					$dias = CursosController::obtenerDia($fechas);

					// 12. Agregamos la información en el array de cursos
					foreach ($dias as $i => $val) {

						if (isset($cursos[$nombre]['nrc'][$nrc]['dias'][$dias[$i][0]])) {
							$cursos[$nombre]['nrc'][$nrc]['dias'][$dias[$i][0]]['horas'][] = $dias[$i][1];
							$cursos[$nombre]['nrc'][$nrc]['dias'][$dias[$i][0]]['horas'][] = $dias[$i][2];
						} else {
							$cursos[$nombre]['nrc'][$nrc]['dias'][$dias[$i][0]] = [
								'horas' => [$dias[$i][1], $dias[$i][2]],
								'edificio' => $dato->Edf,
								'salon' => $dato->Salon,
								'semanales' => $hrs_sem
							];
						}
					}
				}
			}
		}

		return $cursos;
	}

	public function obtenerDia($fechas)
	{
		/* 
			* Array donde vamos a guardar el nombre del día, la hora1 y la hora2.
			* En una de las filas de un NRC pueden haber varios días,
			* por ejemplo, en una fila puede haber hora el lunes y el martes.
		*/
		$dias = [];

		foreach ($fechas as $dia => $horas_unidas) {
			if ($horas_unidas) {
				$horas = CursosController::romperHoras($horas_unidas);
				$dias[] = [$dia, $horas['hora1'], $horas['hora2']];
			}
		}

		return $dias;
	}

	public function romperHoras($dia)
	{
		// Sustraemos las horas del texto de la celda
		$partes = explode('-', $dia);

		$parte1 = substr($partes[0], 0, 2);

		if ($parte1[0] == 0) {

			$hora1 = $parte1[1];
		} else {

			$hora1 = $parte1;
		}

		$parte2 = substr($partes[1], 0, 2);

		if ($parte2[0] == 0) {

			$hora2 = $parte2[1];
		} else {

			$hora2 = $parte2;
		}

		$horas['hora1'] = $hora1;
		$horas['hora2'] = $hora2;

		return $horas;
	}

	public function obtenerSemana()
	{
		for ($i = 7; $i <= 20; $i++) {
			$horas[$i] = '';
		}

		$dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];

		foreach ($dias as $dia) {
			$semana[$dia] = $horas;
		}

		return $semana;
	}

	public function endKey($array, $num)
	{
		if ($num == 1) {

			$array2 = $array;

			while (true) {

				//Aquí utilizamos end() para poner el puntero en el último elemento, no para devolver su valor
				end($array2);
				$llave = key($array2);
				$seccion = DB::select("select Seccion from cursos where Nrc = '" . $llave . "'")[0]->Seccion;

				if (substr($seccion, -1) == "1" or substr($seccion, -1) == "2") {
					array_pop($array2);
					continue;
				} else {
					break;
				}
			}
		} elseif ($num == 2) {
			end($array);
			$llave = key($array);
		}

		return $llave;
	}

	public function validarNrc($semana, $dia, $horas, $nrc)
	{
		for ($i = 1; $i <= count($horas); $i++) {

			if ($i % 2 != 0) {
				$inicio = $i - 1;
				continue;
			} else {

				$final = $i - 1;

				for ($j = $horas[$inicio]; $j <= $horas[$final]; $j++) {

					if (empty($semana[$dia][$j]) or $semana[$dia][$j] == $nrc) {
						$semana[$dia][$j] = $nrc;
						$valido = true;
					} else {
						$valido = false;
						break;
					}
				}

				if ($valido) {
					continue;
				} else {
					break;
				}
			}
		}

		return [$valido, $semana];
	}

	public function nombreNrc($nrc)
	{
		$nombre = DB::select("select Nombre_asignatura from cursos where Nrc = '$nrc' limit 1")[0]->Nombre_asignatura;
		return $nombre;
	}

	public function permutacion($nombre, $perturbada, $cursos)
	{
		$listaNrc = $cursos[$nombre]['nrc'];
		$aleatorio = array_rand(array_flip(array_keys($listaNrc)));
		$listaDias = $listaNrc[$aleatorio]['dias'];
		$seccion = $listaNrc[$aleatorio]['seccion'];

		if (substr($seccion, -1) == "1" or substr($seccion, -1) == "2") {
			$resultado['continue'] = true;
		} else {

			$aceptado = false;
			$ultimo_dia = CursosController::endKey($listaDias, 2);

			foreach ($listaDias as $dia => $infoDia) {

				$horas = $infoDia['horas'];

				$validarNrc = CursosController::validarNrc($perturbada, $dia, $horas, $aleatorio);
				$perturbada = $validarNrc[1];
				$valido = $validarNrc[0];

				if ($valido) {
					if ($dia == $ultimo_dia) {
						$aceptado = true;
					}
				} else {
					foreach ($perturbada as $day => $hours) {
						foreach ($hours as $hour => $time) {
							if ($time == $aleatorio) {
								$perturbada[$day][$hour] = '';
							}
						}
					}
					break;
				}
			}

			if ($aceptado) {
				$resultado['continue'] = false;
				$resultado['aceptado'] = true;
				$resultado['perturbada'] = $perturbada;
				$resultado['aleatorio'] = $aleatorio;
			} else {
				$resultado['continue'] = true;
			}
		}
		return $resultado;
	}

	public function contarHuecos($semana)
	{
		$posiciones = [];
		$huecos = [];

		foreach ($semana as $dia => $horas) {

			$contador = 0;
			$posiciones[$dia] = [];
			foreach ($horas as $hora => $nrc) {
				$contador += 1;
				if ($nrc) {
					$posiciones[$dia][] = $contador;
				}
			}

			$huecos[$dia] = 0;
			if (count($posiciones[$dia]) > 1) {
				for ($i = $posiciones[$dia][0] + 1; $i < end($posiciones[$dia]); $i++) {
					if (!in_array($i, $posiciones[$dia])) {
						$huecos[$dia] += 1;
					}
				}
			} else {
				$huecos[$dia] = 0;
			}
		}

		return $huecos;
	}

	public function obtenerDistancias($nombres, $cursos)
	{
		foreach ($nombres as $nombre) {
			foreach ($cursos[$nombre]['nrc'] as $nrc => $info_nrc) {
				$puntos[] = ['nrc' => $nrc, 'nombre' => $nombre];
			}
		}

		foreach ($nombres as $nombre) {
			foreach ($cursos[$nombre]['nrc'] as $nrc => $info_nrc) {

				$dias = $info_nrc['dias'];
				$i = 0;
				foreach ($puntos as $punto) {

					$nrc_punto = $punto['nrc'];
					$nombre_punto = $punto['nombre'];

					if ($nombre == $nombre_punto) {
						$distancias[$nrc][$nrc_punto] = 0;
					} else {

						$huecos = [];
						$romper = false;

						foreach ($dias as $dia => $info_dia) {

							$espacios = [7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0, 13 => 0, 14 => 0, 15 => 0, 16 => 0, 17 => 0, 18 => 0, 19 => 0, 20 => 0];
							$estaDiaEnPunto = isset($cursos[$nombre_punto]['nrc'][$nrc_punto]['dias'][$dia]);

							if ($estaDiaEnPunto) {

								$horas_nrc = $info_dia['horas'];

								foreach ($horas_nrc as $hora) {
									if ($espacios[$hora] == 0 or $espacios[$hora] == $nrc) {
										$espacios[$hora] = $nrc;
									} else {
										$romper = true;
										break;
									}
								}

								if ($romper) {
									break;
								}

								$horas_punto = $cursos[$nombre_punto]['nrc'][$nrc_punto]['dias'][$dia]['horas'];

								foreach ($horas_punto as $hora) {
									if ($espacios[$hora] == 0 or $espacios[$hora] == $nrc_punto) {
										$espacios[$hora] = $nrc_punto;
									} else {
										$romper = true;
										break;
									}
								}

								if ($romper) {
									break;
								}

								$cont = 0;
								$inicial = 0;
								$final = 0;
								$empezar = false;
								foreach ($espacios as $hora => $espacio) {

									if ($espacio != 0 and $empezar == false) {
										$inicial = $espacio;
										$empezar = true;

										if ($inicial == $nrc) {
											$final = $nrc_punto;
										} else {
											$final = $nrc;
										}
									}

									if ($empezar == true and $espacio == 0) {
										$cont += 1;
									} elseif ($empezar == true and $espacio == $final) {
										$huecos[] = $cont;
										break;
									}
								}
							}
						}

						if ($romper) {
							$distancias[$nrc][$nrc_punto] = 0;
						} else {
							$distancia = array_sum($huecos);
							$distancias[$nrc][$nrc_punto] = $distancia;
						}
					}
				}
			}
		}

		return $distancias;
	}

	public function obtenerHeuristicasLocales($distancias)
	{
		foreach ($distancias as $nrc => $puntos) {
			foreach ($puntos as $punto => $distancia) {
				if ($distancia == 0) {
					$locales[$nrc][$punto] = 0;
				} else {
					$locales[$nrc][$punto] = 1 / $distancia;
				}
			}
		}

		return $locales;
	}

	public function obtenerMatrizFeromonas($distancias)
	{
		foreach ($distancias as $nrc => $puntos) {
			foreach ($puntos as $punto => $distancia) {
				$feromonas[$nrc][$punto] = 0;
			}
		}

		return $feromonas;
	}

	public function shuffle_assoc($array)
	{
		$keys = array_keys($array);

		shuffle($keys);

		foreach ($keys as $key) {
			$new[$key] = $array[$key];
		}

		$array = $new;

		return true;
	}

	public function aumentarFeromonas($elegidos, $distancias, $feromonas)
	{
		$costoTour = 0;

		for ($i = 0; $i < count($elegidos); $i++) {

			$nrc_actual = $elegidos[$i];

			if (end($elegidos) == $nrc_actual) {
				$nrc_siguiente = $elegidos[0];
			} else {
				$nrc_siguiente = $elegidos[$i + 1];
			}

			$costoTour += $distancias[$nrc_actual][$nrc_siguiente];
		}

		for ($i = 0; $i < count($elegidos); $i++) {

			$nrc_actual = $elegidos[$i];

			if (end($elegidos) == $nrc_actual) {
				$nrc_siguiente = $elegidos[0];
			} else {
				$nrc_siguiente = $elegidos[$i + 1];
			}

			if ($costoTour != 0) {
				$feromonas[$nrc_actual][$nrc_siguiente] += 1 / $costoTour;
			}
		}

		return $feromonas;
	}

	public function hill_climbing(Request $request)
	{
		$nombres = $request->input('nombres');
		$cursos = CursosController::obtenerCursos();
		$semana = CursosController::obtenerSemana();
		$iteraciones = 5000;
		$cruzados = [];
		$elegidos = [];
		$start = microtime(true);

		foreach ($nombres as $nombre) {

			$listaNrc = $cursos[$nombre]['nrc'];

			foreach ($listaNrc as $nrc => $infoNrc) {

				$listaDias = $infoNrc['dias'];
				$seccion = $infoNrc['seccion'];

				if (substr($seccion, -1) == "1" or substr($seccion, -1) == "2") {
					continue;
				} else {

					$aceptado = false;
					$ultimo_nrc = CursosController::endKey($listaNrc, 1);
					$ultimo_dia = CursosController::endKey($listaDias, 2);

					foreach ($listaDias as $dia => $infoDia) {

						$horas = $infoDia['horas'];

						$validarNrc = CursosController::validarNrc($semana, $dia, $horas, $nrc);
						$semana = $validarNrc[1];
						$valido = $validarNrc[0];

						if ($valido) {
							if ($dia == $ultimo_dia) {
								$aceptado = true;
								$elegidos[] = $nrc;
							}
						} else {
							// Se quita el NRC de toda la semana
							foreach ($semana as $day => $hours) {
								foreach ($hours as $hour => $time) {
									if ($time == $nrc) {
										$semana[$day][$hour] = '';
									}
								}
							}

							break;
						}
					}

					if ($aceptado) {
						break;
					}

					if ($nrc == $ultimo_nrc) {
						$cruzados[] = $nombre;
					}
				}
			}
		}

		if ($cruzados) {
			$error = "Los NRC de los siguientes cursos se cruzan: ";

			$last = end($cruzados);
			foreach ($cruzados as $cruzado) {

				if ($last == $cruzado) {

					$error .= $cruzado;
				} else {

					$error .= $cruzado . ", ";
				}
			}

			return redirect()->back()->with('error', $error);
		} else {
			// $semanas = [];
			$huequillos = [];
			while ($iteraciones > 0) {
				$perturbada = $semana;

				while (true) {
					if (count($elegidos) == 1) {
						$nrc1 = end($elegidos);
						break;
					} else {
						$nrc1 = array_rand(array_flip($elegidos));
						$nrc2 = array_rand(array_flip($elegidos));
						if ($nrc1 != $nrc2) {
							break;
						}
					}
				}

				$nombre1 = CursosController::nombreNrc($nrc1);
				if (isset($nrc2)) {
					$nombre2 = CursosController::nombreNrc($nrc2);
				}

				foreach ($perturbada as $dia => $horas) {

					foreach ($horas as $hora => $nrc) {

						if ($nrc == $nrc1) {
							$perturbada[$dia][$hora] = '';
						} elseif (isset($nrc2)) {
							if ($nrc == $nrc2) {
								$perturbada[$dia][$hora] = '';
							}
						}
					}
				}

				while (true) {

					$permutacion1 = CursosController::permutacion($nombre1, $perturbada, $cursos);
					if ($permutacion1['continue']) {
						continue;
					} else {
						if ($permutacion1['aceptado']) {
							if (!isset($nombre2)) {
								$perturbada = $permutacion1['perturbada'];
							} else {
								$perturbada1 = $permutacion1['perturbada'];
							}
							$aleatorio1 = $permutacion1['aleatorio'];
						} else {
							continue;
						}
					}

					if (isset($nombre2)) {

						$permutacion2 = CursosController::permutacion($nombre2, $perturbada1, $cursos);
						if ($permutacion2['continue']) {
							continue;
						} else {
							if ($permutacion2['aceptado']) {
								$perturbada = $permutacion2['perturbada'];
								$aleatorio2 = $permutacion2['aleatorio'];
							} else {
								continue;
							}
						}
					}

					break;
				}
				// $semanas[] = [$semana, $nrc1, $aleatorio1];


				$huecos_zx = CursosController::contarHuecos($semana);
				$huecos_zxp = CursosController::contarHuecos($perturbada);

				$zx = array_sum($huecos_zx);
				$zxp = array_sum($huecos_zxp);

				if ($zxp < $zx) {
					foreach ($elegidos as $i => $elegido) {
						if ($elegido == $nrc1) {
							unset($elegidos[$i]);
						} elseif (isset($nrc2)) {
							if ($elegido == $nrc2) {
								unset($elegidos[$i]);
							}
						}
					}
					$elegidos = array_values($elegidos);

					$elegidos[] = $aleatorio1;
					if (isset($aleatorio2)) {
						$elegidos[] = $aleatorio2;
					}

					$huequillos[] = $zxp;

					$semana = $perturbada;
				}

				$iteraciones -= 1;
			}
			$end = microtime(true);
			$time = $end - $start;
			// dd($time, $huequillos);
			$definitivos = [];
			foreach ($elegidos as $elegido) {
				$nombre = CursosController::nombreNrc($elegido);
				$definitivos[$elegido] = $nombre;
			}

			$filas = [];
			for ($i = 7; $i <= 20; $i++) {
				foreach ($semana as $dia => $horas) {
					$filas[$i][] = $semana[$dia][$i];
				}
			}

			$sem = [];
			// foreach ($semanas as $semana) {
			// 	$lista = [];
			// 	foreach ($semana[0] as $dia => $horas) {
			// 		foreach ($horas as $hora => $nrc) {
			// 			if (!empty($nrc) and !in_array($nrc, $lista)) {
			// 				$lista[] = $nrc;
			// 			}
			// 		}
			// 	}
			// 	$sem[] = [$lista, $semana[1], $semana[2]];
			// }
			return view('resultado', ['cursos' => $cursos, 'filas' => $filas, 'definitivos' => $definitivos, 'sem' => $sem]);
		}
	}

	public function simulated_annealing(Request $request)
	{
		$nombres = $request->input('nombres');
		$cursos = CursosController::obtenerCursos();
		$semana = CursosController::obtenerSemana();
		$temperatura = 500;
		$alfa = 0.99;
		$cruzados = [];
		$elegidos = [];
		$start = microtime(true);

		foreach ($nombres as $nombre) {

			$listaNrc = $cursos[$nombre]['nrc'];

			foreach ($listaNrc as $nrc => $infoNrc) {

				$listaDias = $infoNrc['dias'];
				$seccion = $infoNrc['seccion'];

				if (substr($seccion, -1) == "1" or substr($seccion, -1) == "2") {
					continue;
				} else {

					$aceptado = false;
					$ultimo_nrc = CursosController::endKey($listaNrc, 1);
					$ultimo_dia = CursosController::endKey($listaDias, 2);

					foreach ($listaDias as $dia => $infoDia) {

						$horas = $infoDia['horas'];

						$validarNrc = CursosController::validarNrc($semana, $dia, $horas, $nrc);
						$semana = $validarNrc[1];
						$valido = $validarNrc[0];

						if ($valido) {
							if ($dia == $ultimo_dia) {
								$aceptado = true;
								$elegidos[] = $nrc;
							}
						} else {
							// Se quita el NRC de toda la semana
							foreach ($semana as $day => $hours) {
								foreach ($hours as $hour => $time) {
									if ($time == $nrc) {
										$semana[$day][$hour] = '';
									}
								}
							}

							break;
						}
					}

					if ($aceptado) {
						break;
					}

					if ($nrc == $ultimo_nrc) {
						$cruzados[] = $nombre;
					}
				}
			}
		}

		if ($cruzados) {
			$error = "Los NRC de los siguientes cursos se cruzan: ";

			$last = end($cruzados);
			foreach ($cruzados as $cruzado) {

				if ($last == $cruzado) {

					$error .= $cruzado;
				} else {

					$error .= $cruzado . ", ";
				}
			}

			return redirect()->back()->with('error', $error);
		} else {
			// $semanas = [];
			// $huequillos = [];
			while ($temperatura > 0.1) {
				$perturbada = $semana;

				while (true) {
					if (count($elegidos) == 1) {
						$nrc1 = end($elegidos);
						break;
					} else {
						$nrc1 = array_rand(array_flip($elegidos));
						$nrc2 = array_rand(array_flip($elegidos));
						if ($nrc1 != $nrc2) {
							break;
						}
					}
				}

				$nombre1 = CursosController::nombreNrc($nrc1);
				if (isset($nrc2)) {
					$nombre2 = CursosController::nombreNrc($nrc2);
				}

				foreach ($perturbada as $dia => $horas) {

					foreach ($horas as $hora => $nrc) {

						if ($nrc == $nrc1) {
							$perturbada[$dia][$hora] = '';
						} elseif (isset($nrc2)) {
							if ($nrc == $nrc2) {
								$perturbada[$dia][$hora] = '';
							}
						}
					}
				}

				while (true) {

					$permutacion1 = CursosController::permutacion($nombre1, $perturbada, $cursos);
					if ($permutacion1['continue']) {
						continue;
					} else {
						if ($permutacion1['aceptado']) {
							if (!isset($nombre2)) {
								$perturbada = $permutacion1['perturbada'];
							} else {
								$perturbada1 = $permutacion1['perturbada'];
							}
							$aleatorio1 = $permutacion1['aleatorio'];
						} else {
							continue;
						}
					}

					if (isset($nombre2)) {

						$permutacion2 = CursosController::permutacion($nombre2, $perturbada1, $cursos);
						if ($permutacion2['continue']) {
							continue;
						} else {
							if ($permutacion2['aceptado']) {
								$perturbada = $permutacion2['perturbada'];
								$aleatorio2 = $permutacion2['aleatorio'];
							} else {
								continue;
							}
						}
					}

					break;
				}
				// $semanas[] = [$semana, $nrc1, $aleatorio1];


				$huecos_zx = CursosController::contarHuecos($semana);
				$huecos_zxp = CursosController::contarHuecos($perturbada);

				$zx = array_sum($huecos_zx);
				$zxp = array_sum($huecos_zxp);

				if ($zxp < $zx) {
					foreach ($elegidos as $i => $elegido) {
						if ($elegido == $nrc1) {
							unset($elegidos[$i]);
						} elseif (isset($nrc2)) {
							if ($elegido == $nrc2) {
								unset($elegidos[$i]);
							}
						}
					}
					$elegidos = array_values($elegidos);

					$elegidos[] = $aleatorio1;
					if (isset($aleatorio2)) {
						$elegidos[] = $aleatorio2;
					}

					$huequillos[] = $zxp;

					$semana = $perturbada;
					$temperatura *= $alfa;
				} else {
					$n = (float)rand() / (float)getrandmax();
					$e = M_E;
					$dz = $zxp - $zx;
					$division = - ($dz / $temperatura);
					$pdx = pow($e, $division);

					if ($n < $pdx) {
						foreach ($elegidos as $i => $elegido) {
							if ($elegido == $nrc1) {
								unset($elegidos[$i]);
							} elseif (isset($nrc2)) {
								if ($elegido == $nrc2) {
									unset($elegidos[$i]);
								}
							}
						}
						$elegidos = array_values($elegidos);

						$elegidos[] = $aleatorio1;
						if (isset($aleatorio2)) {
							$elegidos[] = $aleatorio2;
						}

						$huequillos[] = $zxp;

						$semana = $perturbada;
						$temperatura *= $alfa;
					}
				}
			}
			$end = microtime(true);
			$time = $end - $start;
			// dd($time, $huequillos);
			$definitivos = [];
			foreach ($elegidos as $elegido) {
				$nombre = CursosController::nombreNrc($elegido);
				$definitivos[$elegido] = $nombre;
			}

			$filas = [];
			for ($i = 7; $i <= 20; $i++) {
				foreach ($semana as $dia => $horas) {
					$filas[$i][] = $semana[$dia][$i];
				}
			}

			$sem = [];
			// foreach ($semanas as $semana) {
			// 	$lista = [];
			// 	foreach ($semana[0] as $dia => $horas) {
			// 		foreach ($horas as $hora => $nrc) {
			// 			if (!empty($nrc) and !in_array($nrc, $lista)) {
			// 				$lista[] = $nrc;
			// 			}
			// 		}
			// 	}
			// 	$sem[] = [$lista, $semana[1], $semana[2]];
			// }
			return view('resultado', ['cursos' => $cursos, 'filas' => $filas, 'definitivos' => $definitivos, 'sem' => $sem]);
		}
	}

	public function ant_colony(Request $request)
	{
		$nombres = $request->input('nombres');
		$cursos = CursosController::obtenerCursos();
		$distancias = CursosController::obtenerDistancias($nombres, $cursos);
		$locales = CursosController::obtenerHeuristicasLocales($distancias);
		$feromonas = CursosController::obtenerMatrizFeromonas($distancias);
		$semana = CursosController::obtenerSemana();
		$iteraciones = 5000;
		$cruzados = [];
		$elegidos = [];
		$start = microtime(true);

		foreach ($nombres as $nombre) {

			$listaNrc = $cursos[$nombre]['nrc'];

			foreach ($listaNrc as $nrc => $infoNrc) {

				$listaDias = $infoNrc['dias'];
				$seccion = $infoNrc['seccion'];

				if (substr($seccion, -1) == "1" or substr($seccion, -1) == "2") {
					continue;
				} else {

					$aceptado = false;
					$ultimo_nrc = CursosController::endKey($listaNrc, 1);
					$ultimo_dia = CursosController::endKey($listaDias, 2);

					foreach ($listaDias as $dia => $infoDia) {

						$horas = $infoDia['horas'];

						$validarNrc = CursosController::validarNrc($semana, $dia, $horas, $nrc);
						$semana = $validarNrc[1];
						$valido = $validarNrc[0];

						if ($valido) {
							if ($dia == $ultimo_dia) {
								$aceptado = true;
								$elegidos[] = $nrc;
							}
						} else {
							// Se quita el NRC de toda la semana
							foreach ($semana as $day => $hours) {
								foreach ($hours as $hour => $time) {
									if ($time == $nrc) {
										$semana[$day][$hour] = '';
									}
								}
							}

							break;
						}
					}

					if ($aceptado) {
						break;
					}

					if ($nrc == $ultimo_nrc) {
						$cruzados[] = $nombre;
					}
				}
			}
		}

		if ($cruzados) {
			$error = "Los NRC de los siguientes cursos se cruzan: ";

			$last = end($cruzados);
			foreach ($cruzados as $cruzado) {

				if ($last == $cruzado) {

					$error .= $cruzado;
				} else {

					$error .= $cruzado . ", ";
				}
			}

			return redirect()->back()->with('error', $error);
		} else {
			// $semanas = [];
			$huequillos = [];

			$feromonas = CursosController::aumentarFeromonas($elegidos, $distancias, $feromonas);

			while ($iteraciones > 0) {
				$perturbada = $semana;

				while (true) {
					if (count($elegidos) == 1) {
						$nrc1 = end($elegidos);
						break;
					} else {
						$nrc1 = array_rand(array_flip($elegidos));
						$nrc2 = array_rand(array_flip($elegidos));
						if ($nrc1 != $nrc2) {
							break;
						}
					}
				}

				$nombre1 = CursosController::nombreNrc($nrc1);
				if (isset($nrc2)) {
					$nombre2 = CursosController::nombreNrc($nrc2);
				}

				foreach ($perturbada as $dia => $horas) {

					foreach ($horas as $hora => $nrc) {

						if ($nrc == $nrc1) {
							$perturbada[$dia][$hora] = '';
						} elseif (isset($nrc2)) {
							if ($nrc == $nrc2) {
								$perturbada[$dia][$hora] = '';
							}
						}
					}
				}

				while (true) {

					$permutacion1 = CursosController::permutacion($nombre1, $perturbada, $cursos);
					if ($permutacion1['continue']) {
						continue;
					} else {
						if ($permutacion1['aceptado']) {
							if (!isset($nombre2)) {
								$perturbada = $permutacion1['perturbada'];
							} else {
								$perturbada1 = $permutacion1['perturbada'];
							}
							$aleatorio1 = $permutacion1['aleatorio'];
						} else {
							continue;
						}
					}

					if (isset($nombre2)) {

						$permutacion2 = CursosController::permutacion($nombre2, $perturbada1, $cursos);
						if ($permutacion2['continue']) {
							continue;
						} else {
							if ($permutacion2['aceptado']) {
								$perturbada = $permutacion2['perturbada'];
								$aleatorio2 = $permutacion2['aleatorio'];
							} else {
								continue;
							}
						}
					}

					break;
				}
				// $semanas[] = [$semana, $nrc1, $aleatorio1];
				
				foreach ($elegidos as $i => $elegido) {
					if ($elegido == $nrc1) {
						unset($elegidos[$i]);
					} elseif (isset($nrc2)) {
						if ($elegido == $nrc2) {
							unset($elegidos[$i]);
						}
					}
				}
				$elegidos = array_values($elegidos);

				$elegidos[] = $aleatorio1;
				if (isset($aleatorio2)) {
					$elegidos[] = $aleatorio2;
				}

				$feromonas = CursosController::aumentarFeromonas($elegidos, $distancias, $feromonas);
				$semana = $perturbada;

				$iteraciones -= 1;
			}
			dd($feromonas);
			$end = microtime(true);
			$time = $end - $start;
			// dd($time, $huequillos);
			$definitivos = [];
			foreach ($elegidos as $elegido) {
				$nombre = CursosController::nombreNrc($elegido);
				$definitivos[$elegido] = $nombre;
			}

			$filas = [];
			for ($i = 7; $i <= 20; $i++) {
				foreach ($semana as $dia => $horas) {
					$filas[$i][] = $semana[$dia][$i];
				}
			}

			$sem = [];
			// foreach ($semanas as $semana) {
			// 	$lista = [];
			// 	foreach ($semana[0] as $dia => $horas) {
			// 		foreach ($horas as $hora => $nrc) {
			// 			if (!empty($nrc) and !in_array($nrc, $lista)) {
			// 				$lista[] = $nrc;
			// 			}
			// 		}
			// 	}
			// 	$sem[] = [$lista, $semana[1], $semana[2]];
			// }
			return view('resultado', ['cursos' => $cursos, 'filas' => $filas, 'definitivos' => $definitivos, 'sem' => $sem]);
		}
	}
}
