<?php

//Define los nombres de las columnas de la tabla de fondos (fondos.csv)
///////////////////////////////////////////////////////////////////////////////
define ("NOMBRE_FONDO", "NOMBRE DEL FONDO COMPLETO");
define ("ARCHIVO_ESCRITURA", "Archivo");
define ("GESTORA", "SOCIEDAD GESTORA");
define ("BANCOS", "BANCO(S) CEDENTE(S)");
define ("PAG_INI", "Nº DE PÁGINA INICIO LISTA DE PRÉSTAMOS EN EL PDF");
define ("PAG_FIN", "Nº DE PÁGINA FINAL LISTA DE PRÉSTAMOS EN EL PDF");
define ("ID_CREDITO", "Nº interno/ secuencial");
define ("FECHA_PRESTAMO", "Fecha préstamo");
define ("VENCIMIENTO", "Vencimiento / Amortización");
define ("GARANTIA", "Tipo préstamo (Garantía)");
define ("CAPITAL_INICIAL", "Capital inicial");
define ("MUNICIPIO", "Municipio");
define ("CP", "Código Postal");
define ("DOMICILIO", "Domicilio");
define ("REG_TOMO", "Tomo");
define ("REG_LIBRO", "Libro");
define ("REG_FOLIO", "Folio");
define ("REG_FINCA", "Nº finca");
define ("REG_INSCRIPCION", "Nº inscripción");
define ("REG_REGISTRO", "Registro");
define ("TITULAR", "Titular");
define ("TIPO_PRESTAMO", "Tipo");
define ("PROCESABLE", "Procesable");
/////////////////////////////////////////////////////////////////////////////
$cabecera_fondos = array ();

//Estructura del archivo de salida////////////////////////////////////////////////////////////
//Indica de donde salen, si de la tabla de fondos (t) o de la escritura (e)
$campos_salida = array (
  NOMBRE_FONDO => "t",
  GESTORA => "t",
  BANCOS => "t",
  ID_CREDITO => "e",
  TIPO_PRESTAMO => "t",
  FECHA_PRESTAMO => "e",
  VENCIMIENTO => "e",
  GARANTIA => "e",
  CAPITAL_INICIAL => "e",
  REG_REGISTRO => "e",
  REG_TOMO => "e",
  REG_LIBRO => "e",
  REG_FOLIO => "e",
  REG_FINCA => "e",
  REG_INSCRIPCION => "e",
  TITULAR => "e",
  DOMICILIO => "e",
  MUNICIPIO => "e",
  CP => "e"
  //Página, pero no aparece en esta cabecera.
  );
  
  

//
function procesafichero ($datosfich){
  global $cabecera_fondos;
  global $campos_salida;
  $campos_entrada = explode ("\t", $datosfich);
  $archivo = $campos_entrada[$cabecera_fondos[ARCHIVO_ESCRITURA]];
  $pag_ini = $campos_entrada[$cabecera_fondos[PAG_INI]];
  $pag_fin = $campos_entrada[$cabecera_fondos[PAG_FIN]];
echo "Procesando $archivo\n";
 for ($i = $pag_ini;$i <= $pag_fin;$i++){
  echo "Procesando página $i\n";
  $output = array ();
  $retval = 0;
  $tabla = array ();
  //$ret = exec ("pdf2txt -p 1 -t xml unencrypted.pdf", $output, $retval);
  //$ret = exec ("pdftohtml -f 1 -l 1 -nodrm -noframes -xml -stdout GAT_FTGENCAT_2009__FONDO_DE_TITULIZACION_DE_ACTIVOS.pdf", $output, $retval);
  $cmd = "pdftohtml -nodrm -f $i -l $i -noframes -xml -stdout $archivo";
  
  $ret = exec ($cmd, $output, $retval);
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
      
    if (empty ($texto) && $texto != 0)
      continue;
    
    
    /*if (!is_numeric ($texto[0])){
      if ($texto[0] == '[' && !$encabecera){
        if ($cabecera)
          continue;
        else
          $cabecera = true;
      }
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
  
  $f = fopen ($archivo . ".csv", "a");
  
  foreach ($tabla as $fila){


    $linea = implode ("\t", $fila);
    if (empty ($linea))
      continue;
    if ($linea[0] != '[' && $linea[0] != '-' && !is_numeric ($linea[0]))
      $linea = str_replace ("\t", " ", $linea);
    if (empty ($linea)){
      fclose ($f);
      continue;
    }
    $csal = explode ("\t", $linea);
    //fwrite ($f, $linea . "\t");
    foreach ($campos_salida as $clave => $valor){
      $posicion = $cabecera_fondos[$clave];
      
      if ($valor == "t"){
        if (isset ($campos_entrada[$posicion]))
          fwrite ($f, $campos_entrada[$posicion]);
        fwrite ($f, "\t");
      }
      else{
        if (empty ($campos_entrada[$posicion]) || !isset ($csal[$campos_entrada[$posicion]-1]))
          fwrite ($f, "\t");
        else{
          fwrite ($f, $csal[$campos_entrada[$posicion]-1]);
          fwrite ($f, "\t");
        }
      }
    }
    //fwrite ($f, "\t");
    fwrite ($f, $i);
    fwrite ($f, "\n");
  }
  fclose ($f);
  }
}  
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

  $f = fopen ("fondos.csv", "r");
  $linea = fgets ($f); //la primera línea contiene la cabecera
  $campos = explode ("\t", $linea);
  $i = 0;
  foreach ($campos as $campo){
    $cabecera_fondos[$campo] = $i;
    $i++;
  }

  while (($linea = fgets ($f)) !== false){
    procesafichero ($linea);
  }
