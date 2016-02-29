<?php
  $output = array ();
  $retval = 0;
  $tabla = array ();
  //$ret = exec ("pdf2txt -p 1 -t xml unencrypted.pdf", $output, $retval);
  //$ret = exec ("pdftohtml -f 1 -l 1 -nodrm -noframes -xml -stdout GAT_FTGENCAT_2009__FONDO_DE_TITULIZACION_DE_ACTIVOS.pdf", $output, $retval);
  $ret = exec ("pdftohtml -nodrm -noframes -xml -stdout GAT_FTGENCAT_2009__FONDO_DE_TITULIZACION_DE_ACTIVOS.pdf", $output, $retval);
  $n = -1;
  //if ($retval != 0)
  $topant = 0;
  $tag = '<text';
  $cabecera = false;
  $encabecera = true;
  echo "Hay ". count ($output) . " líneas\n";
  foreach ($output as $linea){
    $linea = trim ($linea);
    //if (strpos ($linea, '<text ') == false)

    if (strncmp ($linea, $tag , strlen ($tag)) != 0)
      continue;

    //echo "procesada " . ($n +1) . "\n";
    $datos = procesalinea ($linea);
    if ($datos == false)
      continue;

    if ($topant != $datos["top"]){
      $n++;
      $tabla[$n] = array ();
      $topant = $datos["top"];
    }

    if ($datos["texto"][0] == ' ')
      $tabla[$n][] = '';

    $texto = trim ($datos["texto"]);

    //quita espacios dobles
    $cuantos = 1;

    while ($cuantos > 0)
      $texto = str_replace ('  ', ' ', $texto, $cuantos);

    if (empty ($texto))
      continue;


    /*if (!is_numeric ($texto[0])){
      if ($texto[0] == '[' && !$encabecera){
        if ($cabecera)
          continue;
        else
          $cabecera = true;
      }
      else
        continue;
    }
    else {
      $encabecera = false;
    }*/


    $campos = explode (' ', $texto);

    foreach ($campos as $campo)
      $tabla[$n][] = $campo;
  }

  //la escribe
  $cab1= array();
  $cab2= array();
  $c=0;

  $f = fopen ("salida.csv", "w");

  foreach ($tabla as $fila){
    $linea = implode ("\t", $fila);
    if (empty ($linea))
      continue;
    if ($linea[0] == '[' || $linea[0] == '-' || !is_numeric ($linea[0])) {
      if (!is_numeric ($linea[0])) $linea = str_replace ("\t", " ", $linea);
      $cab2[$c++]=$linea;
    } else {
		if (count($cab2)>0 && count(array_diff($cab2, $cab1))>0){
		  foreach ($cab2 as $cab) {
			fwrite ($f, $cab);
			fwrite ($f, "\n");
		  }
		  $cab1=$cab2;
		}
		fwrite ($f, $linea);
		fwrite ($f, "\n");
		$c=0;
		$cab2= array();
	}
  }
  fclose ($f);

  function procesalinea ($linea){

    $claves = array ('top', 'left', 'width', 'height');
    $ret = array ();
    foreach ($claves as $clave){
      $claveplus = $clave . '="';
      $ini = strpos ($linea, $claveplus);
      if ($ini == false)
        return false;

      $ini += strlen ($claveplus);

      $fin = strpos ($linea, '"', $ini);

      $ret[$clave] = floatval (substr ($linea, $ini, $fin));

    }

    $ini = strpos ($linea, '>') + 1;
    if ($ini == false)
      return false;
    //$fin = strpos ($linea, '<', $ini + 1);
    $fin = strpos ($linea, '</');
    if ($fin == false)
      return false;
    /*echo $linea . "\n";
    echo $ini . "\n";
    echo $fin . "\n";*/
    $ret["texto"] = substr ($linea, $ini, $fin-$ini);

    $ret["media"] = $ret["width"] / strlen ($ret["texto"]);
    return $ret;


  }
