<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\CursosController;
use PhpParser\Node\Stmt\Foreach_;

class CursosController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}

	public function index()
	{
		// Obteniendo los cursos de la base de datos
		$cursos = CursosController::obtenerCursos();
		$n = (float)rand() / (float)getrandmax();
		$n = (int)round($n * 3);

		while ($n != 1 and $n != 2 and $n != 3) {
			$n = (float)rand() / (float)getrandmax();
			$n = (int)round($n * 3);
		}

		$n = 3;

		switch ($n) {
			case 1:
				$meta = 'hill_climbing';
				break;

			case 2:
				$meta = 'simulated_annealing';
				break;

			case 3:
				$meta = 'ant_colony';
				break;
		}

		return view('home', ['cursos' => $cursos, 'n' => $n, 'meta' => $meta]);
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

	public function verificarCruce($cursos, $semana, $nrc, $nombre)
	{
		$infoNrc = $cursos[$nombre]['nrc'][$nrc];
		$listaDias = $infoNrc['dias'];
		$seccion = $infoNrc['seccion'];

		if (substr($seccion, -1) == "1" or substr($seccion, -1) == "2") {
			$resultado = true;
		} else {

			$aceptado = false;
			$ultimo_dia = CursosController::endKey($listaDias, 2);

			foreach ($listaDias as $dia => $infoDia) {

				$horas = $infoDia['horas'];

				$validarNrc = CursosController::validarNrc($semana, $dia, $horas, $nrc);
				$semana = $validarNrc[1];
				$valido = $validarNrc[0];

				if ($valido) {
					if ($dia == $ultimo_dia) {
						$aceptado = true;
					}
				} else {
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
				$resultado = false;
				// $resultado['semana'] = $semana;
			} else {
				$resultado = true;
			}
		}

		return $resultado;
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
		// Creando arreglo con todos los puntos del mapa para luego calcular las distancia entre ellos
		foreach ($nombres as $nombre) {
			foreach ($cursos[$nombre]['nrc'] as $nrc => $info_nrc) {
				$puntos[] = ['nrc' => $nrc, 'nombre' => $nombre];
			}
		}

		// Tomamos cada nombre de los cursos elegidos
		foreach ($nombres as $nombre) {

			// De cada nombre tomamos todos los nrc
			foreach ($cursos[$nombre]['nrc'] as $nrc => $info_nrc) {

				// Tomamos la lista de los días de la semana en los que se ve cada nrc
				$dias = $info_nrc['dias'];

				// ESTO NO SE PA QUE ES
				// $i = 0;

				// Calculamos la distancia desde el nrc actual hasta cada punto
				foreach ($puntos as $punto) {

					// Capturamos el nrc y el nombre del curso del punto actual
					$nrc_punto = $punto['nrc'];
					$nombre_punto = $punto['nombre'];

					// Se comprueba si el nrc actual y el punto actual son del mismo curso
					if ($nombre == $nombre_punto) {
						$distancias[$nrc][$nrc_punto] = 'Son del mismo curso';
					} else {

						// Se guarda el conteo de los huecos entre el nrc y el punto
						$huecos = [];

						// Variable para saber si se cruza el nrc o el punto en el arreglo de horas de la semana
						$romper = false;

						// Estado para saber si no están nrc y punto en ningún día
						$coinciden = false;

						// Se calcula la distancia entre el nrc y el punto en cada día de la semana
						foreach ($dias as $dia => $info_dia) {

							// Arreglo donde se ubican los nrc en cada día y se mira cuántos huecos hay entre ellos
							$espacios = [7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0, 13 => 0, 14 => 0, 15 => 0, 16 => 0, 17 => 0, 18 => 0, 19 => 0, 20 => 0];

							// Saber si el día actual que se ve en el nrc está en el punto
							$estaDiaEnPunto = isset($cursos[$nombre_punto]['nrc'][$nrc_punto]['dias'][$dia]);

							// Si el día está en el punto...
							if ($estaDiaEnPunto) {

								// Si coinciden el nrc y el punto en algún día
								$coinciden = true;

								// Guardamos las horas del día actual del nrc
								$horas_nrc = $info_dia['horas'];

								// Miramos si se cruzan en el arreglo
								// Si se cruzan se rompe y se guarda la distancia entre el nrc y el punto como "Se cruzan"
								// Si no, se agregan en el arreglo en la posición correspondiente
								foreach ($horas_nrc as $hora) {
									if ($espacios[$hora] == 0 or $espacios[$hora] == $nrc) {
										$espacios[$hora] = $nrc;
									} else {
										// Aquí se sale del primer foreach
										$romper = true;
										break;
									}
								}

								// Aquí se sale del segundo foreach
								if ($romper) {
									break;
								}

								// Aquí se repite lo anterior, pero ahora con las horas del punto
								$horas_punto = $cursos[$nombre_punto]['nrc'][$nrc_punto]['dias'][$dia]['horas'];

								foreach ($horas_punto as $hora) {
									if ($espacios[$hora] == 0 or $espacios[$hora] == $nrc_punto) {
										$espacios[$hora] = $nrc_punto;
									} else {
										// Se sale del primer foreach
										$romper = true;
										break;
									}
								}

								// Se sale del segundo foreach
								if ($romper) {
									break;
								}

								// cantidad de huecos entre el nrc y el punto
								$cont = 0;

								// Posición inicial
								$inicial = 0;

								// Posición final
								$final = 0;

								// Estado para saber si ya se comenzó el conteo
								$empezar = false;

								// Realizando el conteo de los espacios entre el nrc y el punto
								foreach ($espacios as $hora => $espacio) {

									// Para cada espacio si es diferente de 0 y no ha comenzado el conteo
									if ($espacio != 0 and $empezar == false) {

										// Se toma la posición inicial
										$inicial = $espacio;

										// Se comienza el conteo
										$empezar = true;

										// Aquí definimos que el final es el contrario del inicial
										if ($inicial == $nrc) {
											$final = $nrc_punto;
										} else {
											$final = $nrc;
										}
									}

									// Si es un espacio se aumenta 1 en el conteo de huecos
									// Si se trata del final entonces se guarda la cantidad de huecos del día
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
							$distancias[$nrc][$nrc_punto] = 'Se cruzan';
						} elseif (!$coinciden) {
							$distancias[$nrc][$nrc_punto] = 'No coinciden en la semana';
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
				break;
			} else {
				$nrc_siguiente = $elegidos[$i + 1];
			}

			$dist = $distancias[$nrc_actual][$nrc_siguiente];

			if (is_string($dist)) {
				return [$feromonas, true];
			} else {
				$costoTour += $dist;
			}
		}

		for ($i = 0; $i < count($elegidos); $i++) {

			$nrc_actual = $elegidos[$i];

			if (end($elegidos) == $nrc_actual) {
				break;
			} else {
				$nrc_siguiente = $elegidos[$i + 1];
			}

			if ($costoTour != 0) {
				$feromonas[$nrc_actual][$nrc_siguiente] += 1 / $costoTour;
			}
		}

		return [$feromonas, false];
	}

	public function evaporarFeromonas($feromonas)
	{
		foreach ($feromonas as $nrc => $puntos) {
			foreach ($puntos as $punto => $feromona) {
				$feromonas[$nrc][$punto] *= 0.9;
			}
		}

		return $feromonas;
	}

	public function consultarPrerequisitos($elegidos)
	{
		$invalidos = [];
		$cadena = auth()->user()->aprobados;
		$aprobados = explode(",", $cadena);

		foreach ($elegidos as $elegido) {
			$tienePrerequisitos = DB::select("select * from prerequisitos where nombre = '$elegido'");
			if ($tienePrerequisitos) {
				$cadena = $tienePrerequisitos[0]->prerequisitos;
				$prerequisitos = explode(",", $cadena);
				$noAprobados = [];
				foreach ($prerequisitos as $prerequisito) {
					if (!in_array($prerequisito, $aprobados)) {
						$noAprobados[] = $prerequisito;
					}
				}
				if ($noAprobados) {
					$invalidos[] = [$elegido, $noAprobados];
				}
			}
		}

		return $invalidos;
	}

	public function stats_standard_deviation(array $a, $sample = true)
	{
		$n = count($a);
		if ($n === 0) {
			trigger_error("The array has zero elements", E_USER_WARNING);
			return false;
		}
		if ($sample && $n === 1) {
			trigger_error("The array has only 1 element", E_USER_WARNING);
			return false;
		}
		$mean = array_sum($a) / $n;
		$carry = 0.0;
		foreach ($a as $val) {
			$d = ((float) $val) - $mean;
			$carry += $d * $d;
		};
		if ($sample) {
			--$n;
		}
		return sqrt($carry / $n);
	}

	public function hill_climbing(Request $request)
	{
		$ensayos = 50;
		$soluciones = [];
		$tiempos = [];

		while ($ensayos > 0) {
			$nombres = $request->input('nombres');
			$invalidos = CursosController::consultarPrerequisitos($nombres);

			if ($invalidos) {

				$invalido = $invalidos[0][0];
				$noAprobados = $invalidos[0][1];
				$error = "No puede elegir el curso $invalido debido a que usted no ha aprobado: ";

				$last = end($noAprobados);
				foreach ($noAprobados as $noAprobado) {

					if ($last == $noAprobado) {
						$error .= $noAprobado;
					} else {
						$error .= $noAprobado . ", ";
					}
				}

				return redirect()->back()->with('error', $error);
			}

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
				$huecos_zx = CursosController::contarHuecos($semana);
				$zx = array_sum($huecos_zx);
				$soluciones[] = $zx;
				$tiempos[] = $time;
			}
			$ensayos -= 1;
		}

		$promedio_soluciones = array_sum($soluciones) / count($soluciones);
		$promerio_tiempos = array_sum($tiempos) / count($tiempos);

		$de_soluciones = CursosController::stats_standard_deviation($soluciones);
		$de_tiempos = CursosController::stats_standard_deviation($tiempos);

		$robustez = count(array_unique($soluciones));
		dd($soluciones, $tiempos, $promedio_soluciones, $promerio_tiempos, $de_soluciones, $de_tiempos, $robustez);
		return view('resultado', ['cursos' => $cursos, 'filas' => $filas, 'definitivos' => $definitivos, 'sem' => $sem]);
	}

	public function simulated_annealing(Request $request)
	{
		$ensayos = 50;
		$soluciones = [];
		$tiempos = [];

		while ($ensayos > 0) {
			$nombres = $request->input('nombres');
			$invalidos = CursosController::consultarPrerequisitos($nombres);

			if ($invalidos) {

				$invalido = $invalidos[0][0];
				$noAprobados = $invalidos[0][1];
				$error = "No puede elegir el curso $invalido debido a que usted no ha aprobado: ";

				$last = end($noAprobados);
				foreach ($noAprobados as $noAprobado) {

					if ($last == $noAprobado) {
						$error .= $noAprobado;
					} else {
						$error .= $noAprobado . ", ";
					}
				}

				return redirect()->back()->with('error', $error);
			}

			$cursos = CursosController::obtenerCursos();
			$semana = CursosController::obtenerSemana();
			$temperatura = 5000;
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
				$huecos_zx = CursosController::contarHuecos($semana);
				$zx = array_sum($huecos_zx);
				$soluciones[] = $zx;
				$tiempos[] = $time;
			}
			$ensayos -= 1;
		}

		$promedio_soluciones = array_sum($soluciones) / count($soluciones);
		$promerio_tiempos = array_sum($tiempos) / count($tiempos);

		$de_soluciones = CursosController::stats_standard_deviation($soluciones);
		$de_tiempos = CursosController::stats_standard_deviation($tiempos);

		$robustez = count(array_unique($soluciones));
		dd($soluciones, $tiempos, $promedio_soluciones, $promerio_tiempos, $de_soluciones, $de_tiempos, $robustez);
		return view('resultado', ['cursos' => $cursos, 'filas' => $filas, 'definitivos' => $definitivos, 'sem' => $sem]);
	}

	public function ant_colony(Request $request)
	{
		// Nombres de cursos elegidos por el estudiante
		$nombres = $request->input('nombres');
		// Se toma la lista de los cursos elegidos por el estudiante, lo cuales aún no puede ver
		$invalidos = CursosController::consultarPrerequisitos($nombres);

		// Si hay algún curso elegido que sea inválido lo manda para el home con un mensaje de error
		if ($invalidos) {

			$invalido = $invalidos[0][0];
			$noAprobados = $invalidos[0][1];
			$error = "No puede elegir el curso $invalido debido a que usted no ha aprobado: ";

			$last = end($noAprobados);
			foreach ($noAprobados as $noAprobado) {

				if ($last == $noAprobado) {
					$error .= $noAprobado;
				} else {
					$error .= $noAprobado . ", ";
				}
			}

			return redirect()->back()->with('error', $error);
		}

		$start = microtime(true);

		// Arreglo con toda la información de los cursos
		$cursos = CursosController::obtenerCursos();
		$distancias = CursosController::obtenerDistancias($nombres, $cursos);
		$locales = CursosController::obtenerHeuristicasLocales($distancias);
		$feromonas = CursosController::obtenerMatrizFeromonas($distancias);
		$iteraciones = 5000;
		$repeticiones = 5000;
		$resultados = [];
		$numero_elegidos = count($nombres);
		$huecos = 0;
		$elegidos_def = [];
		$semana_def = [];
		$cadenas = true;


		while ($cadenas == true) {

			$semana = CursosController::obtenerSemana();
			$cruzados = [];
			$elegidos = [];

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
				shuffle($nombres);
				continue;
			}

			$feroArreglo = CursosController::aumentarFeromonas($elegidos, $distancias, $feromonas);
			$feromonas = $feroArreglo[0];
			$cadenas = $feroArreglo[1];

			if ($cadenas) {
				shuffle($nombres);
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
			$semana_inicial = $semana;

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

				$elegidos_nuevos = $elegidos;

				foreach ($elegidos_nuevos as $i => $elegido) {
					if ($elegido == $nrc1) {
						unset($elegidos_nuevos[$i]);
					} elseif (isset($nrc2)) {
						if ($elegido == $nrc2) {
							unset($elegidos_nuevos[$i]);
						}
					}
				}
				$elegidos_nuevos = array_values($elegidos_nuevos);

				$elegidos_nuevos[] = $aleatorio1;
				if (isset($aleatorio2)) {
					$elegidos_nuevos[] = $aleatorio2;
				}

				$feroArreglo = CursosController::aumentarFeromonas($elegidos_nuevos, $distancias, $feromonas);
				$feromonas = $feroArreglo[0];
				$cadenas = $feroArreglo[1];

				if ($cadenas) {
					continue;
				}

				$elegidos = $elegidos_nuevos;
				$semana = $perturbada;

				$iteraciones -= 1;
			}

			foreach ($nombres as $nombre) {
				foreach ($cursos[$nombre]['nrc'] as $nrc => $info_nrc) {
					$puntos[] = ['nrc' => $nrc, 'nombre' => $nombre];
				}
			}

			while ($repeticiones > 0) {
				$elegidos = []; // reset $elegidos
				$nombresX = []; // reset $nombresX
				$descartados = []; // reset $descartados

				// En este punto los nombres llegan completos

				$semana = CursosController::obtenerSemana(); // reset $semana
				$nombreRamdom = array_rand(array_flip($nombres)); // Un nombre al azar de $nombres - No afecta nada
				$nrcRamdom = array_rand(array_flip(array_keys($cursos[$nombreRamdom]['nrc']))); // Se toma un NRC al azar del nombre random - No afecta nada

				$infoNrc = $cursos[$nombreRamdom]['nrc'][$nrcRamdom]; // Información del NRC random - No afecta nada
				$listaDias = $infoNrc['dias']; // Días del NRC random - No afecta nada
				$seccion = $infoNrc['seccion']; // sección del NRC random - No afecta nada

				if (substr($seccion, -1) == "1" or substr($seccion, -1) == "2") {
					continue;
				} else {

					$ultimo_dia = CursosController::endKey($listaDias, 2); // Elige el último día de la lista de días - No afecta nada

					foreach ($listaDias as $dia => $infoDia) {

						$horas = $infoDia['horas']; // Trae las horas de los días del NRC - No afecta nada
						$validarNrc = CursosController::validarNrc($semana, $dia, $horas, $nrcRamdom); // Verifica si el NRC Ramdom calza en la semana - No afecta nada
						$semana = $validarNrc[1]; // Se actualiza la semana - No afecta nada

						if ($dia == $ultimo_dia) {
							$elegidos[] = $nrcRamdom; // Se agrega el NRC Random a los elegidos - No afecta nada
						}
					}
				}

				$nombresX[] = $nombreRamdom; // Se agrega el nombre random en los nombres de curso descartados - No afecta nada
				$descartados[] = $nrcRamdom; // Se agrega el Nrc random en los nrc descartados - No afecta nada

				for ($i = 0; $i < count($nombres); $i++) { // Inicialmente $i = 0 - No afecta nada

					$numeradores = []; // Reset $numeradores - No afecta nada
					$probabilidades = []; // Reset $probabilidades - No afecta nada
					$acomuladas = []; // Reset $acolumnadas - No afecta nada

					foreach ($puntos as $punto) {

						if (in_array($punto['nombre'], $nombresX)) { // Se verifica correctamente si el nombre del punto está en los nombres descartados - No afecta nada
							if (!in_array($punto['nrc'], $descartados)) { // Si hay un punto con un nombre descartado lo agrega a la lista de nombres descartados - No afecta nada
								$descartados[] = $punto['nrc']; // Se agregan los puntos a los nrc descartados - No afecta nada
							}
						} else {

							// $estadoCruce nos dice si se cruzan (true) o no (false) - No afecta nada
							$estadoCruce = CursosController::verificarCruce($cursos, $semana, $punto['nrc'], $punto['nombre']);

							if ($estadoCruce) {
								if (!in_array($punto['nrc'], $descartados)) { // Si se cruza el nrc se verifica si está en los descartados - No afecta nada
									$descartados[] = $punto['nrc']; // Se agrega a los descartados - No afecta nada
								}
							} else { // Si no se cruza...
								$ultimoElegido = end($elegidos); // Se toma el último de la lista de los elegidos - No afecta nada
								$valorFeromona = $feromonas[$ultimoElegido][$punto['nrc']]; // Se toma el valor en la matriz de feromonas - No afecta nada
								$valorLocal = $locales[$ultimoElegido][$punto['nrc']]; // Se toma el valor en la matriz de heurística local - No afecta nada
								$numerador = $valorFeromona * $valorLocal; // Los numeradores son la multiplicación de los dos valores anteriores - No afecta nada

								$numeradores[$punto['nrc']] = $numerador; // Se agrega el valor a los numeradores - No afecta nada
							}
						}
					}

					$denominador = array_sum($numeradores); // Los numeradores se suman correctamente - No afecta nada

					foreach ($numeradores as $candidato => $numerador) {

						if ($denominador == 0) {
							$probabilidad = 0;
						} else {
							$probabilidad = $numerador / $denominador;
						}

						$probabilidades[$candidato] = $probabilidad;
					}

					$suma = 0;
					foreach ($probabilidades as $candidato => $probabilidad) {
						$suma += $probabilidad;
						$acomuladas[$candidato] = $suma;
					}

					$n = (float)rand() / (float)getrandmax();

					foreach ($acomuladas as $candidato => $acomulado) {

						if ($n < $acomulado) {

							$name = CursosController::nombreNrc($candidato);
							$infoNrc = $cursos[$name]['nrc'][$candidato];
							$listaDias = $infoNrc['dias'];
							$seccion = $infoNrc['seccion'];

							if (substr($seccion, -1) == "1" or substr($seccion, -1) == "2") {
								continue;
							} else {

								$ultimo_dia = CursosController::endKey($listaDias, 2);
								foreach ($listaDias as $dia => $infoDia) {

									$horas = $infoDia['horas'];
									$validarNrc = CursosController::validarNrc($semana, $dia, $horas, $candidato);
									$semana = $validarNrc[1];

									if ($dia == $ultimo_dia) {
										$elegidos[] = $candidato;
									}
								}
							}

							if (!in_array($name, $nombresX)) {
								$nombresX[] = $name;
							}

							if (!in_array($candidato, $descartados)) {
								$descartados[] = $candidato;
							}

							break;
						}
					}
				}

				$feroArreglo = CursosController::aumentarFeromonas($elegidos, $distancias, $feromonas);
				$feromonas = $feroArreglo[0];
				$cadenas = $feroArreglo[1];

				if ($cadenas) {
					continue;
				}

				$feromonas = CursosController::evaporarFeromonas($feromonas);

				$arrayHuecos = CursosController::contarHuecos($semana);
				$huecosNuevos = array_sum($arrayHuecos);

				if ($numero_elegidos != 1) {
					if (count($elegidos) == $numero_elegidos) {
						if ($huecos == 0) {
							$huecos = $huecosNuevos;
							$elegidos_def = $elegidos;
							$semana_def = $semana;
						} elseif ($huecosNuevos < $huecos) {
							$huecos = $huecosNuevos;
							$elegidos_def = $elegidos;
							$semana_def = $semana;
						}
					}
				} else {
					$elegidos_def = $elegidos;
					$semana_def = $semana;
				}

				$repeticiones -= 1;
			}

			$semana = $semana_def;
			$elegidos = $elegidos_def;

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

		}

		$huecos_zx = CursosController::contarHuecos($semana);
		$zx = array_sum($huecos_zx);
		dd($zx, $time);
		return view('resultado', ['cursos' => $cursos, 'filas' => $filas, 'definitivos' => $definitivos, 'sem' => $sem]);
	}
}
