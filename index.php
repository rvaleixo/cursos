<?php

if ( isset ( $_REQUEST [ "Entrada" ] ) )	{ $Entrada	= $_REQUEST [ "Entrada" ];	} else { echo 'Falta o Parâmetro de Entrada conforme descrito no exercício'; die();}
if ( isset ( $_REQUEST [ "max_t" ] )	)	{ $max_t 	= $_REQUEST [ "max_t" ];	} else { $max_t = 100;	}

include 'conecta_mysqli.inc';

include 'funcoes_padrao.php';

$ERRO		= '';

$EntradaJS 	= json_decode($Entrada);

$DATA		= $EntradaJS->data;

/* Informa o nível dos erros que serão exibidos */
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
error_reporting(E_ALL);

ApagaTabelaBD();

$i = 0; 

foreach( $DATA as $e ){ 
	$VALIDOU = true;
	$i++; 
	$pos_min 		= strpos($e, 'min');	
	$pos_lightning	= strpos($e, 'lightning');	
	if( $pos_min > 0 ) {

		$str 		= rtrim($e,'min');
		$pos_espaco	=  strrpos($str, ' ');
		$PALESTRA	= substr( $str ,0 , $pos_espaco );
		$DURACAO	= substr( $str ,$pos_espaco + 1 , strlen($str) - ( $pos_espaco ) );
																	
	}elseif( $pos_lightning	 > 0 ){
		$str 		= rtrim($e,'lightning');
		$pos_espaco	= strrpos($str, ' ');
		$PALESTRA	= substr( $e ,0 , $pos_espaco );
		$DURACAO	= '5';
	}else{
		echo 'Palestra: '.$e. ' Sem Tempo Definido! Não Será Agendada'; 
		$VALIDOU = false;
	}
	if( $VALIDOU ) {
		CarregarTabelaBD();
	}
}
$busca_palestras = "SELECT * FROM `palestras` WHERE 1";
$busca_palestras_q = mysqli_query($link,$busca_palestras);
if (!$busca_palestras_q){
	echo 'Erro na Busca de Palestras: Erro => '.mysqli_error($link);
	echo ' "<script>javascript:history.back(-1)</script>"';
	mysqli_close($link);
	die();
}
																		
while ( $linha_palestras = mysqli_fetch_array( $busca_palestras_q ) ) {
	$PALESTRA		=	$linha_palestras['palestra'];
	$DURACAO		=	$linha_palestras['duracao'];
	$ACHEI			= 	0;
	$linha_periodos	=	0;
	
	while ( !$ACHEI ) {
		$D = intval( $DURACAO );
		$busca_periodo = "SELECT * FROM `periodos` WHERE `tempo_restante` >= $D ORDER BY `tempo_restante` DESC";

		$busca_periodo_q = mysqli_query($link,$busca_periodo);
		if (!$busca_periodo_q){
			echo 'Erro na Busca de Periodo com Tempo Restante Suficiente: Erro => '.mysqli_error($link);
			echo ' "<script>javascript:history.back(-1)</script>"';
			mysqli_close($link);
			die();
		}
		while ( $linha_periodos = mysqli_fetch_array( $busca_periodo_q ) ) {
			$TRILHA				=	$linha_periodos['trilha'];
			$PERIODO			=	$linha_periodos['periodo'];
			$ULTIMO_HORARIO		=	$linha_periodos['ultimo_horario'];
			$TEMPO_RESTANTE		=	$linha_periodos['tempo_restante'];
			$MAXIMO_HORARIO		=	$linha_periodos['maximo_horario'];
			$MINIMO_HORARIO		=	$linha_periodos['minimo_horario'];

			// AjustaTempoRest($trilha,$periodo,$duracao)

			$HORA_FINAL			=	date_create($ULTIMO_HORARIO);
			$H1  				= date_parse ( $ULTIMO_HORARIO );
			$MINUTO_FINAL 		= $DURACAO + $H1['minute'];
			$HORA_FIM			= $H1['hour'];
			if ( $MINUTO_FINAL > 59 ) { 
				$HORA_ADICIONAL = intval( $MINUTO_FINAL /60 );  
				$MINUTO_FINAL 	= $MINUTO_FINAL - intval( $MINUTO_FINAL /60 ) * 60  ; 
				$HORA_FIM	   += $HORA_ADICIONAL;
			}
			$HORA_FINAL 		=	date_time_set( $HORA_FINAL, $HORA_FIM , $MINUTO_FINAL );
			$T_R				=	date_diff(date_create( $MAXIMO_HORARIO ) , $HORA_FINAL, true);
			$TEMPO_RESTANTE 	=	$T_R->format("%h")  * 60 +  $T_R->format( "%i" ) ;

			if( $TEMPO_RESTANTE >= 0 ) {
				if( AnotaPeriodoNaPalestra() ) {
					$ACHEI 		=	1;
				}
				if( $ACHEI ) {
					$ACHEI 		=	0;
					if( AtualizaPeriodo() ) {
						$ACHEI 		=	1;
					}
				}
			}else{
				if (!CriaPeriodo() ) {
					die();
				}
			}
			break;
		}
		if( !$ACHEI ) {
			if (!CriaPeriodo() ) {
				die();
			}
		}else{
			break;
		}
																	
	}
}

//////////////////////// Verificar Se Algum Período Vespertino Termina Antes das 16h /////////////////////// 
/*
$busca_periodos = "SELECT * FROM `periodos` WHERE `periodo` = 'V' and `ultimo_horario` < '16:00:00' ORDER BY `trilha` ASC";
$busca_periodos_q = mysqli_query($link,$busca_periodos);
if (!$busca_periodos_q){
	echo 'Erro na Busca de Periodos para Revisão Término: Erro => '.mysqli_error($link);
	echo ' "<script>javascript:history.back(-1)</script>"';
	mysqli_close($link);
	die();
}
while ( $linha_periodos = mysqli_fetch_array( $busca_periodos_q ) {
	$TRILHA		= $linha_periodos['trilha'];
	$PERIODO	= $linha_periodos['periodo'];
	
	$TEMPO_FALTATE = date_diff( date_create( '16:00:00' ) , $linha_periodos['ultimo_horario'] , true);
	$busca_palestras = "SELECT * FROM `palestras` WHERE `duracao` >= '$TEMPO_FALTATE' and `trilha` != '$TRILHA' and `periodo` != '$PERIODO' ORDER BY `duracao` ASC";
	$busca_palestras_q = mysqli_query($link,$busca_palestras);
	if (!$busca_palestras_q){
		echo 'Erro na Busca de Palestra para Trocar de Período: Erro => '.mysqli_error($link);
		echo ' "<script>javascript:history.back(-1)</script>"';
		mysqli_close($link);
		die();
	}
	$linha_palestras = mysqli_fetch_array( $busca_palestras_q );

}
*/

////////////////////////  Montar Saida //////////////////////// 

$busca_palestras = "SELECT * FROM `palestras` WHERE 1   ORDER BY `trilha` ASC, `periodo` ASC, `hora_inicio` ASC";
$busca_palestras_q = mysqli_query($link,$busca_palestras);
if (!$busca_palestras_q){
	echo 'Erro na Busca de Palestras para Saida: Erro => '.mysqli_error($link);
	echo ' "<script>javascript:history.back(-1)</script>"';
	mysqli_close($link);
	die();
}
$GUARDA_TRILHA = 0;
$GUARDA_PERIODO= '';
$IMPRIME_PERIODO= 0;
$SAIDA_EVENTOS = array('"data"​ : [  ');

while ( $linha_palestras = mysqli_fetch_array( $busca_palestras_q ) ) {
	
	$PALESTRA		=	$linha_palestras['palestra'];
	$DURACAO		=	$linha_palestras['duracao'];
	$TRILHA         =	$linha_palestras['trilha'];
	$PERIODO        =	$linha_palestras['periodo'];
	$HORA_INICIO    =	$linha_palestras['hora_inicio'];
	$HORA_FIM       =	$linha_palestras['hora_fim'];
	$SAIDA_PERIODO 	=	$SAIDA_TRILHA = '';
	if( $GUARDA_TRILHA != $TRILHA ) {
		if( $IMPRIME_PERIODO ) {
			$SAIDA_PERIODO	= '"05:00 PM Networking Event" ]],';
			array_push ($SAIDA_EVENTOS, $SAIDA_PERIODO );
		}
		$GUARDA_TRILHA  = $TRILHA;
		$SAIDA_TRILHA	= '["title": "Track '. $GUARDA_TRILHA .'","data" : [';
		array_push ($SAIDA_EVENTOS, $SAIDA_TRILHA );
	}
	if( $GUARDA_PERIODO		!= $PERIODO ) { 
		$GUARDA_PERIODO		 = $PERIODO;
		
		if( $PERIODO == 'V' ) {
			$SAIDA_PERIODO 	= '"12:00 PM Lunch",';
			if( $IMPRIME_PERIODO ) {
				array_push ($SAIDA_EVENTOS, $SAIDA_PERIODO );
			}
		}
	}
	$EVENTO = '"'.date_format( date_create( $HORA_INICIO ) ,"H:i A " ) .$PALESTRA. ' ' .$DURACAO. 'min",';
	array_push ($SAIDA_EVENTOS, $EVENTO );
	$IMPRIME_PERIODO= 1;	
}

$SAIDA_PERIODO	= '"05:00 PM Networking Event"]]]]';
array_push ($SAIDA_EVENTOS, $SAIDA_PERIODO );
foreach( $SAIDA_EVENTOS as $ev) {
	echo $ev. '<br>';
}
echo '<br><br><br>JSON object:<br>';
var_dump( json_encode( $SAIDA_EVENTOS ) );

echo '<br><br><br>Array:<br>';
var_dump(  $SAIDA_EVENTOS  );

//var_dump( $SAIDA_EVENTOS );
echo '<br>processo Terminado';
///////////////////////////////////   FUNCOES UTILIZADAS ///////////////////////////////////

function ApagaTabelaBD(){
	global $link;
	$apaga_palestra = "DELETE FROM `palestras` WHERE 1";
	$apaga_palestra_q = mysqli_query($link,$apaga_palestra);
	if (!$apaga_palestra_q){
		echo 'Erro na Exclusão das Palestras Anteriores na tabela: Erro=> '.mysqli_error($link);
		echo ' "<script>javascript:history.back(-1)</script>"';
		mysqli_close($link);
		die();
	}
	$apaga_parametros = "UPDATE `parametros` 
							SET `max_t`=100,
								`ultima_trilha`=1,
								`ultimo_periodo`='M',
								`iniciei_processo`='0',
								`minimo_horario`='16:00:00' 
						WHERE 	1";
	$apaga_parametros_q = mysqli_query($link,$apaga_parametros);
	if (!$apaga_parametros_q){
		echo 'Erro na Exclusão dos Parâmetros Anteriores na tabela: Erro=> '.mysqli_error($link);
		echo ' "<script>javascript:history.back(-1)</script>"';
		mysqli_close($link);
		die();
	}
	$apaga_periodos = "DELETE FROM `periodos` WHERE 1";
	$apaga_periodos_q = mysqli_query($link,$apaga_periodos);
	if (!$apaga_periodos_q){
		echo 'Erro na Exclusão de Trilhs / Perídoso Anteriores na tabela: Erro=> '.mysqli_error($link);
		echo ' "<script>javascript:history.back(-1)</script>"';
		mysqli_close($link);
		die();
	}
	$link -> commit();
}

function CarregarTabelaBD(){
	global $PALESTRA, $DURACAO, $link;

	if( $DURACAO >= 60 ) {
		$t	= str_pad(intval( $DURACAO / 60 ), 2, "0", STR_PAD_LEFT );
																		//ECHO '<br><br>'.fmod( intval( $DURACAO ), 60 );   // fmod( $DURACAO , 60 )
		$t2	= str_pad($DURACAO - ( intval( $DURACAO / 60 ) * 60 ) , 2, "0" ,STR_PAD_LEFT );
		$TEMPO = ($t.':'. $t2.':00' );
	}else{
		$TEMPO = '00:'.$DURACAO.':00';
	}
																		//ECHO '<br>'.$TEMPO;
	$achei = false;
	// verificar duplicidade => $achei = true
	
	if( !$achei ) {
		// insere palestra na tabela palestras
		$insere_palestra = "INSERT INTO `palestras`(`palestra`, `duracao`)
											VALUES ('$PALESTRA','$DURACAO')";
		$insere_palestra_q = mysqli_query($link,$insere_palestra);
		if (!$insere_palestra_q){
			echo 'Erro na Inserção da Palestra na tabela: Erro=> '.mysqli_error($link);
			echo ' "<script>javascript:history.back(-1)</script>"';
			mysqli_close($link);
			die();
		}
	}else{
		echo 'Erro! Palestra Já Consta na Tabela';
	}
}

function CriaPeriodo() {
	global $link;
	
	// ler parametros
	$busca_parametros = "SELECT * FROM `parametros` WHERE 1";
	$busca_parametros_q = mysqli_query($link,$busca_parametros);
	if (!$busca_parametros_q){
		echo 'Erro na Busca dos Parâmetros: Erro => '.mysqli_error($link);
		echo ' "<script>javascript:history.back(-1)</script>"';
		mysqli_close($link);
		die();
	}
	$linha_parametros	=	mysqli_fetch_array($busca_parametros_q);
	$ULTIMA_TRILHA		=	$linha_parametros ['ultima_trilha'];
	$ULTIMO_PERIODO		=	$linha_parametros ['ultimo_periodo'];
	$MAX_T				=	$linha_parametros ['max_t'];
	$INICIEI_PROCESSO	= 	$linha_parametros ['iniciei_processo'];
																				
	// ajustar novo período
	if( $INICIEI_PROCESSO ) {
		if( $ULTIMO_PERIODO == 'V' ) {
			$ULTIMA_TRILHA 	= $ULTIMA_TRILHA + 1;
			$ULTIMO_PERIODO = 'M';
		}else{ 
			$ULTIMO_PERIODO = 'V';
		}
	}
	if( $ULTIMA_TRILHA > $MAX_T  ) {   // MOSTRA ERRO
		echo 'Erro: Foram Preenchidos Todas as '.$MAX_T.' Trilhas Permitidas!';
		return false;
	}
	if( $ULTIMO_PERIODO == 'M' ) {
		$MAXIMO_HORARIO =  '12:00:00';
		$MINIMO_HORARIO =  '09:00:00';
		$TEMPO_RESTANTE	=  180;
		
	}elseif ($ULTIMO_PERIODO == 'V' ) {
		$MAXIMO_HORARIO =  '17:00:00';            //(nunca terminar antes das 16:00h) 
		$MINIMO_HORARIO =  '13:00:00';
		$TEMPO_RESTANTE	=  240;
	}
	$ULTIMO_HORARIO  =  date_create();
	// gravar parametros
	$atualiza_parametros = "UPDATE 	`parametros` 
								SET `ultima_trilha`		= '$ULTIMA_TRILHA',
									`ultimo_periodo`	= '$ULTIMO_PERIODO',
									`iniciei_processo`	= '1',
									`minimo_horario`	= '$MINIMO_HORARIO' 
							WHERE 	1";
	$atualiza_parametros_q = mysqli_query($link,$atualiza_parametros);
	if (!$atualiza_parametros_q){
		echo 'Erro na Atualização dos Parâmetros: Erro => '.mysqli_error($link);
		echo ' "<script>javascript:history.back(-1)</script>"';
		mysqli_close($link);
		die();
	}
	// inserir novo periodo
	$cria_periodo = "INSERT INTO `periodos`	(`trilha`			, `periodo`			, `ultimo_horario`	, `tempo_restante`	, `maximo_horario`, `minimo_horario`) 
					VALUES 					('$ULTIMA_TRILHA'	,'$ULTIMO_PERIODO'	, '$MINIMO_HORARIO'	, '$TEMPO_RESTANTE'	, '$MAXIMO_HORARIO','$MINIMO_HORARIO')";
	$acria_periodo_q = mysqli_query($link,$cria_periodo);
	if (!$acria_periodo_q){
		echo 'Erro na Criacao de Periodo com Tempo Restante Suficiente: Erro => '.mysqli_error($link);
		echo ' "<script>javascript:history.back(-1)</script>"';
		die();
	}
	$link -> commit();
	return true;
}

function AnotaPeriodoNaPalestra() {
	global $PALESTRA, $TRILHA, $PERIODO, $ULTIMO_HORARIO, $HORA_FINAL, $link;
	
	// gravar na palestra em tratamento a trilha, período e o horário de inicio dela
	$HI = date_format( date_create( $ULTIMO_HORARIO ), 'H:i' );
	$HF = date_format( $HORA_FINAL , 'H:i' );
	$atualiza_palestra = "UPDATE	`palestras` 
							SET 	`trilha`		='$TRILHA',
									`periodo`		='$PERIODO',
									`hora_inicio`	='$HI',
									`hora_fim`		='$HF'
									
							WHERE 	`palestra` 		='$PALESTRA' ";
	$atualiza_palestra_q = mysqli_query($link,$atualiza_palestra);
	if (!$atualiza_palestra_q){
		echo 'Erro na Atualização da Palestra com a Trilha / Período Encontrados: Erro=> '.mysqli_error($link);
		echo ' "<script>javascript:history.back(-1)</script>"';
		mysqli_close($link);
		die();
	}
	$link -> commit();
	return true;
}
function AtualizaPeriodo() {

	global $TRILHA, $PERIODO, $HORA_FINAL, $TEMPO_RESTANTE, $link;

	$H = date_format( $HORA_FINAL , 'H:i' );
	// gravar na palestra em tratamento a trilha, período e o horário de inicio dela
	$atualiza_periodo = "UPDATE 	`periodos` 
							SET 	`tempo_restante`= '$TEMPO_RESTANTE',
									`ultimo_horario`= '$H'
							WHERE 	`trilha` 		= '$TRILHA' and
									`periodo`		= '$PERIODO'";
									
	$atualiza_periodo_q = mysqli_query($link,$atualiza_periodo);
	if (!$atualiza_periodo_q){
		echo 'Erro na Atualização da Trilha/Período com dados da Palestra: Erro=> '.mysqli_error($link);
		echo ' "<script>javascript:history.back(-1)</script>"';
		mysqli_close($link);
		die();
	}
	$link -> commit();
	return true;
}
/* 
	/////////////////////////////////////////////  exemplo de Entrada //////////////////////////
	$Entrada:
	{
	   ​"data"​ :[  
		   ​"Writing Fast Tests Against Enterprise Rails 60min"​,  
		   ​"Overdoing it in Python 45min"​,  
		   ​"Lua for the Masses 30min"​,  
		   ​"Ruby Errors from Mismatched Gem Versions 45min"​,  
		   ​"Common Ruby Errors 45min"​,  
		   ​"Rails for Python Developers lightning"​,  
		   ​"Communicating Over Distance 60min"​,  
		   ​"Accounting-Driven Development 45min"​,  
		   ​"Woah 30min"​,  
		   ​"Sit Down and Write 30min"​,  
		   ​"Pair Programming vs Noise 45min"​,  
		   ​"Rails Magic 60min"​,  
		   ​"Ruby on Rails: Why We Should Move On 60min"​,  
		   ​"Clojure Ate Scala (on my project) 45min"​,  
		   ​"Programming in the Boondocks of Seattle 30min"​,  
		   ​"Ruby vs. Clojure for Back-End Development 30min"​,  
		   ​"Ruby on Rails Legacy App Maintenance 60min"​,  
		   ​"A World Without HackerNews 30min"​,  
		   ​"User Interface CSS in Rails Apps 30min"  
	   ]  
	}  
/////////////////////////////////////////////  exemplo de Saida //////////////////////////

"data"​ : [  
       {  
           ​"title"​ : ​"Track 1"​,  
           ​"data"​ : [  
               ​"09:00AM Writing Fast Tests Against Enterprise Rails 60min"​,  
               ​"10:00AM Overdoing it in Python 45min"​,  
               ​"10:45AM Lua for the Masses 30min"​,  
               ​"11:15AM Ruby Errors from Mismatched Gem Versions 45min"​,  
               ​"12:00PM Lunch"​,  
               ​"01:00PM Ruby on Rails: Why We Should Move On 60min"​,  
               ​"02:00PM Common Ruby Errors 45min"​,  
               ​"02:45PM Pair Programming vs Noise 45min"​,  
               ​"03:30PM Programming in the Boondocks of Seattle 30min"​,  
               ​"04:00PM Ruby vs. Clojure for Back-End Development 30min"​,  
               ​"04:30PM User Interface CSS in Rails Apps 30min"​,  
               ​"05:00PM Networking Event"  
           ]  
       },  
       {  
           ​"title"​ : ​"Track 2"​,  
           ​"data"​ : [  
               ​"09:00AM Communicating Over Distance 60min"​,  
               ​"10:00AM Rails Magic 60min"​,  
               ​"11:00AM Woah 30min"​,  
               ​"11:30AM Sit Down and Write 30min"​,  
               ​"12:00PM Lunch"​,  
               ​"01:00PM Accounting-Driven Development 45min"​,  
               ​"01:45PM Clojure Ate Scala (on my project) 45min"​,  
               ​"02:30PM A World Without HackerNews 30min"​,  
               ​"03:00PM Ruby on Rails Legacy App Maintenance 60min"​,  
               ​"04:00PM Rails for Python Developers lightning"​,  
               ​"05:00PM Networking Event"  
           ]  
       }  
       ​//Others tracks if necessary                  
   ]  
} 
*/
?>