<?php 
include 'funcoes_padrao.php';

// chama cursos.php

	$A1		= 'Writing Fast Tests Against Enterprise Rails 60min';
	$A2		= 'Overdoing it in Python 45min';
	$A3		= 'Lua for the Masses 30min';
	$A4		= 'Ruby Errors from Mismatched Gem Versions 45min';
	$A5		= 'Common Ruby Errors 45min';
	$A6		= 'Rails for Python Developers lightning';
	$A7		= 'Communicating Over Distance 60min';
	$A8		= 'Accounting-Driven Development 45min';
	$A9		= 'Woah 30min';
	$A10	= 'Sit Down and Write 30min';
	$A11	= 'Pair Programming vs Noise 45min';
	$A12	= 'Rails Magic 60min';
	$A13	= 'Ruby on Rails: Why We Should Move On 60min';
	$A14	= 'Clojure Ate Scala (on my project) 45min';
	$A15	= 'Programming in the Boondocks of Seattle 30min';
	$A16	= 'Ruby vs. Clojure for Back-End Development 30min';
	$A17	= 'Ruby on Rails Legacy App Maintenance 60min';
	$A18	= 'A World Without HackerNews 30min';
	$A19	= 'User Interface CSS in Rails Apps 30min';

	$Entrada->data =  [ $A1,$A2,$A3,$A4,$A5,$A6,$A7,$A8,$A9,$A10,$A11,$A12,$A13,$A14,$A15,$A16,$A17,$A18,$A19  ];

$PARAM = json_encode( $Entrada ) ;
$chama = 'index.php?Entrada='.$PARAM;
/*
AbreJanela($chama,_self);
*/
?>
<!DOCTYPE html>
<html>
<body>

<p>Clique para rodar a API</p>

<button onclick="myFunction()">API</button>

<script>
function myFunction() {

	Entrada = {
		"data":[	"Ford", "BMW", "Fiat",'VW' ]
	};
/*		
	Entrada = {
	"data":[	"Writing Fast Tests Against Enterprise Rails 60min",  
				​"Overdoing it in Python 45min",  
				​"Lua for the Masses 30min",  
				​"Ruby Errors from Mismatched Gem Versions 45min",  
				​"Common Ruby Errors 45min",  
				​"Rails for Python Developers lightning",  
				​"Communicating Over Distance 60min",  
				​"Accounting-Driven Development 45min",  
				​"Woah 30min",  
				​"Sit Down and Write 30min",  
				​"Pair Programming vs Noise 45min",  
				​"Rails Magic 60min",  
				​"Ruby on Rails: Why We Should Move On 60min",  
				​"Clojure Ate Scala (on my project) 45min",  
				​"Programming in the Boondocks of Seattle 30min",  
				​"Ruby vs. Clojure for Back-End Development 30min",  
				​"Ruby on Rails Legacy App Maintenance 60min",  
				​"A World Without HackerNews 30min",  
				​"User Interface CSS in Rails Apps 30min'  
		   ]
	}
*/

  var myWindow = window.open('<?php echo $chama ?>', '_self');
}
</script>

</body>
</html>