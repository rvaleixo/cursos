<?php

function Mensagem($msg){
	echo "<script type=\"text/javascript\">alert('$msg');</script>";
}
function Console($msg){
	echo "<script type=\"text/javascript\">console.log('$msg');</script>";
}
function AbreJanela($msg,$destino){
	echo "<script type=\"text/javascript\">window.open('$msg','$destino');</script>";
}
function RetornaLabel($campo,$tipo) {
	global $LINGUA,$link;
	
	$busca_dicionario = "SELECT * FROM `dicionario` WHERE `lingua` = '$LINGUA' and `campo` = '".$campo."'";
	$busca_dicionario_q = mysqli_query($link,$busca_dicionario);
	if (!$busca_dicionario_q){
		Mensagem('prg:funcoes_padrao:Problema na Busca do Dicionário do campo: '.$campo);
		#echo ' "<script>javascript:history.back(-1)</script>"';
		echo ( mysqli_error($link) );
		mysqli_close($link);
		die();
	}
	$linha_dicionario = mysqli_fetch_array($busca_dicionario_q);
	if ($tipo == 'LBL' ) { return utf8_encode($linha_dicionario['label'] ) ; }
	if ($tipo == 'TTL' ) { return utf8_encode($linha_dicionario['title'] ) ; }
	if( $linha_dicionario['faixa'] != '' ) { // montar a matriz de opções
	}
}

function RetornaDicionario($campo) {
	global $LINGUA,$link;
	if ($campo != '') {
		$busca_dicionario = "SELECT * FROM `dicionario` WHERE `lingua` = '$LINGUA' and `campo` = '".$campo."'";
																					//Console($busca_dicionario);
		$busca_dicionario_q = mysqli_query($link,$busca_dicionario);
		if (!$busca_dicionario_q){
			Mensagem('prg:procura:Problema na Busca do Dicionário do campo: '.$campo);
			#echo ' "<script>javascript:history.back(-1)</script>"';
			echo ( mysqli_error($link) );
			mysqli_close($link);
			die();
		}
		$linha_dicionario = mysqli_fetch_array($busca_dicionario_q);
		$LBL = utf8_encode($linha_dicionario['label']	);
		$TTL = utf8_encode($linha_dicionario['title']	);
		$FX	 = utf8_encode($linha_dicionario['faixa']	);
		$MSG = utf8_encode($linha_dicionario['message'] );
	}else{
		$LBL = '';
		$TTL = '';
		$FX	 = '';
		$MSG = '';
	}	
	return '&'.$LBL.'&'.$TTL.'&'.$FX.'&'.$MSG.'&';

}	
function CarregaImportancia($campo,$CAMPO,$DADO,$COR) { // nome do campo no dicionário | nome do campo na tela | valor do campo gravado
	$DIC	= explode("&" , RetornaDicionario($campo) );
	$LBL	= $DIC[1];
	$TTL	= $DIC[2];
	$FX		= $DIC[3];;
	if( $COR == '') {$C= 'SteelBlue';}else{$C= 'Coral';}
	$RETORNO 	= '<br><br><font size="4" color="'.$C.'">'.$LBL.':&nbsp;&nbsp;</font>
					<select name="'.$CAMPO.'" onclick="AtivaDesfaz()" title="'.$TTL.'" ><br>';

	$LISTA	= explode("|",$FX);
																				///Console($L_P_RACA_IMPORTANCIA);
	for ($i = 0; $i < count( $LISTA) - 1 ; $i++ ) {
		$VALOR	= substr( $LISTA[$i], 0, 1 );
		$LBL	= substr( $LISTA[$i], 1, strlen( $LISTA[$i] ) - 1 );
		if( $VALOR == $DADO ) {  $SELECTED = 'selected';	}else{ $SELECTED = ''; }
		$RETORNO .= '
						<option '.$SELECTED.' value="'.$VALOR.'" name="'.$LBL.'">'.$LBL.'</option>';
	}
	$RETORNO .= '<br>				</select><br><div class="linha"></div>';
	return $RETORNO;
}
	
	
function FormataData($d,$tipo){   // tipo = | só <D>ata  ||  <C>ompleto || <H>ora
												//Console('FormataData:$d'.$d);
	if( $d == '0000-00-00' or $d == '0000-00-00 00:00:000' ) {
		$dd = new DateTime();
	}else{
		$dd = new DateTime($d);
	}
	switch ($tipo){
		case 'C':
			$formato = 'Y-m-d G:i';
			break;
		case 'D':
			$formato = 'Y-m-d';
			break;
		case 'H':
			$formato = 'H:i:s';
			break;
	}
	$r = date_format( $dd,$formato);   
												//Console('FormataData:$r'.$r);
	return $r;
}
function CalculaIdade( $data ) {   // passar data no formato string YYYY:MM:DD
	//Data atual
	$dia = date ('d');
	$mes = date ('m');
	$ano = date ('Y');
	//Data do aniversário
	$dianasc = substr ( $data , 8, 2);
	$mesnasc = substr ( $data , 5, 2);
	$anonasc = substr ( $data , 0, 4);
	//Calculando sua idade
	$idade = $ano - $anonasc;
	if ($mes < $mesnasc){
		$idade--;
		return $idade;
	}elseif ($mes == $mesnasc and $dia <= $dianasc) {
		$idade--;
		return $idade;
	}else{
		return $idade;
	}
}

function AnotaAcesso( $codigo ) {
	global $link,$DISPOSITIVO;
	$DATA_ACESSO = date('Y-m-d G:i:s');
	$anota_cadastro = "UPDATE `cadastro` SET `ultimo_acesso_em` = '$DATA_ACESSO', `ultimo_acesso_dispositivo` = '$DISPOSITIVO' WHERE `codigo` = '$codigo'";
	$anota_cadastro_q = mysqli_query($link,$anota_cadastro);
	if (!$anota_cadastro_q){
		Mensagem('Erro ao anotar o acesso no cadastro código:' .$codigo);
		echo ' "<script>javascript:history.back(-1)</script>"';
		echo mysqli_error($link);
		mysqli_close($link);
		die();
	}
}
function LeCadastro( $codigo ) {
	global $link,$APELIDO,$SEXO,$PREFERENCIA_SEXUAL,$IDADE,$ALTURA,$ESCOLARIDADE,$CEP,$DISTANCIA,$FOTO_AVATAR,$ASSINANTE;
	$busca_cadastro = "SELECT  * FROM cadastro WHERE `codigo` = '$codigo'";
	$busca_cadastro_q = mysqli_query($link,$busca_cadastro);
	if (!$busca_cadastro_q){
		Mensagem('Erro ao ler o cadastro código:' .$codigo);
		echo ' "<script>javascript:history.back(-1)</script>"';
		echo mysqli_error($link);
		mysqli_close($link);
		die();
	}
	$linha_cadastro 	= mysqli_fetch_array($busca_cadastro_q);
	$APELIDO			= $linha_cadastro['apelido'];
	$SEXO				= $linha_cadastro['sexo'];
	$PREFERENCIA_SEXUAL	= $linha_cadastro['preferencia_sexual'];
	$ESCOLARIDADE		= $linha_cadastro['escolaridade'];
	$FOTO_AVATAR		= $linha_cadastro['foto_avatar'];
	$IDADE 				= CalculaIdade( $linha_cadastro['nascimento'] );
	$ALTURA 			= $linha_cadastro['altura'];
	$CEP 				= $linha_cadastro['cep'];
	$ASSINANTE			= $linha_cadastro['status_assinatura'];
	 
}	

function MostraSalvarDesfazer($cor) {
	$salvar_t		= RetornaLabel('salvar','TTL');
															//Console($salvar_t);
	$salvar_l		= RetornaLabel('salvar','LBL');
	$desfaz_t		= RetornaLabel('desfaz','TTL');
	$desfaz_l		= RetornaLabel('desfaz','LBL');
	if( $cor == '' ) { 
		$img_check = 'check.png'; $c= 'blue';
	}else{
		$img_check = 'check_coral.png';$c= $cor;
	}
	echo '
					<div class="col-sm-3 col-xs-3" style="background-color:transparent;">
						<a onclick="Salvar()" title="'.$salvar_t.'">
						<div class="botao" align="center" ><img id="botao_salva" src="images/'.$img_check.'" width="40px"<br><font size="5" color="'.$c.'"><span id="texto_solicitar"><b>'.$salvar_l.'</b></span></font></div>
						</a>
					</div>
					<div class="col-sm-3 col-xs-3" style="background-color:transparent;"align="left">
						<a onclick=""  id="link_desfaz" title="'.$desfaz_t.'">
						<div class="botao_inativo" align="center" ><img id="botao_desfaz" src="images/desfaz_disabled.png" width="40px"<br><span id="texto_desfaz" style="font-size:25px; color: gray; font-weight:bold">'.$desfaz_l.'</span></div>
						</a>
					</div>
	';
}

function VerificaNavegadorSO1() {
	global $DISPOSITIVO,$NAV;
	
    $ip = $_SERVER['REMOTE_ADDR'];

    $u_agent = $_SERVER['HTTP_USER_AGENT'];
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version= "";

    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'Linux';
    }
    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'Mac';
    }
    elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'Windows';
    }


    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)){
        $bname = 'Internet Explorer';
        $ub = "MSIE";
    }
    elseif(preg_match('/Firefox/i',$u_agent)){
        $bname = 'Mozilla Firefox';
        $ub = "Firefox";
    }
    elseif(preg_match('/Chrome/i',$u_agent)){
        $bname = 'Google Chrome';
        $ub = "Chrome";
    }
    elseif(preg_match('/AppleWebKit/i',$u_agent)){
        $bname = 'AppleWebKit';
        $ub = "Opera";
    }
    elseif(preg_match('/Safari/i',$u_agent)){
        $bname = 'Apple Safari';
        $ub = "Safari";
    }
    elseif(preg_match('/Netscape/i',$u_agent)){
        $bname = 'Netscape';
        $ub = "Netscape";
    }

    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) .
    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
    }


    $i = count($matches['browser']);
    if ($i != 1) {
        if (strripos(chr($u_agent,"Version")) < strripos(chr($u_agent),$ub)){
            $version= $matches['version'][0];
        }else{
            $version= $matches['version'][1];
        }
    }else{
        $version= $matches['version'][0];
    }

    // check if we have a number
    if ($version==null || $version=="") {$version="?";}

    $Browser = array(
            'userAgent' => $u_agent,
            'name'      => $bname,
            'version'   => $version,
            'platform'  => $platform,
            'pattern'   => $pattern
    );

    $navegador = "Navegador: " . $Browser['name'] . " " . $Browser['version'];
    $so = "SO: " . $Browser['platform'];
	
	if($DISPOSITIVO ==''){
		$iphone 	= strpos($_SERVER['HTTP_USER_AGENT'],"iPhone");
		$ipad 		= strpos($_SERVER['HTTP_USER_AGENT'],"iPad");
		$android 	= strpos($_SERVER['HTTP_USER_AGENT'],"Android");
		$palmpre 	= strpos($_SERVER['HTTP_USER_AGENT'],"webOS");
		$berry 		= strpos($_SERVER['HTTP_USER_AGENT'],"BlackBerry");
		$ipod 		= strpos($_SERVER['HTTP_USER_AGENT'],"iPod");
		$symbian 	=  strpos($_SERVER['HTTP_USER_AGENT'],"Symbian");

		if ($iphone || $android || $palmpre || $ipod || $berry || $symbian == true){
			$DISPOSITIVO = 'MOVEL';
		}else{
			if($ipad){
				$DISPOSITIVO = 'TABLET';
			}else{
				$DISPOSITIVO = 'PC';
			}
		}
		if($iphone || $ipad){
			if($iphone){
				$NAV = 'APPLE';
			}else{
				$NAV = 'TABLET';
			}
		}else{
			$NAV = '';
		}
	}
																			//Mensagem('dispositivo='.$DISPOSITIVO.'\nNavegador'.$navegador.'\n'.$so);
}
function mask($val, $mask)
{
 $maskayellow = '';
 $k = 0;
 for($i = 0; $i<=strlen($mask)-1; $i++)
 {
 if($mask[$i] == '#')
 {
 if(isset($val[$k]))
 $maskayellow .= $val[$k++];
 }
 else
 {
 if(isset($mask[$i]))
 $maskayellow .= $mask[$i];
 }
 }
 return $maskayellow;
}

?>