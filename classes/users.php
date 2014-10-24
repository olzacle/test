<?php 
class Users{
 	
	private $db;

	public function __construct($database) {
	    $this->db = $database;
	}


	public function modelos_vendidos(){

		$modelos_reales=array('XT1053w' => TRUE ,'XT1053' => TRUE , 'XT621' => TRUE, 'FerrariLimitedEdit' => TRUE, 'XT621R' => TRUE, 'XT605' => TRUE ,'Moto G Forte' => TRUE ,'XT1022' => TRUE);
		$modelos=$this->db->prepare("SELECT DISTINCT (modelo) FROM valida WHERE pagar=0 AND modelo NOT LIKE '%bono%'");
		$modelos->execute();
		$salida="";
		while ($dato=$modelos->fetch()){
			
			if($modelos_reales[$dato['modelo']]){

				$catalogo=$this->db->prepare("SELECT nombre FROM catalogo WHERE modelo=?");
				$catalogo->bindValue(1, $dato['modelo']);
				$catalogo->execute();
				$detalle=$catalogo->fetch();
				$nombre_equipo=$detalle['nombre'];

				$modelo_especifico=$this->db->prepare("SELECT modelo FROM valida WHERE modelo=? AND pagar=0");
				$modelo_especifico->bindValue(1, $dato['modelo']);
				$modelo_especifico->execute();
				$total=$modelo_especifico->rowCount();
				$salida.="<tr>
							<td>".$dato['modelo']."</td>
							<td>".$nombre_equipo."</td>
							<td>".$total."</td>
				</tr>";
			}

		}

		return $salida;


	}

	public function usuarios_totales()
	{
		$tipos_usuario=array('ALE' => TRUE,'GDM' => TRUE,'GNT' => TRUE,'ICA' => TRUE,'RED' => TRUE,'ROALCOM' => TRUE,'SIB' => TRUE,'CC' => TRUE,'DC' => TRUE );
		$distribuidores=0;
		$normales=0;
		$total=0;
		$usuarios=$this->db->prepare("SELECT * FROM users");
		$usuarios->execute();
		while ($dato=$usuarios->fetch()){
			if($tipos_usuario[$dato['tipo']]){
				$distribuidores++;
			}
			else
			{
				$normales++;
			}
		}
		$total=$normales+$distribuidores;
		return $normales.",".$distribuidores.",".$total;
	}

	public function pesos_usuario_distribuidor(){
		$tipos_usuario=array('ALE' => TRUE,'GDM' => TRUE,'GNT' => TRUE,'ICA' => TRUE,'RED' => TRUE,'ROALCOM' => TRUE,'SIB' => TRUE,'CC' => TRUE,'DC' => TRUE );
		$pagos=$this->db->prepare("SELECT * FROM pagos");
		$pagos->execute();
		
		$total_pesos_pagados_usuario=0;
		$total_pesos_pendientes_usuario=0;
		$total_pesos_globales_usuario=0;

		$total_pesos_pagados_distribuidor=0;
		$total_pesos_pendientes_distribuidor=0;
		$total_pesos_globales_distribuidor=0;

		while ($dato=$pagos->fetch()){


					$usuario=$this->db->prepare("SELECT tipo FROM users WHERE cvendedor=?");
					$usuario->bindValue(1, $dato['cvendedor']);
					$usuario->execute();
					$fila=$usuario->fetch();

					if($tipos_usuario[$fila['tipo']])
					{
						if($dato['status']==0)
							$total_pesos_pendientes_distribuidor+=$dato['monto'];
						else if($dato['status']==1)
							$total_pesos_pagados_distribuidor+=$dato['monto'];
					}
					else
					{
						if($dato['status']==0)
							$total_pesos_pendientes_usuario+=$dato['monto'];
						else if($dato['status']==1)
							$total_pesos_pagados_usuario+=$dato['monto'];
					}


					
				}

		$total_pesos_globales_distribuidor=$total_pesos_pendientes_distribuidor+$total_pesos_pagados_distribuidor;
		$total_pesos_globales_usuario=$total_pesos_pendientes_usuario+$total_pesos_pagados_usuario;


		return $total_pesos_pagados_usuario.",".$total_pesos_pendientes_usuario.",".$total_pesos_globales_usuario."|".		$total_pesos_pagados_distribuidor.",".$total_pesos_pendientes_distribuidor.",".$total_pesos_globales_distribuidor;

	}

	public function tabla_usuarios_mes($mes,$ano){

		if($mes==0 && $ano==0)
		{
				$mes=date("m");
				$ano=date("Y");
		}
		
		$diafin=date("t",mktime(0,0,0,$mes,1,$ano));
		$fecha_inicio=date("Y-m-d 00:00:00",mktime(0,0,0,$mes,1,$ano));
		$fecha_final=date("Y-m-d 23:59:59",mktime(0,0,0,$mes,$diafin,$ano));

		$fecha_inicio=strtotime($fecha_inicio);
		$fecha_final=strtotime($fecha_final);

		$usuario=$this->db->prepare("SELECT cvendedor,nombre,apellido,email,tipo FROM users WHERE time BETWEEN ? AND ?");
		$usuario->bindValue(1, $fecha_inicio);
		$usuario->bindValue(2, $fecha_final);
		$usuario->execute();
		$tabla="";
		while ($dato=$usuario->fetch()){

					$tabla.="<tr>
						<td>".$dato['cvendedor']."</td>
						<td>".$dato['nombre']."</td>
						<td>".$dato['apellido']."</td>
						<td>".$dato['email']."</td>
						<td>".$dato['tipo']."</td>
					</tr>";
				}
		return $tabla;



	}

	public function ventas_registradas_mes_actual(){

			$tipos_usuario=array('ALE' => TRUE,'GDM' => TRUE,'GNT' => TRUE,'ICA' => TRUE,'RED' => TRUE,'ROALCOM' => TRUE,'SIB' => TRUE,'CC' => TRUE,'DC' => TRUE );


				$mes=date("n");
				$ano=date("Y");
				$diafin=date("t",mktime(0,0,0,$mes,1,$ano));
				$fecha_inicio=date("Y-m-d 00:00:00",mktime(0,0,0,$mes,1,$ano));
				$fecha_final=date("Y-m-d 23:59:59",mktime(0,0,0,$mes,$diafin,$ano));

			$ventas=$this->db->prepare("SELECT id,cvendedor FROM valida WHERE fecha BETWEEN '".$fecha_inicio."' AND '".$fecha_final."'");
			$ventas->execute();
			$distribuidor=0;
			$normales=0;
			while ($dato=$ventas->fetch()){
					$usuario=$this->db->prepare("SELECT tipo FROM users WHERE cvendedor='".$dato['cvendedor']."'");
					$usuario->execute();
					$row=$usuario->fetch();

					if($tipos_usuario[$row['tipo']])
						$distribuidor++;
					else
						$normales++;
				}
				$tabla=$normales."|".$distribuidor;

				return $tabla;

	}
	public function ventas_registradas_mes_anterior(){

			$tipos_usuario=array('ALE' => TRUE,'GDM' => TRUE,'GNT' => TRUE,'ICA' => TRUE,'RED' => TRUE,'ROALCOM' => TRUE,'SIB' => TRUE,'CC' => TRUE,'DC' => TRUE );


			$mesinicio=date("Y-m-d");
				$mes=date("n",strtotime('-1 month',strtotime($mesinicio)));
				$ano=date("Y",strtotime('-1 month'));
				$diafin=date("t",mktime(0,0,0,$mes,1,$ano));
				$fecha_inicio=date("Y-m-d 00:00:00",mktime(0,0,0,$mes,1,$ano));
				$fecha_final=date("Y-m-d 23:59:59",mktime(0,0,0,$mes,$diafin,$ano));

			$ventas=$this->db->prepare("SELECT id,cvendedor FROM valida WHERE fecha BETWEEN '".$fecha_inicio."' AND '".$fecha_final."'");
			$ventas->execute();
			$distribuidor=0;
			$normales=0;
			while ($dato=$ventas->fetch()){
					$usuario=$this->db->prepare("SELECT tipo FROM users WHERE cvendedor='".$dato['cvendedor']."'");
					$usuario->execute();
					$row=$usuario->fetch();

					if($tipos_usuario[$row['tipo']])
						$distribuidor++;
					else
						$normales++;
				}
				$tabla=$normales."|".$distribuidor;

				return $tabla;

	}

	public function tabla_usuarios_activos(){
				$usuario=$this->db->prepare("SELECT cvendedor,nombre,apellido,email,confirmed FROM users ORDER BY confirmed DESC");
				$usuario->execute();
				$tabla="";
				while ($dato=$usuario->fetch()){
					$background="gray";
					$estado="Inactivo";
					
					if($dato['confirmed']==1){
						$background="transparent";
						$estado="Activo";
					}

					$tabla.="<tr style='background-color:".$background."'>
						<td>".$dato['cvendedor']."</td>
						<td>".$dato['nombre']."</td>
						<td>".$dato['apellido']."</td>
						<td>".$dato['email']."</td>
						<td>".$estado."</td>
					</tr>";
				}
				return $tabla;


	}
	
	public function tabla_usuarios_cobranza(){
				$usuario=$this->db->prepare("SELECT cvendedor,nombre,apellido,email,clabe,cuenta,ife FROM users");
				$usuario->execute();
				$tabla="";
				while ($dato=$usuario->fetch()){
					$background="transparent";
					$estado = "Documentacion completa";

					if($dato['ife']=="")
					{
						$estado="Sin IFE";
						$background="gray";
					}
					if ($dato['clabe']=="") {
						
						if($dato['cuenta']=="")
						{
							$estado="No existen datos bancarios";
							$background="gray";
						}
					}

					$tabla.="<tr style='background-color:".$background."'>
						<td>".$dato['cvendedor']."</td>
						<td>".$dato['nombre']."</td>
						<td>".$dato['apellido']."</td>
						<td>".$dato['email']."</td>
						<td>".$estado."</td>
					</tr>";
				}
				return $tabla;


	}

	public function usuarios_mes_actual(){

				$mes=date("n");
				$ano=date("Y");
				$diafin=date("t",mktime(0,0,0,$mes,1,$ano));
				$fecha_inicio=date("Y-m-d 00:00:00",mktime(0,0,0,$mes,1,$ano));
				$fecha_final=date("Y-m-d 23:59:59",mktime(0,0,0,$mes,$diafin,$ano));

				$fecha_inicio=strtotime($fecha_inicio);
				$fecha_final=strtotime($fecha_final);

				$usuario=$this->db->prepare("SELECT cvendedor FROM users WHERE time BETWEEN ? AND ?");
				$usuario->bindValue(1, $fecha_inicio);
				$usuario->bindValue(2, $fecha_final);
				$usuario->execute();

				return $usuario->rowCount();

	}
	public function usuarios_mes_anterior(){
				$mesinicio=date("Y-m-d");
				$mes=date("n",strtotime('-1 month',strtotime($mesinicio)));
				$ano=date("Y",strtotime('-1 month'));
				$diafin=date("t",mktime(0,0,0,$mes,1,$ano));
				$fecha_inicio=date("Y-m-d 00:00:00",mktime(0,0,0,$mes,1,$ano));
				$fecha_final=date("Y-m-d 23:59:59",mktime(0,0,0,$mes,$diafin,$ano));

				$fecha_inicio=strtotime($fecha_inicio);
				$fecha_final=strtotime($fecha_final);

				$usuario=$this->db->prepare("SELECT cvendedor FROM users WHERE time BETWEEN ? AND ?");
				$usuario->bindValue(1, $fecha_inicio);
				$usuario->bindValue(2, $fecha_final);
				$usuario->execute();

				return $usuario->rowCount();

	}

	public function perfil_ventas_modelo_mes($id,$mes_entrada)
	{
		$link=mysql_connect("localhost","motobene_usuario","socio00");
		mysql_select_db("motobene_usuario");
		$cadena="";

		$meses= array('', 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre' );


		$usuario=$this->db->prepare("SELECT cvendedor,tipo FROM users WHERE id=?");
		$usuario->bindValue(1, $id);
		$usuario->execute();
		$row=$usuario->fetch();


		$vendedor=$row['cvendedor'];
		$tipo=$row['tipo'];
		$grafico="";



				$mesinicio=date("Y-".$mes_entrada."-d");
				$mes=date("n",strtotime('-1 month',strtotime($mesinicio)));
				$ano=date("Y",strtotime('-1 month'));
				$diafin=date("t",mktime(0,0,0,$mes,1,$ano));
				$fecha_inicio=date("Y-m-d 00:00:00",mktime(0,0,0,$mes,1,$ano));
				$fecha_final=date("Y-m-d 23:59:59",mktime(0,0,0,$mes,$diafin,$ano));


				

				$ventas="SELECT DISTINCT valida.modelo FROM valida,catalogo WHERE valida.modelo=catalogo.modelo AND cvendedor='".$vendedor."' AND fecha BETWEEN '".$fecha_inicio."' AND '".$fecha_final."' ORDER BY catalogo.modelo ASC";
				$resultado_ventas=mysql_query($ventas);



				$totalfinal=0;
				$totalpagado=0;
				$totalporpagar=0;
				$modelos='';

				if(mysql_num_rows($resultado_ventas)>0)
				{	
						while ($row=mysql_fetch_array($resultado_ventas)) {

							$puntostotales=0;

							$detalle="SELECT * FROM valida,catalogo WHERE valida.modelo=catalogo.modelo AND fecha BETWEEN '".$fecha_inicio."' AND '".$fecha_final."'  AND cvendedor='".$vendedor."' AND valida.modelo='".$row['modelo']."'";
							$resultado_detalle=mysql_query($detalle);

							$unidades_vendidas=mysql_num_rows($resultado_detalle);

							$puntosnormales=0;
							$puntospromocion=0;
							$unidadesnormales=0;
							$unidadespromocion=0;

							while ($rowdetalle=mysql_fetch_array($resultado_detalle)) {
										

									$imagen=$rowdetalle['imagen'];
									$nombre=$rowdetalle['nombre'];
									$cobrado=$rowdetalle['cobrado'];
									$query="SELECT puntos FROM promo WHERE tipo='$tipo' AND modelo='".$row['modelo']."' AND inicio<='".$rowdetalle['fecha']."' AND final>='".$rowdetalle['fecha']."'";
									$resultado=mysql_query($query);
									if(mysql_num_rows($resultado)>0)
									{
										$totalfinal+=$promodetalle['puntos'];	
									}
									else
									{
										$totalfinal+=$rowdetalle['puntos'];
									}

							}

							$modelos.='<tr><td>'.$nombre.'</td><td>'.$unidades_vendidas.'</td></tr>';


					}



						$grafico.='<div class="col-md-4">
							<div class="reporte">
							<table>
								<thead>
								<tr style="background: transparent !important;">
									<th colspan="2">'.$meses[$mes].'</th>
								</tr>
								</thead>
								
								<tbody>
									<tr><td>Saldo</td><td>$ '.number_format($totalfinal,2,'.',',').'</td></tr>
									<tr><td>Modelo</td><td>Unidades vendidas</td></tr>
									'.$modelos.'
								</tbody>
							</table>
							</div>

						</div>';
				}
				else
				{

						$grafico.='<div class="col-md-4">
							<div class="reporte">
							<table>
								<thead>
								<tr style="background: transparent !important;">
									<th colspan="2">'.$meses[$mes].'</th>
								</tr>
								</thead>
								
								<tbody>
									<tr><td>Saldo</td><td>$ 0</td></tr>
									<tr><td>Ningun modelo vendido</td></tr>
								</tbody>
							</table>

							</div>
						</div>';
				}


		return $grafico;

	}


	public function perfil_ventas_mes($id,$mes_entrada)
	{
		
		
		$link=mysql_connect("localhost","motobene_usuario","socio00");
		mysql_select_db("motobene_usuario");
		$cadena="";

		$meses= array('', 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre' );


		$usuario=$this->db->prepare("SELECT cvendedor,tipo FROM users WHERE id=?");
		$usuario->bindValue(1, $id);
		$usuario->execute();
		$row=$usuario->fetch();


		$vendedor=$row['cvendedor'];
		$tipo=$row['tipo'];
		$grafico="";

		for ($r=3; $r >=1 ; $r--) { 


				$mesinicio=date("Y-".$mes_entrada."-d");
				$mes=date("n",strtotime('-'.$r.' month',strtotime($mesinicio)));
				$ano=date("Y",strtotime('-'.$r.' month'));
				$diafin=date("t",mktime(0,0,0,$mes,1,$ano));
				$fecha_inicio=date("Y-m-d 00:00:00",mktime(0,0,0,$mes,1,$ano));
				$fecha_final=date("Y-m-d 23:59:59",mktime(0,0,0,$mes,$diafin,$ano));


				

				$ventas="SELECT DISTINCT valida.modelo FROM valida,catalogo WHERE valida.modelo=catalogo.modelo AND cvendedor='".$vendedor."' AND fecha BETWEEN '".$fecha_inicio."' AND '".$fecha_final."' ORDER BY catalogo.modelo ASC";
				$resultado_ventas=mysql_query($ventas);



				$totalfinal=0;
				$totalpagado=0;
				$totalporpagar=0;

				if(mysql_num_rows($resultado_ventas)>0)
				{	
						while ($row=mysql_fetch_array($resultado_ventas)) {

							$puntostotales=0;

							$detalle="SELECT * FROM valida,catalogo WHERE valida.modelo=catalogo.modelo AND fecha BETWEEN '".$fecha_inicio."' AND '".$fecha_final."'  AND cvendedor='".$vendedor."' AND valida.modelo='".$row['modelo']."'";
							$resultado_detalle=mysql_query($detalle);

							$puntosnormales=0;
							$puntospromocion=0;
							$unidadesnormales=0;
							$unidadespromocion=0;

							while ($rowdetalle=mysql_fetch_array($resultado_detalle)) {
										

									$imagen=$rowdetalle['imagen'];
									$nombre=$rowdetalle['nombre'];
									$cobrado=$rowdetalle['cobrado'];
									$query="SELECT puntos FROM promo WHERE tipo='$tipo' AND modelo='".$row['modelo']."' AND inicio<='".$rowdetalle['fecha']."' AND final>='".$rowdetalle['fecha']."'";
									$resultado=mysql_query($query);
									



									if(mysql_num_rows($resultado)>0)
									{
										$promodetalle=mysql_fetch_array($resultado);
										$totalfinal+=$promodetalle['puntos'];	
										$puntostotales+=$promodetalle['puntos'];
										$puntospromocion+=$promodetalle['puntos'];
										$unidadespromocion++;
										if($cobrado==1)
										{
											$totalpagado+=$promodetalle['puntos'];
										}
										else
										{
											$totalporpagar+=$promodetalle['puntos'];
										}


									}
									else
									{
										$totalfinal+=$rowdetalle['puntos'];
										$puntostotales+=$rowdetalle['puntos'];
										$puntosnormales+=$rowdetalle['puntos'];
										$unidadesnormales++;
										if($cobrado==1)
										{
											$totalpagado+=$rowdetalle['puntos'];
										}
										else
										{
											$totalporpagar+=$rowdetalle['puntos'];
										}

									}

							}


					}
						$grafico.='<div class="col-md-4">
							<div class="reporte">
							<table>
								<thead>
								<tr style="background: transparent !important;">
									<th colspan="2"><a href="javascript:window.open(\'detalle_usuario_mes.php?id='.$id.'&mes='.$mes.'&ano='.$ano.'\', \'_blank\', \'toolbar=yes, scrollbars=yes, resizable=yes, top=500, left=500, width=800, height=400\');">'.$meses[$mes].'</a></th>
								</tr>
								</thead>
								
								<tbody>
									<tr><td>Saldo</td><td>$ '.number_format($totalfinal,2,'.',',').'</td></tr>
									<tr><td>Cobrado</td><td> $ '.number_format($totalpagado,2,'.',',').'</td></tr>
									<tr><td>Disponible</td><td>$ '.number_format($totalporpagar,2,'.',',').'</td></tr>
								</tbody>
							</table>
							</div>

						</div>';
				}
				else
				{

						$grafico.='<div class="col-md-4">
							<div class="reporte">
							<table>
								<thead>
								<tr style="background: transparent !important;">
									<th colspan="2">'.$meses[$mes].'</th>
								</tr>
								</thead>
								
								<tbody>
									<tr><td>Saldo</td><td>$ 0</td></tr>
									<tr><td>Cobrado</td><td> $ 0</td></tr>
									<tr><td>Disponible</td><td>$ 0</td></tr>
								</tbody>
							</table>

							</div>
						</div>';
				}

		}

		return $grafico;

	}



	public function detalle_vendedor_mes($id,$mes,$ano)
	{
		$link=mysql_connect("localhost","motobene_usuario","socio00");
		mysql_select_db("motobene_usuario");
		$cadena="";

		$usuario=$this->db->prepare("SELECT cvendedor,tipo FROM users WHERE id=?");
		$usuario->bindValue(1, $id);
		$usuario->execute();
		$row=$usuario->fetch();


		$vendedor=$row['cvendedor'];
		$tipo=$row['tipo'];

		$diafin=date("t",mktime(0,0,0,$mes,1,$ano));
		$fecha_inicio=date("Y-m-d 00:00:00",mktime(0,0,0,$mes,1,$ano));
		$fecha_final=date("Y-m-d 23:59:59",mktime(0,0,0,$mes,$diafin,$ano));




		$query="SELECT DISTINCT valida.modelo FROM valida,catalogo WHERE valida.modelo=catalogo.modelo AND cvendedor='".$vendedor."' AND fecha BETWEEN '".$fecha_inicio."' AND '".$fecha_final."' ORDER BY catalogo.modelo ASC";
		$resultadofechas=mysql_query($query);


		

		$totalfinal=0;
		$totalpagado=0;

		if(mysql_num_rows($resultadofechas)>0)
		{	
				while ($row=mysql_fetch_array($resultadofechas)) {

					$puntostotales=0;
					$detalle=$this->db->prepare("SELECT * FROM valida,catalogo WHERE valida.modelo=catalogo.modelo AND cvendedor=? AND valida.modelo=? AND fecha BETWEEN '".$fecha_inicio."' AND '".$fecha_final."'");
					

					$detalle->bindValue(1,$vendedor);
					$detalle->bindValue(2,$row['modelo']);

					$detalle->execute();

					$puntosnormales=0;
					$puntospromocion=0;
					$unidadesnormales=0;
					$unidadespromocion=0;

					while ($rowdetalle=$detalle->fetch()) {
								

							$imagen=$rowdetalle['imagen'];
							$nombre=$rowdetalle['nombre'];
							$cobrado=$rowdetalle['cobrado'];
							$query="SELECT puntos FROM promo WHERE tipo='$tipo' AND modelo='".$row['modelo']."' AND inicio<='".$rowdetalle['fecha']."' AND final>='".$rowdetalle['fecha']."'";
							$resultado=mysql_query($query);
							



							if(mysql_num_rows($resultado)>0)
							{
								$promodetalle=mysql_fetch_array($resultado);
								$totalfinal+=$promodetalle['puntos'];	
								$puntostotales+=$promodetalle['puntos'];
								$puntospromocion+=$promodetalle['puntos'];
								$unidadespromocion++;

								$textopuntospromo=$promodetalle['puntos'];
								$textopuntosnormal=0;


								if($cobrado==1)
								{
									$textocobrado="COBRADO";
									$totalpagado+=$promodetalle['puntos'];
								}
								else
								{
									$textocobrado="DISPONIBLE";
									$totalporpagar+=$promodetalle['puntos'];
								}


							}
							else
							{
								$totalfinal+=$rowdetalle['puntos'];
								$puntostotales+=$rowdetalle['puntos'];
								$puntosnormales+=$rowdetalle['puntos'];
								$unidadesnormales++;

								$textopuntospromo=0;
								$textopuntosnormal=$rowdetalle['puntos'];

								if($cobrado==1)
								{
									$textocobrado="COBRADO";
									$totalpagado+=$rowdetalle['puntos'];
								}
								else
								{
									$textocobrado="DISPONIBLE";
									$totalporpagar+=$rowdetalle['puntos'];
								}

							}

							$cadena.='
							<tr>
								<td>'.$rowdetalle['folio'].'</td>
								<td>'.$rowdetalle['modelo'].'</td>
								<td>'.$rowdetalle['fecha'].'</td>
								<td>'.$textopuntosnormal.'</td>
								<td>'.$textopuntospromo.'</td>
								<td>'.$textocobrado.'</td>
							</tr>

					';
									


					}

					


			}
				return $cadena;
		}
		else
			return "No hay registros de ventas";



	}

	public function lista_usuarios_mes($mes,$ano)
	{
		$link=mysql_connect("localhost","motobene_usuario","socio00");
		mysql_select_db("motobene_usuario");
		$cadena="";


		$todoslosusuarios=$this->db->prepare("SELECT * FROM users");
		$todoslosusuarios->execute();

		while ($elid=$todoslosusuarios->fetch()) {

				$totalfinal=0;
				$totalpagado=0;
				$totalporpagar=0;

				$usuario=$this->db->prepare("SELECT cvendedor,tipo FROM users WHERE id=?");
				$usuario->bindValue(1, $elid['id']);
				$usuario->execute();
				$row=$usuario->fetch();


				$vendedor=$row['cvendedor'];
				$tipo=$row['tipo'];


				$diafin=date("t",mktime(0,0,0,$mes,1,$ano));
				$fecha_inicio=date("Y-m-d 00:00:00",mktime(0,0,0,$mes,1,$ano));
				$fecha_final=date("Y-m-d 23:59:59",mktime(0,0,0,$mes,$diafin,$ano));




				$query="SELECT DISTINCT valida.modelo FROM valida,catalogo WHERE valida.modelo=catalogo.modelo AND cvendedor='".$vendedor."' AND fecha BETWEEN '".$fecha_inicio."' AND '".$fecha_final."' ORDER BY catalogo.modelo ASC";
				$resultadofechas=mysql_query($query);


				

				if(mysql_num_rows($resultadofechas) >0)
				{	
						while ($row=mysql_fetch_array($resultadofechas)) {

							$puntostotales=0;
							$detalle=$this->db->prepare("SELECT * FROM valida,catalogo WHERE valida.modelo=catalogo.modelo AND cvendedor=? AND valida.modelo=? AND fecha BETWEEN '".$fecha_inicio."' AND '".$fecha_final."'");
							

							$detalle->bindValue(1,$vendedor);
							$detalle->bindValue(2,$row['modelo']);

							$detalle->execute();

							$puntosnormales=0;
							$puntospromocion=0;
							$unidadesnormales=0;
							$unidadespromocion=0;

							while ($rowdetalle=$detalle->fetch()) {
										

									$imagen=$rowdetalle['imagen'];
									$nombre=$rowdetalle['nombre'];
									$cobrado=$rowdetalle['cobrado'];
									$query="SELECT puntos FROM promo WHERE tipo='$tipo' AND modelo='".$row['modelo']."' AND inicio<='".$rowdetalle['fecha']."' AND final>='".$rowdetalle['fecha']."'";
									$resultado=mysql_query($query);
									



									if(mysql_num_rows($resultado)>0)
									{
										$promodetalle=mysql_fetch_array($resultado);
										$totalfinal+=$promodetalle['puntos'];	
										$puntostotales+=$promodetalle['puntos'];
										$puntospromocion+=$promodetalle['puntos'];
										$unidadespromocion++;
										if($cobrado==1)
										{
											$totalpagado+=$promodetalle['puntos'];
										}
										else
										{
											$totalporpagar+=$promodetalle['puntos'];
										}


									}
									else
									{
										$totalfinal+=$rowdetalle['puntos'];
										$puntostotales+=$rowdetalle['puntos'];
										$puntosnormales+=$rowdetalle['puntos'];
										$unidadesnormales++;
										if($cobrado==1)
										{
											$totalpagado+=$rowdetalle['puntos'];
										}
										else
										{
											$totalporpagar+=$rowdetalle['puntos'];
										}

									}

							}

							


					}

					



						
				}
				else
				{
						$totalporpagar=0;
						$totalporpagar=0;
						$totalfinal=0;
				}


				$cadena.='<tr>
						<td><a href="detalle_usuario_mes.php?id='.$elid['id'].'&mes='.$mes.'&ano='.$ano.'" target="_blank">'.$elid['cvendedor'].'</a></td>
						<td>'.$elid['nombre'].'</td>
						<td>'.$elid['apellido'].'</td>
						<td>'.$elid['email'].'</td>
						<td>'.$elid['tipo'].'</td>
						<td>'.$totalpagado.'</td>
						<td>'.$totalporpagar.'</td>
						<td>'.$totalfinal.'</td>
				</tr>';
		}

			return $cadena;
	}

	public function genera_tabla_pagos_general($id)
	{
		$filepath='reporte_final.csv';
		$fp=fopen($filepath, "w+");
		
		fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

		$cabecera = array('Codigo de vendedor','Nombre','Apellido','E-mail','RFC','CURP','Banco','Cuenta','CLABE','IFE','Monto','Status','Fecha');
		fputcsv($fp, $cabecera);


		$solicitud=$this->db->prepare("SELECT * FROM pagos");
		$solicitud->execute();

		$cadena="";

		
		while($datos=$solicitud->fetch())
		{
			
			if($datos['status']==0)
			{
				$estado="PENDIENTE";
				$botones='<div id="botones'.$datos['id'].'"><button class="status_ok" onclick="ir(\'../aprobar.php?id='.$datos['id'].'\',\'divi'.$datos['id'].'\');"></button><button class="status_no"  onclick="aparecer('.$datos['id'].');"></button></div>
				<div id="motivos'.$datos['id'].'" style="display:none">

				<select id="selectmotivos'.$datos['id'].'" onchange="verifica_motivo('.$datos['id'].');">
					<option value="Por captura incorrecta de datos en cuenta / clabe bancaria">Por captura incorrecta de datos en cuenta / clabe bancaria
					<option value="Carga incorrecta de IFE">Carga incorrecta de IFE 
					<option value="otro">Otro
				</select>
				<textarea id="otros_motivos'.$datos['id'].'" style="display:none"></textarea>
				<button onclick="envia_cancelacion('.$datos['id'].')">Enviar</button>

				</div>';
			}
			if($datos['status']==1)
			{
				$estado="APROBADO";
				$botones="";
			}
			if($datos['status']==2)
			{
				$estado="RECHAZADO";
				$botones="";
			}

		

			$cadena.="<tr>
							<td>".$datos['cvendedor']."</td>
							<td>".utf8_encode($datos['nombre'])."</td>
							<td>".utf8_encode($datos['apellido'])."</td>
							<td>".$datos['email']."</td>
							<td>".$datos['rfc']."</td>
							<td>".$datos['curp']."</td>
							<td>".$datos['banco']."</td>
							<td>".$datos['cuenta']."</td>
							<td>".$datos['clabe']."</td>
							<td><a target='_blank' href='../detalle_pago.php?id=".$datos['id']."'>".$datos['image_ife']."</a></td>
							<td>$ ".number_format($datos['monto'],2,'.',',')."</td>
							<td><div id='divi".$datos['id']."'>".$estado."</div></td>
							<td><div id='moti".$datos['id']."'>".$datos['motivo']."</div></td>
							<td>".$datos['fecha']."</td>
							<td>".$botones."</td>
						</tr>";
					$fields = array($datos['cvendedor'],$datos['nombre'],$datos['apellido'],$datos['email'],$datos['rfc'],$datos['curp'],$datos['banco'],$datos['cuenta'],$datos['clabe'],utf8_encode($datos['image_ife']),"$ ".number_format($datos['monto'],2,'.',','),$estado,$datos['fecha']);
					fputcsv($fp, $fields);
					unset($fields);

		}

		fclose($fp);
		return $cadena;

	}



	public function genera_tabla_pagos($id)
	{


		$usuario=$this->db->prepare("SELECT cvendedor,tipo FROM users WHERE id=?");
		$usuario->bindValue(1, $id);
		$usuario->execute();
		$row=$usuario->fetch();

		$solicitud=$this->db->prepare("SELECT * FROM pagos WHERE cvendedor=?");
		$solicitud->bindValue(1,$row['cvendedor']);
		$solicitud->execute();

		$cadena="";

		
		while($datos=$solicitud->fetch())
		{
			
			if($datos['status']==0)
				$estado="PENDIENTE";
			if($datos['status']==1)
				$estado="APROBADO";
			if($datos['status']==2)
				$estado="RECHAZADO";


			$cadena.="<tr>
									<td data-th='Fecha'>".$datos['fecha']."</td>
									<td data-th='Monto'>".$datos['monto']."</td>
									<td data-th='Status'>".$estado."</td>
									<td data-th='Motivo'>".$datos['motivo']."</td>
								</tr>";
		}

		return $cadena;
	}


	public function actualizando_montos($id,$porpagar)
	{
		$usuario=$this->db->prepare("SELECT * FROM users WHERE id=?");
		$usuario->bindValue(1, $id);
		$usuario->execute();
		$row=$usuario->fetch();

		$fecha=date("Y-m-d H:i:00");


		$solicitud=$this->db->prepare("UPDATE valida SET cobrado=?,fecha_solicitud=? WHERE cvendedor=? AND cobrado=?");
		$solicitud->bindValue(1,"1");
		$solicitud->bindValue(2,$fecha);
		$solicitud->bindValue(3,$row['cvendedor']);
		$solicitud->bindValue(4,"0");
		$solicitud->execute();


		$pago=$this->db->prepare("INSERT INTO pagos (cvendedor,nombre,apellido,email,rfc,curp,banco,cuenta,clabe,image_ife,monto,status,fecha) VALUES ( ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ?)");
		$pago->bindValue(1,$row['cvendedor']);
		$pago->bindValue(2,$row['nombre']);
		$pago->bindValue(3,$row['apellido']);
		$pago->bindValue(4,$row['email']);
		$pago->bindValue(5,$row['rfc']);
		$pago->bindValue(6,$row['curp']);
		$pago->bindValue(7,$row['banco']);
		$pago->bindValue(8,$row['cuenta']);
		$pago->bindValue(9,$row['clabe']);
		$pago->bindValue(10,$row['image_ife']);
		$pago->bindValue(11,$porpagar);
		$pago->bindValue(12,"0");
		$pago->bindValue(13,$fecha);
		$pago->execute();


	}

	public function pesos_por_recibo($id)
	{
		$ventas=$this->db->prepare("SELECT * FROM pagos WHERE id=?");
		$ventas->bindValue(1, $id);
		$ventas->execute();	
		$row=$ventas->fetch();
		return implode("&&", $row);

	}

	public function pesos_por_pagar($id)
	{
		$link=mysql_connect("localhost","motobene_usuario","socio00");
		mysql_select_db("motobene_usuario");
		$cadena="";

		$usuario=$this->db->prepare("SELECT cvendedor,tipo FROM users WHERE id=?");
		$usuario->bindValue(1, $id);
		$usuario->execute();
		$row=$usuario->fetch();


		$vendedor=$row['cvendedor'];
		$tipo=$row['tipo'];


		$ventas=$this->db->prepare("SELECT DISTINCT valida.modelo FROM valida,catalogo WHERE valida.modelo=catalogo.modelo AND cvendedor=? ORDER BY catalogo.modelo ASC");
		$ventas->bindValue(1, $vendedor);
		$ventas->execute();	

		$totalfinal=0;
		$totalpagado=0;

		if($ventas->rowCount()>0)
		{	
				while ($row=$ventas->fetch()) {

					$puntostotales=0;
					$detalle=$this->db->prepare("SELECT * FROM valida,catalogo WHERE valida.modelo=catalogo.modelo AND cvendedor=? AND valida.modelo=?");
					

					$detalle->bindValue(1,$vendedor);
					$detalle->bindValue(2,$row['modelo']);

					$detalle->execute();

					$puntosnormales=0;
					$puntospromocion=0;
					$unidadesnormales=0;
					$unidadespromocion=0;

					while ($rowdetalle=$detalle->fetch()) {
								

							$imagen=$rowdetalle['imagen'];
							$nombre=$rowdetalle['nombre'];
							$cobrado=$rowdetalle['cobrado'];
							$query="SELECT puntos FROM promo WHERE tipo='$tipo' AND modelo='".$row['modelo']."' AND inicio<='".$rowdetalle['fecha']."' AND final>='".$rowdetalle['fecha']."'";
							$resultado=mysql_query($query);
							



							if(mysql_num_rows($resultado)>0)
							{
								$promodetalle=mysql_fetch_array($resultado);
								$totalfinal+=$promodetalle['puntos'];	
								$puntostotales+=$promodetalle['puntos'];
								$puntospromocion+=$promodetalle['puntos'];
								$unidadespromocion++;
								if($cobrado==1)
								{
									$totalpagado+=$promodetalle['puntos'];
								}
								else
								{
									$totalporpagar+=$promodetalle['puntos'];
								}


							}
							else
							{
								$totalfinal+=$rowdetalle['puntos'];
								$puntostotales+=$rowdetalle['puntos'];
								$puntosnormales+=$rowdetalle['puntos'];
								$unidadesnormales++;
								if($cobrado==1)
								{
									$totalpagado+=$rowdetalle['puntos'];
								}
								else
								{
									$totalporpagar+=$rowdetalle['puntos'];
								}

							}

					}


			}

			



				return $totalporpagar;
		}
		else
			return 0;



	}


	public function pesos_por_pagar_distribuidor($id)
	{
		$link=mysql_connect("localhost","motobene_usuario","socio00");
		mysql_select_db("motobene_usuario");
		$cadena="";

		$usuario=$this->db->prepare("SELECT cvendedor,tipo FROM users WHERE id=?");
		$usuario->bindValue(1, $id);
		$usuario->execute();
		$row=$usuario->fetch();

		$vendedor=$row['cvendedor'];
		$tipo=$row['tipo'];


		$ventas=$this->db->prepare("SELECT SUM(pagar) AS pagar FROM valida WHERE cvendedor = ? AND cobrado!='1'");
		$ventas->bindValue(1, $vendedor);
		$ventas->execute();	


		if($ventas->rowCount()>0)
		{	
			$row=$ventas->fetch();
			return 	$row['pagar'];
		}
		else
			return 0;

	}

	public function update_cobranza($nombre,$apellido,$telefono,$ife,$banco,$cuenta,$clabe,$rfc,$curp,$image_location,$user_id)
	{
			$query = $this->db->prepare("UPDATE `users` SET
								`nombre`	= ?,
								`apellido`		= ?,
								`telefono`			= ?,
								`ife`= ?,
								`banco`= ?,
								`cuenta`= ?,
								`clabe`= ?,
								`rfc`= ?,
								`curp`= ?,
								`image_ife`= ?
								
								WHERE `id` 		= ? 
								");

		$query->bindValue(1, $nombre);
		$query->bindValue(2, $apellido);
		$query->bindValue(3, $telefono);
		$query->bindValue(4, $ife);
		$query->bindValue(5, $banco);
		$query->bindValue(6, $cuenta);
		$query->bindValue(7, $clabe);
		$query->bindValue(8, $rfc);
		$query->bindValue(9, $curp);
		$query->bindValue(10, $image_location);
		$query->bindValue(11,$user_id);



		
		try{
			$query->execute();
		}catch(PDOException $e){
			die($e->getMessage());
		}
	}
	
	public function motivo_update($id)
	{
		$usuario=$this->db->prepare("SELECT cvendedor,tipo FROM users WHERE id=?");
		$usuario->bindValue(1, $id);
		$usuario->execute();
		$row=$usuario->fetch();
		$vendedor=$row['cvendedor'];

		$query = $this->db->prepare("SELECT * FROM pagos WHERE cvendedor= ? ORDER BY pagos.id DESC LIMIT 1");
		$query->bindValue(1, $vendedor);
		$query->execute();
		$row=$query->fetch();
		
		$motivo=$row['motivo'];
		
		if($motivo == "")
			return 0;
		else
			return 1;
	}



	public function detalle_vendedor($id)
	{
		$link=mysql_connect("localhost","motobene_usuario","socio00");
		mysql_select_db("motobene_usuario");
		$cadena="";

		$usuario=$this->db->prepare("SELECT cvendedor,tipo FROM users WHERE id=?");
		$usuario->bindValue(1, $id);
		$usuario->execute();
		$row=$usuario->fetch();


		$vendedor=$row['cvendedor'];
		$tipo=$row['tipo'];


		$ventas=$this->db->prepare("SELECT DISTINCT valida.modelo FROM valida,catalogo WHERE valida.modelo=catalogo.modelo AND cvendedor=? ORDER BY catalogo.modelo ASC");
		$ventas->bindValue(1, $vendedor);
		$ventas->execute();	

		$totalfinal=0;
		$totalpagado=0;

		if($ventas->rowCount()>0)
		{	
				while ($row=$ventas->fetch()) {

					$puntostotales=0;
					$detalle=$this->db->prepare("SELECT * FROM valida,catalogo WHERE valida.modelo=catalogo.modelo AND cvendedor=? AND valida.modelo=?");
					

					$detalle->bindValue(1,$vendedor);
					$detalle->bindValue(2,$row['modelo']);

					$detalle->execute();

					$puntosnormales=0;
					$puntospromocion=0;
					$unidadesnormales=0;
					$unidadespromocion=0;

					while ($rowdetalle=$detalle->fetch()) {
								

							$imagen=$rowdetalle['imagen'];
							$nombre=$rowdetalle['nombre'];
							$cobrado=$rowdetalle['cobrado'];
							$query="SELECT puntos FROM promo WHERE tipo='$tipo' AND modelo='".$row['modelo']."' AND inicio<='".$rowdetalle['fecha']."' AND final>='".$rowdetalle['fecha']."'";
							$resultado=mysql_query($query);
							



							if(mysql_num_rows($resultado)>0)
							{
								$promodetalle=mysql_fetch_array($resultado);
								$totalfinal+=$promodetalle['puntos'];	
								$puntostotales+=$promodetalle['puntos'];
								$puntospromocion+=$promodetalle['puntos'];
								$unidadespromocion++;

								$textopuntospromo=$promodetalle['puntos'];
								$textopuntosnormal=0;


								if($cobrado==1)
								{
									$textocobrado="COBRADO";
									$totalpagado+=$promodetalle['puntos'];
								}
								else
								{
									$textocobrado="DISPONIBLE";
									$totalporpagar+=$promodetalle['puntos'];
								}


							}
							else
							{
								$totalfinal+=$rowdetalle['puntos'];
								$puntostotales+=$rowdetalle['puntos'];
								$puntosnormales+=$rowdetalle['puntos'];
								$unidadesnormales++;

								$textopuntospromo=0;
								$textopuntosnormal=$rowdetalle['puntos'];

								if($cobrado==1)
								{
									$textocobrado="COBRADO";
									$totalpagado+=$rowdetalle['puntos'];
								}
								else
								{
									$textocobrado="DISPONIBLE";
									$totalporpagar+=$rowdetalle['puntos'];
								}

							}

							$cadena.='
							<tr>
								<td>'.$rowdetalle['folio'].'</td>
								<td>'.$rowdetalle['modelo'].'</td>
								<td>'.$rowdetalle['fecha'].'</td>
								<td>'.$textopuntosnormal.'</td>
								<td>'.$textopuntospromo.'</td>
								<td>'.$textocobrado.'</td>
							</tr>

					';
									


					}

					


			}
				return $cadena;
		}
		else
			return "No hay registros de ventas";



	}


	public function detalle_vendedor_dist($id)
	{
		$link=mysql_connect("localhost","motobene_usuario","socio00");
		mysql_select_db("motobene_usuario");
		$cadena="";

		$usuario=$this->db->prepare("SELECT cvendedor,tipo FROM users WHERE id=?");
		$usuario->bindValue(1, $id);
		$usuario->execute();
		$row=$usuario->fetch();


		$vendedor=$row['cvendedor'];
		$tipo=$row['tipo'];


		$ventas=$this->db->prepare("SELECT * FROM valida WHERE cvendedor=? AND pagar!=0");
		$ventas->bindValue(1, $vendedor);
		$ventas->execute();	

		$totalfinal=0;
		$totalpagado=0;

		if($ventas->rowCount()>0)
		{	
				while ($row=$ventas->fetch()) {
					$estado='POR PAGAR';
					if($row['cobrado']==1)
					{
						$estado='COBRADO';
					}
					$porpagar=number_format($row['pagar'],2,'.',',');

							$cadena.='
							<tr>
								<td>'.$row['folio'].'</td>
								<td>'.$row['fecha'].'</td>
								<td>'.$estado.'</td>
								<td>$ '.$porpagar.'</td>
							</tr>

					';
									



					


			}
				return $cadena;
		}
		else
			return "No hay registros de ventas";



	}

	


	public function lista_usuarios()
	{
		
		
		$tipos_usuario=array('ALE' => TRUE,'GDM' => TRUE,'GNT' => TRUE,'ICA' => TRUE,'RED' => TRUE,'ROALCOM' => TRUE,'SIB' => TRUE,'CC' => TRUE,'DC' => TRUE );

		$filepath='ventas_canal_directo.csv';
		$fp=fopen($filepath, "w+");
		
		fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

		$cabecera = array('Codigo de vendedor','Username','Nombre','Apellido','E-mail','Telefono','Tipo','Pagado','Pagar','Gran total');
		fputcsv($fp, $cabecera);

		$link=mysql_connect("localhost","motobene_usuario","socio00");
		mysql_select_db("motobene_usuario");
		$cadena="";


		$todoslosusuarios=$this->db->prepare("SELECT * FROM users");
		$todoslosusuarios->execute();

		while ($elid=$todoslosusuarios->fetch()) {



				$totalfinal=0;
				$totalpagado=0;
				$totalporpagar=0;

				$usuario=$this->db->prepare("SELECT cvendedor,tipo FROM users WHERE id=?");
				$usuario->bindValue(1, $elid['id']);
				$usuario->execute();
				$row=$usuario->fetch();


				if(!$tipos_usuario[$row['tipo']])
				{



						$vendedor=$row['cvendedor'];
						$tipo=$row['tipo'];


						$ventas=$this->db->prepare("SELECT DISTINCT valida.modelo FROM valida,catalogo WHERE valida.modelo=catalogo.modelo AND pagar=0 AND cvendedor=? ORDER BY catalogo.modelo ASC");
						$ventas->bindValue(1, $vendedor);
						$ventas->execute();	

						

						if($ventas->rowCount()>0)
						{	
								while ($row=$ventas->fetch()) {

									$puntostotales=0;
									$detalle=$this->db->prepare("SELECT * FROM valida,catalogo WHERE valida.modelo=catalogo.modelo AND cvendedor=? AND valida.modelo=?");
									

									$detalle->bindValue(1,$vendedor);
									$detalle->bindValue(2,$row['modelo']);

									$detalle->execute();

									$puntosnormales=0;
									$puntospromocion=0;
									$unidadesnormales=0;
									$unidadespromocion=0;

									while ($rowdetalle=$detalle->fetch()) {
												

											$imagen=$rowdetalle['imagen'];
											$nombre=$rowdetalle['nombre'];
											$cobrado=$rowdetalle['cobrado'];
											$query="SELECT puntos FROM promo WHERE tipo='$tipo' AND modelo='".$row['modelo']."' AND inicio<='".$rowdetalle['fecha']."' AND final>='".$rowdetalle['fecha']."'";
											$resultado=mysql_query($query);
											



											if(mysql_num_rows($resultado)>0)
											{
												$promodetalle=mysql_fetch_array($resultado);
												$totalfinal+=$promodetalle['puntos'];	
												$puntostotales+=$promodetalle['puntos'];
												$puntospromocion+=$promodetalle['puntos'];
												$unidadespromocion++;
												if($cobrado==1)
												{
													$totalpagado+=$promodetalle['puntos'];
												}
												else
												{
													$totalporpagar+=$promodetalle['puntos'];
												}


											}
											else
											{
												$totalfinal+=$rowdetalle['puntos'];
												$puntostotales+=$rowdetalle['puntos'];
												$puntosnormales+=$rowdetalle['puntos'];
												$unidadesnormales++;
												if($cobrado==1)
												{
													$totalpagado+=$rowdetalle['puntos'];
												}
												else
												{
													$totalporpagar+=$rowdetalle['puntos'];
												}

											}

									}

									


							}

							



								
						}
						else
						{
								$totalporpagar=0;
								$totalporpagar=0;
								$totalfinal=0;
						}


						$cadena.='<tr>
								<td><a href="detalle_usuario.php?id='.$elid['id'].'" target="_blank">'.$elid['cvendedor'].'</a></td>
								<td>'.$elid['username'].'</td>
								<td>'.$elid['nombre'].'</td>
								<td>'.$elid['apellido'].'</td>
								<td>'.$elid['email'].'</td>
								<td>'.$elid['telefono'].'</td>
								<td>'.$elid['tipo'].'</td>
								<td>'.$totalpagado.'</td>
								<td>'.$totalporpagar.'</td>
								<td>'.$totalfinal.'</td>
						</tr>';

							$fields = array($elid['cvendedor'],utf8_decode($elid['username']),$elid['nombre'],$elid['apellido'],$elid['email'],$elid['telefono'],$elid['tipo'],$totalpagado,$totalporpagar,$totalfinal);
							fputcsv($fp, $fields);
							unset($fields);

				}

		}

			
			fclose($fp);
			return $cadena;
	}



	public function lista_usuarios_dist()
	{
		
		$filepath='ventas_canal_alterno.csv';
		$fp=fopen($filepath, "w+");
		
		fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

		$cabecera = array('Codigo de vendedor','Username','Nombre','Apellido','E-mail','Telefono','Tipo','Pagado','Pagar','Gran total');
		fputcsv($fp, $cabecera);

		$link=mysql_connect("localhost","motobene_usuario","socio00");
		mysql_select_db("motobene_usuario");
		$cadena="";


		$todoslosusuarios=$this->db->prepare("SELECT DISTINCT (cvendedor) as cvendedor FROM valida WHERE pagar!=0");
		$todoslosusuarios->execute();

		while ($vendedor=$todoslosusuarios->fetch()) {

				$usuario=$this->db->prepare("SELECT * FROM users WHERE cvendedor=?");
				$usuario->bindValue(1, $vendedor['cvendedor']);
				$usuario->execute();
				$row=$usuario->fetch();

				$ventas=$this->db->prepare("SELECT SUM(pagar) as cobrado FROM valida WHERE cvendedor=? AND cobrado='1'");
				$ventas->bindValue(1, $vendedor['cvendedor']);
				$ventas->execute();
				$totalcobrado=$ventas->fetch();

				$ventas=$this->db->prepare("SELECT SUM(pagar) as porpagar FROM valida WHERE cvendedor=? AND cobrado='0'");
				$ventas->bindValue(1, $vendedor['cvendedor']);
				$ventas->execute();
				$totalporpagar=$ventas->fetch();

				$totalfinal=$totalcobrado['cobrado']+$totalporpagar['porpagar'];

				$cadena.='<tr>
						<td><a href="detalle_usuario_dist.php?id='.$row['id'].'" target="_blank">'.$row['cvendedor'].'</a></td>
						<td>'.$row['username'].'</td>
						<td>'.$row['nombre'].'</td>
						<td>'.$row['apellido'].'</td>
						<td>'.$row['email'].'</td>
						<td>'.$row['telefono'].'</td>
						<td>'.$row['tipo'].'</td>
						<td>'.$totalcobrado['cobrado'].'</td>
						<td>'.$totalporpagar['porpagar'].'</td>
						<td>'.$totalfinal.'</td>
				</tr>';

					$fields = array($row['cvendedor'],utf8_decode($row['username']),$row['nombre'],$row['apellido'],$row['email'],$row['telefono'],$row['tipo'],$totalcobrado['cobrado'],$totalporpagar['porpagar'],$totalfinal);
					fputcsv($fp, $fields);
					unset($fields);

		}

			
			fclose($fp);
			return $cadena;
	}
	
	public function lista_usuarios_dist_temp()
		{
			
			$filepath='solicitadas_final_dist.csv';
			$fp=fopen($filepath, "w+");
			
			fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
	
			$cabecera = array('Codigo de vendedor','Username','Nombre','Apellido','E-mail','Telefono','Tipo','Pagado','Pagar','Gran total');
			fputcsv($fp, $cabecera);
	
			$link=mysql_connect("localhost","motobene_usuario","socio00");
			mysql_select_db("motobene_usuario");
			$cadena="";
	
	
			$todoslosusuarios=$this->db->prepare("SELECT DISTINCT (cvendedor) as cvendedor FROM valida_temp_dist WHERE pagar!=0");
			$todoslosusuarios->execute();
	
			while ($vendedor=$todoslosusuarios->fetch()) {
	
					$usuario=$this->db->prepare("SELECT * FROM users WHERE cvendedor=?");
					$usuario->bindValue(1, $vendedor['cvendedor']);
					$usuario->execute();
					$row=$usuario->fetch();
	
					$ventas=$this->db->prepare("SELECT SUM(pagar) as cobrado FROM valida_temp_dist WHERE cvendedor=? AND cobrado='1'");
					$ventas->bindValue(1, $vendedor['cvendedor']);
					$ventas->execute();
					$totalcobrado=$ventas->fetch();
	
					$ventas=$this->db->prepare("SELECT SUM(pagar) as porpagar FROM valida_temp_dist WHERE cvendedor=? AND cobrado='0'");
					$ventas->bindValue(1, $vendedor['cvendedor']);
					$ventas->execute();
					$totalporpagar=$ventas->fetch();
	
					$totalfinal=$totalcobrado['cobrado']+$totalporpagar['porpagar'];
	
					$cadena.='<tr>
							<td><a href="detalle_usuario_dist.php?id='.$row['id'].'" target="_blank">'.$row['cvendedor'].'</a></td>
							<td>'.$row['username'].'</td>
							<td>'.$row['nombre'].'</td>
							<td>'.$row['apellido'].'</td>
							<td>'.$row['email'].'</td>
							<td>'.$row['telefono'].'</td>
							<td>'.$row['tipo'].'</td>
							<td>'.$totalcobrado['cobrado'].'</td>
							<td>'.$totalporpagar['porpagar'].'</td>
							<td>'.$totalfinal.'</td>
					</tr>';
	
						$fields = array($row['cvendedor'],utf8_decode($row['username']),utf8_encode($row['nombre']),utf8_encode($row['apellido']),$row['email'],$row['telefono'],$row['tipo'],$totalcobrado['cobrado'],$totalporpagar['porpagar'],$totalfinal);
						fputcsv($fp, $fields);
						unset($fields);
	
			}
	
				
				fclose($fp);
				return $cadena;
		}
	
		
	public function lista_usuarios_test()
		{
		
			$tipos_usuario=array('ALE' => TRUE,'GDM' => TRUE,'GNT' => TRUE,'ICA' => TRUE,'RED' => TRUE,'ROALCOM' => TRUE,'SIB' => TRUE,'CC' => TRUE,'DC' => TRUE );
			
			$link=mysql_connect("localhost","motobene_usuario","socio00");
			mysql_select_db("motobene_usuario");
			$cadena="";
	
	
			$todoslosusuarios=$this->db->prepare("SELECT * FROM users");
			$todoslosusuarios->execute();
	
			while ($elid=$todoslosusuarios->fetch()) {
	
					$totalfinal=0;
					$totalpagado=0;
					$totalporpagar=0;
	
					$usuario=$this->db->prepare("SELECT cvendedor,tipo FROM users WHERE id=?");
					$usuario->bindValue(1, $elid['id']);
					$usuario->execute();
					$row=$usuario->fetch();


					if(!$tipos_usuario[$row['tipo']])
				{
	
	
					$vendedor=$row['cvendedor'];
					$tipo=$row['tipo'];
	
	
					$ventas=$this->db->prepare("SELECT DISTINCT valida_temp.modelo FROM valida_temp,catalogo WHERE valida_temp.modelo=catalogo.modelo AND cvendedor=? ORDER BY catalogo.modelo ASC");
					$ventas->bindValue(1, $vendedor);
					$ventas->execute();	
	
					
	
					if($ventas->rowCount()>0)
					{	
							while ($row=$ventas->fetch()) {
	
								$puntostotales=0;
								$detalle=$this->db->prepare("SELECT * FROM valida_temp,catalogo WHERE valida_temp.modelo=catalogo.modelo AND cvendedor=? AND valida_temp.modelo=?");
								
	
								$detalle->bindValue(1,$vendedor);
								$detalle->bindValue(2,$row['modelo']);
	
								$detalle->execute();
	
								$puntosnormales=0;
								$puntospromocion=0;
								$unidadesnormales=0;
								$unidadespromocion=0;
	
								while ($rowdetalle=$detalle->fetch()) {
											
	
										$imagen=$rowdetalle['imagen'];
										$nombre=$rowdetalle['nombre'];
										$cobrado=$rowdetalle['cobrado'];
										$query="SELECT puntos FROM promo WHERE tipo='$tipo' AND modelo='".$row['modelo']."' AND inicio<='".$rowdetalle['fecha']."' AND final>='".$rowdetalle['fecha']."'";
										$resultado=mysql_query($query);
										
	
	
	
										if(mysql_num_rows($resultado)>0)
										{
											$promodetalle=mysql_fetch_array($resultado);
											$totalfinal+=$promodetalle['puntos'];	
											$puntostotales+=$promodetalle['puntos'];
											$puntospromocion+=$promodetalle['puntos'];
											$unidadespromocion++;
											if($cobrado==1)
											{
												$totalpagado+=$promodetalle['puntos'];
											}
											else
											{
												$totalporpagar+=$promodetalle['puntos'];
											}
	
	
										}
										else
										{
											$totalfinal+=$rowdetalle['puntos'];
											$puntostotales+=$rowdetalle['puntos'];
											$puntosnormales+=$rowdetalle['puntos'];
											$unidadesnormales++;
											if($cobrado==1)
											{
												$totalpagado+=$rowdetalle['puntos'];
											}
											else
											{
												$totalporpagar+=$rowdetalle['puntos'];
											}
	
										}
	
								}
	
								
	
	
						}
	
						
	
	
	
							
					}
					else
					{
							$totalporpagar=0;
							$totalporpagar=0;
							$totalfinal=0;
					}
	
	
					$cadena.='<tr>
							<td><a href="detalle_usuario.php?id='.$elid['id'].'" target="_blank">'.$elid['cvendedor'].'</a></td>
							<td>'.$elid['username'].'</td>
							<td>'.$elid['nombre'].'</td>
							<td>'.$elid['apellido'].'</td>
							<td>'.$elid['email'].'</td>
							<td>'.$elid['telefono'].'</td>
							<td>'.$elid['tipo'].'</td>
							<td>'.$totalpagado.'</td>
							<td>'.$totalporpagar.'</td>
							<td>'.$totalfinal.'</td>
					</tr>';
				}

			}
	
				return $cadena;
		}
		
		
	public function lista_usuarios_test_2()
		{
			$link=mysql_connect("localhost","motobene_usuario","socio00");
			mysql_select_db("motobene_usuario");
			$cadena="";
	
	
			$todoslosusuarios=$this->db->prepare("SELECT * FROM users");
			$todoslosusuarios->execute();
	
			while ($elid=$todoslosusuarios->fetch()) {
	
					$totalfinal=0;
					$totalpagado=0;
					$totalporpagar=0;
	
					$usuario=$this->db->prepare("SELECT cvendedor,tipo FROM users WHERE id=?");
					$usuario->bindValue(1, $elid['id']);
					$usuario->execute();
					$row=$usuario->fetch();
	
	
					$vendedor=$row['cvendedor'];
					$tipo=$row['tipo'];
	
	
					$ventas=$this->db->prepare("SELECT DISTINCT valida_temp_2.modelo FROM valida_temp_2,catalogo WHERE valida_temp_2.modelo=catalogo.modelo AND cvendedor=? ORDER BY catalogo.modelo ASC");
					$ventas->bindValue(1, $vendedor);
					$ventas->execute();	
	
					
	
					if($ventas->rowCount()>0)
					{	
							while ($row=$ventas->fetch()) {
	
								$puntostotales=0;
								$detalle=$this->db->prepare("SELECT * FROM valida_temp_2,catalogo WHERE valida_temp_2.modelo=catalogo.modelo AND cvendedor=? AND valida_temp_2.modelo=?");
								
	
								$detalle->bindValue(1,$vendedor);
								$detalle->bindValue(2,$row['modelo']);
	
								$detalle->execute();
	
								$puntosnormales=0;
								$puntospromocion=0;
								$unidadesnormales=0;
								$unidadespromocion=0;
	
								while ($rowdetalle=$detalle->fetch()) {
											
	
										$imagen=$rowdetalle['imagen'];
										$nombre=$rowdetalle['nombre'];
										$cobrado=$rowdetalle['cobrado'];
										$query="SELECT puntos FROM promo WHERE tipo='$tipo' AND modelo='".$row['modelo']."' AND inicio<='".$rowdetalle['fecha']."' AND final>='".$rowdetalle['fecha']."'";
										$resultado=mysql_query($query);
										
	
	
	
										if(mysql_num_rows($resultado)>0)
										{
											$promodetalle=mysql_fetch_array($resultado);
											$totalfinal+=$promodetalle['puntos'];	
											$puntostotales+=$promodetalle['puntos'];
											$puntospromocion+=$promodetalle['puntos'];
											$unidadespromocion++;
											if($cobrado==1)
											{
												$totalpagado+=$promodetalle['puntos'];
											}
											else
											{
												$totalporpagar+=$promodetalle['puntos'];
											}
	
	
										}
										else
										{
											$totalfinal+=$rowdetalle['puntos'];
											$puntostotales+=$rowdetalle['puntos'];
											$puntosnormales+=$rowdetalle['puntos'];
											$unidadesnormales++;
											if($cobrado==1)
											{
												$totalpagado+=$rowdetalle['puntos'];
											}
											else
											{
												$totalporpagar+=$rowdetalle['puntos'];
											}
	
										}
	
								}
	
								
	
	
						}
	
						
	
	
	
							
					}
					else
					{
							$totalporpagar=0;
							$totalporpagar=0;
							$totalfinal=0;
					}
	
	
					$cadena.='<tr>
							<td><a href="detalle_usuario.php?id='.$elid['id'].'" target="_blank">'.$elid['cvendedor'].'</a></td>
							<td>'.$elid['username'].'</td>
							<td>'.$elid['nombre'].'</td>
							<td>'.$elid['apellido'].'</td>
							<td>'.$elid['email'].'</td>
							<td>'.$elid['telefono'].'</td>
							<td>'.$elid['tipo'].'</td>
							<td>'.$totalpagado.'</td>
							<td>'.$totalporpagar.'</td>
							<td>'.$totalfinal.'</td>
					</tr>';
			}
	
				return $cadena;
		}


	public function vendedor_pesos_modelo($id)
	{
		$link=mysql_connect("localhost","motobene_usuario","socio00");
		mysql_select_db("motobene_usuario");
		$cadena="";

		$usuario=$this->db->prepare("SELECT cvendedor,tipo FROM users WHERE id=?");
		$usuario->bindValue(1, $id);
		$usuario->execute();
		$row=$usuario->fetch();


		$vendedor=$row['cvendedor'];
		$tipo=$row['tipo'];


		$ventas=$this->db->prepare("SELECT DISTINCT valida.modelo FROM valida,catalogo WHERE valida.modelo=catalogo.modelo AND cvendedor=? ORDER BY catalogo.modelo ASC");
		$ventas->bindValue(1, $vendedor);
		$ventas->execute();	

		$totalfinal=0;
		$totalpagado=0;

		if($ventas->rowCount()>0)
		{	
				while ($row=$ventas->fetch()) {

					$puntostotales=0;
					$detalle=$this->db->prepare("SELECT * FROM valida,catalogo WHERE valida.modelo=catalogo.modelo AND cvendedor=? AND valida.modelo=?");
					

					$detalle->bindValue(1,$vendedor);
					$detalle->bindValue(2,$row['modelo']);

					$detalle->execute();

					$puntosnormales=0;
					$puntospromocion=0;
					$unidadesnormales=0;
					$unidadespromocion=0;

					while ($rowdetalle=$detalle->fetch()) {
								

							$imagen=$rowdetalle['imagen'];
							$nombre=$rowdetalle['nombre'];
							$cobrado=$rowdetalle['cobrado'];
							$query="SELECT puntos FROM promo WHERE tipo='$tipo' AND modelo='".$row['modelo']."' AND inicio<='".$rowdetalle['fecha']."' AND final>='".$rowdetalle['fecha']."'";
							$resultado=mysql_query($query);
							



							if(mysql_num_rows($resultado)>0)
							{
								$promodetalle=mysql_fetch_array($resultado);
								$totalfinal+=$promodetalle['puntos'];	
								$puntostotales+=$promodetalle['puntos'];
								$puntospromocion+=$promodetalle['puntos'];
								$unidadespromocion++;
								if($cobrado==1)
								{
									$totalpagado+=$promodetalle['puntos'];
								}
								else
								{
									$totalporpagar+=$promodetalle['puntos'];
								}


							}
							else
							{
								$totalfinal+=$rowdetalle['puntos'];
								$puntostotales+=$rowdetalle['puntos'];
								$puntosnormales+=$rowdetalle['puntos'];
								$unidadesnormales++;
								if($cobrado==1)
								{
									$totalpagado+=$rowdetalle['puntos'];
								}
								else
								{
									$totalporpagar+=$rowdetalle['puntos'];
								}

							}

					}

					$query="SELECT modelo FROM omisiones WHERE modelo='".$row['modelo']."'";
					$omision=mysql_query($query);
					$estilo="block";
					if(mysql_num_rows($omision)>0)
						$estilo='none';

					$cadena.='
			           <div class="reportes col-xs-3 col-sm-3 white" style="display:'.$estilo.';">
			           <table width="100%" style="margin: 20px auto;">
			           <thead>
			           <tr style="background: transparent !important;">
			           	<th style="text-align: center;" colspan="2">'.$nombre.' | '.$row['modelo'].'</th>
			           </tr>
			           </thead>
			           
			           <tbody>
			           <tr>
			           <td>Unidades:</td><td>'.$unidadesnormales.'</td>
			           </tr>
			           <tr>
			           <td>Pesos:</td><td>'.$puntosnormales.'</td>
			           </tr>
			           <tr>
			           <td>Unidades promocion:</td><td>'.$unidadespromocion.'</td>
			           </tr>
			           <tr>
			           <td>Pesos promocion:</td><td>'.$puntospromocion.'</td>
			           </tr>
			           <tr>
			           <td>Total:</td><td>$ '.$puntostotales.'</td>
			           </tr>
			           </tbody>
			           </table>
			           </div>
					';


			}

			$cadena.='
			           <div class="reportes2 text-center white">
			           <table width="100% style="margin:20px auto;">
			           <thead>
			           <tr>
			           <th>TOTAL</th>
			           </tr>
			           </thead>
			           
			           <tbody>
			           <tr>
			           <td>Pagado: $ '.$totalpagado.'</td>
			           </tr>
			           <tr>
			           <td>Por pagar: $ '.$totalporpagar.'</td>
			           </tr>
			           </tbody>
			           </table>
			           </div>
					';



				return $cadena;
		}
		else
			return "No hay registros de ventas";



	}

	public function puntos_mes($id,$mes,$ano,$tipo)
	{
			if($tipo=="reales")
				$tabla="valida";
			else
				$tabla="ventas";

			$usuario=$this->db->prepare("SELECT cvendedor FROM users WHERE id=?");
			$usuario->bindValue(1, $id);
			$usuario->execute();
			$row=$usuario->fetch();

			$diafin=date("t",mktime(0,0,0,$mes,1,$ano));
			$fecha_inicio=date("Y-m-d 00:00:00",mktime(0,0,0,$mes,1,$ano));
			$fecha_final=date("Y-m-d 23:59:59",mktime(0,0,0,$mes,$diafin,$ano));

			$puntos=$this->db->prepare("SELECT * FROM ".$tabla.",catalogo WHERE ".$tabla.".modelo=catalogo.modelo AND cvendedor=? AND fecha BETWEEN ? AND ?");
			$puntos->bindValue(1, $row['cvendedor']);
			$puntos->bindValue(2, $fecha_inicio);
			$puntos->bindValue(3, $fecha_final);
			$puntos->execute();

			$total=0;
			while ($row=$puntos->fetch()) {

				$total+=$row['puntos'];

			}

			return $total;
	}

	public function puntos_periodo($id,$mes_inicio,$ano_inicio,$mes_fin,$ano_fin,$tipo,$suma)
	{
			
			if($tipo=="reales")
				$tabla="valida";
			else
				$tabla="ventas";

			$usuario=$this->db->prepare("SELECT cvendedor FROM users WHERE id=?");
			$usuario->bindValue(1, $id);
			$usuario->execute();
			$row=$usuario->fetch();
			$vendedor=$row['cvendedor'];



			$diafin=date("t",mktime(0,0,0,$mes_fin,1,$ano_fin));

			$fecha_inicio=date("Y-m-d 00:00:00",mktime(0,0,0,$mes_inicio,1,$ano_inicio));
			$fecha_final=date("Y-m-d 23:59:59",mktime(0,0,0,$mes_fin,$diafin,$ano_fin));

			$puntos=$this->db->prepare("SELECT * FROM ".$tabla.",catalogo WHERE ".$tabla.".modelo=catalogo.modelo AND cvendedor=? AND fecha BETWEEN ? AND ?");
			$puntos->bindValue(1, $row['cvendedor']);
			$puntos->bindValue(2, $fecha_inicio);
			$puntos->bindValue(3, $fecha_final);
			$puntos->execute();

			$total=0;
			while ($row=$puntos->fetch()) {

				$total+=$row['puntos'];

			}

			if($suma=="restar")
			{

				$usados=$this->db->prepare("SELECT * FROM puntos_usados WHERE cvendedor=? AND fecha BETWEEN ? AND ?");
				$usados->bindValue(1, $vendedor);
				$usados->bindValue(2, $fecha_inicio);
				$usados->bindValue(3, $fecha_final);
				$usados->execute();

				while ($row=$usados->fetch()) {

				$total-=$row['puntos'];

				}
			}


			return $total;
	}


	public function ver_ventas($id)
	{
		$cadena="";

		$usuario=$this->db->prepare("SELECT cvendedor FROM users WHERE id=?");
		$usuario->bindValue(1, $id);
		$usuario->execute();
		$row=$usuario->fetch();

		$ventas=$this->db->prepare("SELECT * FROM ventas WHERE cvendedor=?");
		$ventas->bindValue(1, $row['cvendedor']);
		$ventas->execute();
		if($ventas->rowCount()>0)
		{	
				while ($row=$ventas->fetch()) {

					$valida=$this->db->prepare("SELECT folio FROM valida WHERE folio=?");
					$valida->bindValue(1, $row['folio']);
					$valida->execute();
					if($valida->rowCount()>=1)
					{
						$simbolo='<img src="img/valida.png" alt="" />';
					}
					else
					{
						$simbolo='<img src="img/invalida.png" alt="" />';
					}

					$cadena.='
						<tr>
			            <td>'.$row[folio].'</td>
			            <td>'.$row[modelo].'</td>
			            <td>'.$row[cvendedor].'</td>
			            <td>'.$row[nombre].'</td>
			            <td>'.$row[fecha].'</td>
			            <td>'.$simbolo.'</td>
			            <tr>
			          
					';
				}

				return $cadena;
		}
		else
			return "No hay registros de ventas";


	}
	
	public function update_user($nombre,$apellido,$telefono,$email,$username,$image_location,$id){

		$query = $this->db->prepare("UPDATE `users` SET
								`nombre`	= ?,
								`apellido`		= ?,
								`telefono`			= ?,
								`email`= ?,
								`username`= ?,
								`image_location`= ?
								
								WHERE `id` 		= ? 
								");

		$query->bindValue(1, $nombre);
		$query->bindValue(2, $apellido);
		$query->bindValue(3, $telefono);
		$query->bindValue(4, $email);
		$query->bindValue(5, $username);
		$query->bindValue(6, $image_location);
		$query->bindValue(7, $id);
		
		try{
			$query->execute();
		}catch(PDOException $e){
			die($e->getMessage());
		}	
	}

	public function change_password($user_id, $password) {

		global $bcrypt;

		$password_hash = $bcrypt->genHash($password);

		$query = $this->db->prepare("UPDATE `users` SET `password` = ? WHERE `id` = ?");

		$query->bindValue(1, $password_hash);
		$query->bindValue(2, $user_id);				

		try{
			$query->execute();
			return true;
		} catch(PDOException $e){
			die($e->getMessage());
		}

	}

	public function recover($email, $generated_string) {

		if($generated_string == 0){
			return false;
		}else{
	
			$query = $this->db->prepare("SELECT COUNT(`id`) FROM `users` WHERE `email` = ? AND `generated_string` = ?");

			$query->bindValue(1, $email);
			$query->bindValue(2, $generated_string);

			try{

				$query->execute();
				$rows = $query->fetchColumn();

				if($rows == 1){
					
					global $bcrypt;

					$username = $this->fetch_info('username', 'email', $email); 
					$user_id  = $this->fetch_info('id', 'email', $email);
			
					$charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
					$generated_password = substr(str_shuffle($charset),0, 10);

					$this->change_password($user_id, $generated_password);

					$query = $this->db->prepare("UPDATE `users` SET `generated_string` = 0 WHERE `id` = ?");

					$query->bindValue(1, $user_id);
	
					$query->execute();

					mail($email, 'Tu contraseña', "Hola " . $username . ",\n\ntu nueva contraseña es: " . $generated_password . "\n\nTe sugerimos cambiar tu contraseña una vez que hayas ingresado.\n\nMotobenefits");

				}else{
					return false;
				}

			} catch(PDOException $e){
				die($e->getMessage());
			}
		}
	}

        public function fetch_info($what, $field, $value){
    
    		$allowed = array('id', 'username', 'first_name', 'last_name', 'gender', 'bio', 'email');    		
    		if (!in_array($what, $allowed, true) || !in_array($field, $allowed, true)) {
    		    throw new InvalidArgumentException;
    		}else{
    		
    			$query = $this->db->prepare("SELECT $what FROM `users` WHERE $field = ?");
    
    			$query->bindValue(1, $value);
    
    			try{
    
    				$query->execute();
    				
    			} catch(PDOException $e){
    
    				die($e->getMessage());
    			}
    
    			return $query->fetchColumn();
    		}
    	}

	public function confirm_recover($email){

		$username = $this->fetch_info('username', 'email', $email);

		$unique = uniqid('',true);
		$random = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'),0, 10);
		
		$generated_string = $unique . $random;

		$query = $this->db->prepare("UPDATE `users` SET `generated_string` = ? WHERE `email` = ?");

		$query->bindValue(1, $generated_string);
		$query->bindValue(2, $email);

		try{
			
			$query->execute();
				
				$headers .= "Reply-To: Motobenefits <noreplay@moto-benefits.com.mx>\r\n"; 
				$headers .= "Return-Path: Motobenefits <noreplay@moto-benefits.com.mx>\r\n"; 
				$headers .= "From: Motobenefits <noreplay@moto-benefits.com.mx>\r\n"; 
			    $headers .= "Organization: Motobenefits\r\n";
			    $headers .= "MIME-Version: 1.0\r\n";
			    $headers .= "Content-type: text/plain; charset=utf-8\r\n";
			    $headers .= "X-Priority: 3\r\n";
			    $headers .= "X-Mailer: PHP". phpversion() ."\r\n";   
			mail($email, 'Recuperar contraseña', "Hola " . $username. ",\r\nPor favor da click a la liga de abajo:\r\n\r\nhttp://www.moto-benefits.com.mx/recover.php?email=" . $email . "&generated_string=" . $generated_string . "\r\n\r\n Generaremos una nueva contraseña que será enviada a tu correo\r\n\r\nMotobenefits", $headers);			
			
		} catch(PDOException $e){
			die($e->getMessage());
		}
	}

	public function user_exists($username) {
	
		$query = $this->db->prepare("SELECT COUNT(`id`) FROM `users` WHERE `username`= ?");
		$query->bindValue(1, $username);
	
		try{

			$query->execute();
			$rows = $query->fetchColumn();

			if($rows == 1){
				return true;
			}else{
				return false;
			}

		} catch (PDOException $e){
			die($e->getMessage());
		}

	}

	public function user_vendedor_registrado($username) {
	
		$query = $this->db->prepare("SELECT id FROM `req` WHERE `cvendedor`= ?");
		$query->bindValue(1, $username);
	
		try{

			$query->execute();
			$rows = $query->rowCount();

			if($rows >= 1){
				return false;
			}else{
				return true;
			}

		} catch (PDOException $e){
			die($e->getMessage());
		}

	}

	public function venta_exists($username) {
	
		$query = $this->db->prepare("SELECT COUNT(`id`) FROM `ventas` WHERE `folio`= ?");
		$query->bindValue(1, $username);
	
		try{

			$query->execute();
			$rows = $query->fetchColumn();

			if($rows >= 1){
				return true;
			}else{
				return false;
			}

		} catch (PDOException $e){
			die($e->getMessage());
		}

	}
	
		public function user_cvendedor($cvendedor) {
		
			$query = $this->db->prepare("SELECT `id` FROM `users` WHERE `cvendedor`= ?");
			$query->bindValue(1, $cvendedor);
		
			try{
	
				$query->execute();
				$rows = $query->rowCount();
	
				if($rows == 1){
					return true;
				}else{
					return false;
				}
	
			} catch (PDOException $e){
				die($e->getMessage());
			}
	
		}
	 
	public function email_exists($email) {

		$query = $this->db->prepare("SELECT COUNT(`id`) FROM `users` WHERE `email`= ?");
		$query->bindValue(1, $email);
	
		try{

			$query->execute();
			$rows = $query->fetchColumn();

			if($rows == 1){
				return true;
			}else{
				return false;
			}

		} catch (PDOException $e){
			die($e->getMessage());
		}

	}

	public function register($cvendedor,$nombre,$apellido,$apellido_m,$email,$telefono,$celular,$nextel,$tipo,$estado,$ciudad,$username, $password){
	
			global $bcrypt; // making the $bcrypt variable global so we can use here
	
			$time 		= time();
			$ip 		= $_SERVER['REMOTE_ADDR']; // getting the users IP address
			$email_code = $email_code = uniqid('code_',true); // Creating a unique string.
			
			$password   = $bcrypt->genHash($password);
	
			$query 	= $this->db->prepare("INSERT INTO `users` (`cvendedor`,`nombre`,`apellido`,`apellido_m`,`email`,`telefono`,`celular`,`nextel`,`tipo`,`estado`,`ciudad`,`username`,`password`,`ip`,`time`,`email_code`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ");
	
			$query->bindValue(1, $cvendedor);
			$query->bindValue(2, $nombre);
			$query->bindValue(3, $apellido);
			$query->bindValue(4, $apellido_m);
			$query->bindValue(5, $email);
			$query->bindValue(6, $telefono);
			$query->bindValue(7, $celular);
			$query->bindValue(8, $nextel);
			$query->bindValue(9, $tipo);
			$query->bindValue(10, $estado);
			$query->bindValue(11, $ciudad);
			$query->bindValue(12, $username);
			$query->bindValue(13, $password);
			$query->bindValue(14, $ip);
			$query->bindValue(15, $time);
			$query->bindValue(16, $email_code);
	
			try{
				$query->execute();
				$headers = "From: noreplay@moto-benefits.com.mx\r\n";
				mail($email, 'Activacion Motobenefits', "Hola " . $username. ",\r\nGracias por registrarte. Por favor da click en la siguiente liga para activar tu cuenta:\r\n\r\nhttp://www.moto-benefits.com/activate.php?email=" . $email . "&email_code=" . $email_code . "\r\n\r\nMotobenefits", $headers);
			}catch(PDOException $e){
				die($e->getMessage());
			}	
		}
		
		
public function register_venta($folio,$ciudad,$modelo,$nombre,$id){

		$usuario=$this->db->prepare("SELECT cvendedor FROM users WHERE id=?");
		$usuario->bindValue(1, $id);
		$usuario->execute();
		$row=$usuario->fetch();


		$query 	= $this->db->prepare("INSERT INTO ventas (folio,ciudad,modelo,cvendedor,nombre,fecha) VALUES (?, ?, ?, ?, ?, ?)");

		$query->bindValue(1, $folio);
		$query->bindValue(2, $ciudad);
		$query->bindValue(3, $modelo);
		$query->bindValue(4, $row['cvendedor']);
		$query->bindValue(5, $nombre);
		$query->bindValue(6, date("Y-m-d H:i:s"));
		
		

		try{
			$query->execute();
			
		}catch(PDOException $e){
			die($e->getMessage());
		}	
	}


	public function activate($email, $email_code) {
		
		$query = $this->db->prepare("SELECT COUNT(`id`) FROM `users` WHERE `email` = ? AND `email_code` = ? AND `confirmed` = ?");

		$query->bindValue(1, $email);
		$query->bindValue(2, $email_code);
		$query->bindValue(3, 0);

		try{

			$query->execute();
			$rows = $query->fetchColumn();

			if($rows == 1){
				
				$query_2 = $this->db->prepare("UPDATE `users` SET `confirmed` = ? WHERE `email` = ?");

				$query_2->bindValue(1, 1);
				$query_2->bindValue(2, $email);				

				$query_2->execute();
				return true;

			}else{
				return false;
			}

		} catch(PDOException $e){
			die($e->getMessage());
		}

	}


	public function email_confirmed($username) {

		$query = $this->db->prepare("SELECT COUNT(`id`) FROM `users` WHERE `username`= ? AND `confirmed` = ?");
		$query->bindValue(1, $username);
		$query->bindValue(2, 1);
		
		try{
			
			$query->execute();
			$rows = $query->fetchColumn();

			if($rows == 1){
				return true;
			}else{
				return false;
			}

		} catch(PDOException $e){
			die($e->getMessage());
		}

	}

	public function login($username, $password) {

		global $bcrypt; 

		$query = $this->db->prepare("SELECT `password`, `id` FROM `users` WHERE `username` = ?");
		$query->bindValue(1, $username);

		try{
			
			$query->execute();
			$data 				= $query->fetch();
			$stored_password 	= $data['password'];
			$id   				= $data['id']; 
			
			if($bcrypt->verify($password, $stored_password) === true){ 
			return $id;
			}else{
				return false;	
			}

		}catch(PDOException $e){
			die($e->getMessage());
		}
	
	}

	public function tipo_usuario($id) {


		$query = $this->db->prepare("SELECT tipo FROM `users` WHERE `id` = ?");
		$query->bindValue(1, $id);

		try{
			
			$query->execute();
			$data 				= $query->fetch();
			$tipo 				= $data['tipo'];
			
			return $tipo;

		}catch(PDOException $e){
			die($e->getMessage());
		}
	
	}

	public function userdata($id) {

		$query = $this->db->prepare("SELECT * FROM `users` WHERE `id`= ?");
		$query->bindValue(1, $id);

		try{

			$query->execute();

			return $query->fetch();

		} catch(PDOException $e){

			die($e->getMessage());
		}

	}
	  	  	 
	public function get_users() {

		$query = $this->db->prepare("SELECT * FROM `users` ORDER BY `time` DESC");
		
		try{
			$query->execute();
		}catch(PDOException $e){
			die($e->getMessage());
		}

		return $query->fetchAll();

	}	
}