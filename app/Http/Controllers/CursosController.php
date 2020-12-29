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

	public function obtenerLabs()
	{
		$consulta = DB::select("select distinct Nombre_asignatura from cursos where Seccion like '%1' or Seccion like '%2'");

		foreach ($consulta as $curso) {
			$laboratorios[] = $curso->Nombre_asignatura;
		}

		return $laboratorios;
	}

	public function tieneLab($nombreCurso)
	{
		$laboratorios = CursosController::obtenerLabs();

		foreach ($laboratorios as $nombreLab) {
			if ($nombreLab == $nombreCurso) {
				$tieneLab = true;
				break;
			} else {
				$tieneLab = false;
			}
		}

		return $tieneLab;
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

	public function hill_climbing(Request $request)
	{
		$nombres = $request->input('nombres');
		$cursos = CursosController::obtenerCursos();
		$semana = CursosController::obtenerSemana();
		$laboratorios = CursosController::obtenerLabs(); // Posiblemente se puede quitar
		$iteraciones = 500;
		$cruzados = [];
		$elegidos = [];
		$elegidos_labs = [];

		foreach ($nombres as $nombre) {

			$tieneLab = CursosController::tieneLab($nombre);
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
								if (!$tieneLab) {
									$elegidos[] = $nrc;
								}
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

						if ($tieneLab) {
							$nrc_labs = [];

							$seleccion = DB::select("select Nrc from cursos where Seccion Like '" . $seccion . "%' and (Seccion like '%1' or Seccion like '%2') and Nombre_asignatura = '" . $nombre . "'");

							foreach ($seleccion as $clave => $valor) {
								$nrc_labs[] = $valor->Nrc;
							}

							// Sabemos cuáles son los labs pero no sabemos si calzan en la semana
							$labs_aceptados = false;

							foreach ($nrc_labs as $nrc_lab) {
								// NRC aceptado
								$aceptado_lab = false;

								// Último NRC
								$ultimo_nrc_lab = end($nrc_labs);

								// Último día del NRC
								$ultimo_dia_lab = CursosController::endKey($cursos[$nombre][$nrc_lab], 2);

								foreach ($cursos[$nombre][$nrc_lab] as $dia => $val2) {

									if ($dia != 'materia' and $dia != 'curso' and $dia != 'seccion' and $dia != 'capacidad' and $dia != 'disponibles' and $dia != 'ocupados' and $dia != 'codigo_docente' and $dia != 'docente' and $dia != 'tipo') {

										$horas = $cursos[$nombre][$nrc_lab][$dia]['horas'];

										for ($i = 1; $i <= count($horas); $i++) {

											if ($i % 2 != 0) {

												$inicio = $i - 1;
												continue;
											} else {

												$final = $i - 1;

												for ($j = $horas[$inicio]; $j <= $horas[$final]; $j++) {

													if (empty($semana[$dia][$j]) or $semana[$dia][$j] == $nrc_lab) {

														$semana[$dia][$j] = $nrc_lab;

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

										if ($valido) {
											if ($dia == $ultimo_dia_lab) {
												$aceptado_lab = true;

												if ($nrc_lab == $ultimo_nrc_lab) {
													$labs_aceptados = true;
													break; // break 1 inicio
												}

												break; // Igual al break 1
											}
										} else {

											// SE ESTÁ EVALUANDO EL CASO EN EL QUE NO ES ACEPTADO UN LABORATORIO
											// PRIMERO SE VA A VER SI DE UNA VEZ ES RECHAZADO.
											// LUEGO SI ES ACEPTADO EL PRIMERO Y RECHAZADO EL SEGUNDO

											// Se quita el NRC Lab de toda la semana
											foreach ($semana as $day => $hours) {
												foreach ($hours as $hour => $time) {
													if ($time == $nrc_lab) {
														$semana[$day][$hour] = '';
													}
												}
											}

											if ($nrc_lab == $ultimo_nrc_lab) {
												// Se quita el NRC de toda la semana
												foreach ($semana as $day => $hours) {
													foreach ($hours as $hour => $time) {
														if ($time == $nrc) {
															$semana[$day][$hour] = '';
														}
													}
												}
											}

											break; // Igual al break 1
										}
									}
								}
								// break 1 final

								// if ($aceptado_lab) {
								//     break; // break 2 inicio
								// }
							}

							// Si todos los laboratorios del NRC fueron aceptados se agregan a al lista de elegidos
							if ($labs_aceptados) {
								foreach ($nrc_labs as $nrc_lab) {
									$elegidos_labs[] = $nrc_lab;
								}
								$elegidos[] = $nrc;
							} else {
								continue;
							}
						}

						break;
					}

					if ($nrc == $ultimo_nrc) {
						$cruzados[] = $nombre;
					}
				}
			}
		}

		// Devolver al estudiante a la selección de cursos si alguno se cruza
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
				$nombre2 = CursosController::nombreNrc($nrc2);

				// Se quitan los NRC 1 y 2 de toda la semana perturbada
				foreach ($perturbada as $dia => $horas) {

					foreach ($horas as $hora => $nrc) {

						if ($nrc == $nrc1) {
							$perturbada[$dia][$hora] = '';
						} elseif (isset($nrc2)) {
							if ($nrc == $nrc2) {
								$perturbada[$dia][$hora] = '';
							}
						}
						// elseif (isset($nrc_labo1)) {
						// 	if ($nrc == $nrc_labo1) {
						// 		$perturbada[$dia][$hora] = '';
						// 	}
						// } elseif (isset($nrc_labo2)) {
						// 	if ($nrc == $nrc_labo2) {
						// 		$perturbada[$dia][$hora] = '';
						// 	}
						// }
					}
				}

				// Se comprueba si los NRC tienen laboratorio
				$corequisito1 = false;
				$corequisito2 = false;

				// // Tiene correquisito de laboratorio
				// foreach ($laboratorios as $curso) {
				// 	if ($curso == $nombre1) {
				// 		$corequisito1 = true;

				// 		foreach ($elegidos_labs as $elegido_lab1) {
				// 			$nombre_elegido = DB::select("select Nombre_asignatura from cursos where Nrc = '$elegido_lab1' limit 1")[0]->Nombre_asignatura;

				// 			if ($nombre_elegido == $nombre1) {
				// 				$nrc_labo1 = $elegido_lab1;
				// 			}
				// 		}
				// 	} elseif (isset($nrc2)) {
				// 		if ($curso == $nombre2) {
				// 			$corequisito2 = true;

				// 			foreach ($elegidos_labs as $elegido_lab2) {
				// 				$nombre_elegido = DB::select("select Nombre_asignatura from cursos where Nrc = '$elegido_lab2' limit 1")[0]->Nombre_asignatura;

				// 				if ($nombre_elegido == $nombre2) {
				// 					$nrc_labo2 = $elegido_lab2;
				// 				}
				// 			}
				// 		}
				// 	}
				// }

				// Se agregan los nuevos NRC de los cursos elegidos al azar
				while (true) {

					$aleatorio1 = array_rand(array_flip(array_keys($cursos[$nombre1])));

					if ($aleatorio1 != 'campus' and $aleatorio1 != 'fecha_inicio' and $aleatorio1 != 'creditos') {

						$seccion = $cursos[$nombre1][$aleatorio1]['seccion'];

						if (substr($seccion, -1) == "1" or substr($seccion, -1) == "2") {
							continue;
						} else {
							// NRC aceptado
							$aceptado1 = false;

							// $ultimo_nrc es aleatorio1 en este caso

							// Último día del NRC
							$ultimo_dia = CursosController::endKey($cursos[$nombre1][$aleatorio1], 2);

							// Aquí se están revisando todos los día de la semana del NRC aleatorio
							foreach ($cursos[$nombre1][$aleatorio1] as $dia => $val2) {

								if ($dia != 'materia' and $dia != 'curso' and $dia != 'seccion' and $dia != 'capacidad' and $dia != 'disponibles' and $dia != 'ocupados' and $dia != 'codigo_docente' and $dia != 'docente' and $dia != 'tipo') {

									$horas = $cursos[$nombre1][$aleatorio1][$dia]['horas'];

									for ($i = 1; $i <= count($horas); $i++) {

										if ($i % 2 != 0) {

											$inicio = $i - 1;
											continue;
										} else {

											$final = $i - 1;

											for ($j = $horas[$inicio]; $j <= $horas[$final]; $j++) {

												if (empty($perturbada[$dia][$j]) or $perturbada[$dia][$j] == $aleatorio1) {

													$perturbada[$dia][$j] = $aleatorio1;

													$valido = true;
												} else {

													$valido = false;
													break; // break 2 inicio
												}
											}
											// break 2 final
											if ($valido) {

												continue;
											} else {

												break; // break 3 inicio
											}
										}
									}
									// break 3 final
									if ($valido) {
										if ($dia == $ultimo_dia) {
											$aceptado1 = true;

											if (!$corequisito1) {
												$elegidos[] = $aleatorio1;
											}

											break; // break 1 inicio
										}
									} else {
										// Se quita el NRC de toda la semana
										foreach ($perturbada as $day => $hours) {
											foreach ($hours as $hour => $time) {
												if ($time == $aleatorio1) {
													$perturbada[$day][$hour] = '';
												}
											}
										}

										break; // break 1 inicio
									}
								}
							}
							// break 1 final

							if ($aceptado1) {

								if ($corequisito1) {
									$nrc_labs = [];

									$seleccion = DB::select("select Nrc from cursos where Seccion Like '" . $seccion . "%' and (Seccion like '%1' or Seccion like '%2') and Nombre_asignatura = '" . $nombre1 . "'");

									foreach ($seleccion as $clave => $valor) {
										$nrc_labs[] = $valor->Nrc;
									}

									foreach ($nrc_labs as $nrc_lab) {
										// NRC aceptado
										$aceptado_lab = false;

										// Último NRC
										$ultimo_nrc_lab = end($nrc_labs);

										// Último día del NRC
										$ultimo_dia_lab = CursosController::endKey($cursos[$nombre1][$nrc_lab], 2);

										foreach ($cursos[$nombre1][$nrc_lab] as $dia => $val2) {

											if ($dia != 'materia' and $dia != 'curso' and $dia != 'seccion' and $dia != 'capacidad' and $dia != 'disponibles' and $dia != 'ocupados' and $dia != 'codigo_docente' and $dia != 'docente' and $dia != 'tipo') {

												$horas = $cursos[$nombre1][$nrc_lab][$dia]['horas'];

												for ($i = 1; $i <= count($horas); $i++) {

													if ($i % 2 != 0) {

														$inicio = $i - 1;
														continue;
													} else {

														$final = $i - 1;

														for ($j = $horas[$inicio]; $j <= $horas[$final]; $j++) {

															if (empty($perturbada[$dia][$j]) or $perturbada[$dia][$j] == $nrc_lab) {

																$perturbada[$dia][$j] = $nrc_lab;

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

												if ($valido) {
													if ($dia == $ultimo_dia_lab) {
														$aceptado_lab = true;
														$elegidos_labs[] = $nrc_lab;
														$elegidos[] = $aleatorio1;
														break;
													}
												} else {

													// Se quita el NRC de toda la semana
													foreach ($perturbada as $day => $hours) {
														foreach ($hours as $hour => $time) {
															if ($time == $nrc_lab) {
																$perturbada[$day][$hour] = '';
															}
														}
													}

													if ($nrc_lab == $ultimo_nrc_lab) {
														// Se quita el NRC de toda la semana
														foreach ($perturbada as $day => $hours) {
															foreach ($hours as $hour => $time) {
																if ($time == $aleatorio1) {
																	$perturbada[$day][$hour] = '';
																}
															}
														}
													}

													break;
												}
											}
										}

										if ($aceptado_lab) {
											break;
										}
									}

									if (!$aceptado_lab) {
										continue;
									}
								}

								break; // break 2 inicio
							}

							if ($aleatorio1 == $ultimo_nrc) {
								$cruzados[] = $nombre1;
							}

							if (!$aceptado1) {
								// Se repite el while de la línea 380
								continue;
							}
						}
					} else {
						// Se repite el while de la línea 380
						continue;
					}

					if (isset($nombre2)) {
						$aleatorio2 = array_rand(array_flip(array_keys($cursos[$nombre2])));

						if ($aleatorio2 != 'campus' and $aleatorio2 != 'fecha_inicio' and $aleatorio2 != 'creditos') {
							// NRC aceptado
							$aceptado2 = false;

							// Último día del NRC
							$ultimo_dia = CursosController::endKey($cursos[$nombre2][$aleatorio2]);

							// Aquí se están revisando todos los día de la semana del NRC aleatorio
							foreach ($cursos[$nombre2][$aleatorio2] as $dia => $val2) {

								if ($dia != 'materia' and $dia != 'curso' and $dia != 'seccion' and $dia != 'capacidad' and $dia != 'disponibles' and $dia != 'ocupados' and $dia != 'codigo_docente' and $dia != 'docente' and $dia != 'tipo') {

									$horas = $cursos[$nombre2][$aleatorio2][$dia]['horas'];

									for ($i = 1; $i <= count($horas); $i++) {

										if ($i % 2 != 0) {

											$inicio = $i - 1;
											continue;
										} else {

											$final = $i - 1;

											for ($j = $horas[$inicio]; $j <= $horas[$final]; $j++) {

												if (empty($perturbada[$dia][$j]) or $perturbada[$dia][$j] == $aleatorio2) {

													$perturbada[$dia][$j] = $aleatorio2;

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

									if ($valido) {
										if ($dia == $ultimo_dia) {
											$aceptado2 = true;
											break;
										}
									} else {
										// Se quita el NRC de toda la semana
										foreach ($perturbada as $day => $hours) {
											foreach ($hours as $hour => $time) {
												if ($time == $aleatorio2) {
													$perturbada[$day][$hour] = '';
												}
											}
										}

										break;
									}
								}
							}

							if (!$aceptado2) {
								continue;
							}
						} else {
							continue;
						}
					}

					if (isset($aceptado2)) {
						if ($aceptado1 and $aceptado2) {
							break;
						} else {
							continue;
						}
					} else {
						if ($aceptado1) {
							break;
						} else {
							continue;
						}
					}
				}

				// // Borramos los NRC viejos de los elegidos
				// foreach ($elegidos as $i => $elegido) {

				// 	if ($elegido == $nrc1) {

				// 		unset($elegidos[$i]);
				// 	} elseif (isset($nrc2)) {

				// 		if ($elegido == $nrc2) {

				// 			unset($elegidos[$i]);
				// 		}
				// 	}
				// }

				// // Borramos los NRC viejos de los elegidos_labs
				// foreach ($elegidos_labs as $i => $elegido_lab) {

				// 	if (isset($nrc_labo1)) {
				// 		if ($elegido_lab == $nrc_labo1) {
				// 			unset($elegidos_labs[$i]);
				// 		}
				// 	}

				// 	if (isset($nrc_labo2)) {
				// 		if ($elegido_lab == $nrc_labo2) {
				// 			unset($elegidos_labs[$i]);
				// 		}
				// 	}
				// }

				// Borramos los NRC viejos de los elegidos
				foreach ($elegidos as $i => $elegido) {

					if ($elegido == $nrc1) {

						unset($elegidos[$i]);
					} elseif (isset($nrc2)) {

						if ($elegido == $nrc2) {

							unset($elegidos[$i]);
						}
					}
				}

				// Agregamos los NRC nuevos a los elegidos
				$elegidos[] = $aleatorio1;

				if (isset($aleatorio2)) {
					$elegidos[] = $aleatorio2;
				}



				// PASO 3: CALCULAR FUNCIÓN OBJETIVO ZX Y ZXP Y COMPARARLAS


				// Función objetivo con respecto a x
				$posiciones1 = [];
				$contador1 = 0;

				// Obtenemos las posiciones de los NRC en la semana
				foreach ($semana as $dia => $horas) {

					foreach ($horas as $hora => $nrc) {

						$contador1 += 1;

						if ($nrc) {

							$posiciones1[] = $contador1;
						}
					}
				}

				$zx = end($posiciones1) - $posiciones1[0];


				// Función objetivo con respecto a x perturbada
				$posiciones2 = [];
				$contador2 = 0;

				// Obtenemos las posiciones de los NRC en la semana perturbada
				foreach ($perturbada as $dia => $horas) {

					foreach ($horas as $hora => $nrc) {

						$contador2 += 1;

						if ($nrc) {

							$posiciones2[] = $contador2;
						}
					}
				}

				$zxp = end($posiciones2) - $posiciones2[0];


				if ($zxp < $zx) {
					// Actualizamos la semana
					$semana = $perturbada;
				}

				// Descontamos una iteración
				$iteraciones -= 1;
			}


			// Agregando los nombres de los cursos

			$definitivos = [];

			foreach ($semana as $dia => $horas) {
				foreach ($horas as $hora => $nrc) {
					if (!empty($nrc) and !in_array($nrc, $definitivos)) {
						$name = DB::select('select * from cursos where Nrc = "' . $nrc . '" limit 1');
						$name = $name[0]->Nombre_asignatura;

						$definitivos[$nrc] = $name;
					}
				}
			}


			// Ordenando para mostrar horario generado

			$filas = [];

			for ($i = 7; $i <= 20; $i++) {

				foreach ($semana as $dia => $horas) {

					$filas[$i][] = $semana[$dia][$i];
				}
			}

			return view('resultado', ['cursos' => $cursos, 'filas' => $filas, 'definitivos' => $definitivos]);
		}
	}
}
