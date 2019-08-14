<?php
/*
   ┌────────────────────────────────────────┐ 
   │ Bot                                    │ 
   │ Copyright © 2019 Maurício Garcia       │ 
   │ SOLVERTANK                             │ 
   └───────────────────────────────────--───┘ 
*/

//recuperando a questão
$question = $_POST['question'];



//carregando o arquivo com perguntas e respostas
//essa é a base de conhecimento ("knowledge base")

//a eficiência do bot depende desse arquivo
//quanto mais linhas nesse arquivo, mais eficiência
//tipicamente, para resultados satisfatórios, precisa ter pelo menos 1.000 linhas
//nesse exemplo há apenas poucas linhas, somente para entender o mecanismo

//aqui foi usado o formato TXT por ser mais simples, mas pode ser um banco de dados

$lines = array_map( function( $v ){ return str_getcsv( $v, "\t" ); }, file( 'data.txt' ) );




//calculando a similaridade

//essa rotina percorre todas as linhas da base de conhecimento para localizar
//qual delas tem a maior similaridade

$num = 0;
$max = 0;
$pos = 0;
$percent = 0;
foreach ( $lines as $line ){
	$percent = similaridadeCosine( $question, $line[0] );
	if ( $percent > $max ) {
		$max = $percent;
		$pos = $num;
	}
	$num++;
}



//exibindo a resposta se a similaridade for acima de 75%
//esse valor pode ser alterado para mais ou para menos, dependendo da sensibilidade desejada
if ( $max > 0.75 ) {
	$answer = $lines[$pos][1]; 
}
else {
	$answer = 'Desculpe, não compreendi o que você quis dizer.';
	//Isso é o chamado "transbordo", ou seja, perguntas não respondidas
	
	//aqui pode ser criada uma rotina para serem tratadas as perguntas não respondidas
	//por exemplo, pode-se salvar a pergunta em uma base de dados e alguém analisar diariamente
	//para responder e incorporar a resposta na base de conhecimento (arquivo data.txt)
	//É assim que o robô "aprende"!!

	//pode também direcionar a pergunta para um email, para que alguém responda a quem perguntou
}
echo $answer;







//esse é o coração do chatbot, o algoritmo que calcula a similaridade entre duas frases

//aqui está sendo usado o algoritmo Cosine (cosseno)
//esse algoritmo transforma cada frase em uma reta e calcula o cosseno do ângulo entre elas
//quanto mais próximas as retas, menor o ângulo e, portanto, maior o cosseno

//existem outros algoritmos de similaridade: Jaccard, Levenshtein, Jaro Winkler, Smith Waterman Gotoh
//o próprio PhP tem um bem simples através da função similar_text() 

function similaridadeCosine( $A, $B ) {
	
	$A = prepara( $A ); //remove pontuação e acentos
	$B = prepara( $B ); //remove pontuação e acentos

	$A = preg_split( '/[\pZ\pC]+/u', $A , null, PREG_SPLIT_NO_EMPTY ); //cria o token
	$B = preg_split( '/[\pZ\pC]+/u', $B , null, PREG_SPLIT_NO_EMPTY ); //cria o token
	
    if ( is_int( key( $A ) ) ) {
        $v1 = array_count_values( $A );
	}
    else {
        $v1 = &$A;
	}
	
    if ( is_int( key( $B ) ) ) {
        $v2 = array_count_values( $B );
	}
    else {
        $v2 = &$B;
	}
	
    $prod = 0.0;

    $v1_norm = 0.0;
    foreach ( $v1 as $i=>$xi ) {
        if ( isset( $v2[$i] ) ) {
            $prod += $xi * $v2[$i];
        }
        $v1_norm += $xi * $xi;
    }
    $v1_norm = sqrt( $v1_norm );

    $v2_norm = 0.0;
    foreach ( $v2 as $i=>$xi ) {
        $v2_norm += $xi * $xi;
    }
    $v2_norm = sqrt( $v2_norm );

	if ( $v1_norm * $v2_norm == 0 ) {
		return 0;
	}
	else {
		return $prod / ( $v1_norm * $v2_norm );
	}
}




//removendo pontuação e acentos
function prepara( $str ) {
	$str = strtolower( $str );
	$str = preg_replace( "/(?![.=$'€%-])\p{P}/u", "", $str ); //remove pontuação
	$str = strtr(utf8_decode( $str ), utf8_decode( 'àáâãäçèéêëìíîïñòóôõöùúûüýÿ' ), 'aaaaaceeeeiiiinooooouuuuyy' ); //remove acentos
	return $str;
}






?>

