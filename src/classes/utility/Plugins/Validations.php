<?php
namespace OpenApi\classes\utility\Plugins;
class Validations {
  function italianFiscalCode($cf){
    if($cf=='') {
      return false;
    }
 

    if(strlen($cf)!= 16) {
      return false;
    }
 
    $cf=strtoupper($cf);
    if(!preg_match("/[A-Z0-9]+$/", $cf)) {
      return false;
    }
 
    $s = 0;
    for($i=1; $i<=13; $i+=2){
      $c=$cf[$i];
      if('0'<=$c and $c<='9'){
        $s+=ord($c)-ord('0');
      }
      else{
        $s+=ord($c)-ord('A');
      }
      
    }

    for($i=0; $i<=14; $i+=2){
    $c=$cf[$i];
    switch($c){
                case '0':  $s += 1;  break;
          case '1':  $s += 0;  break;
                case '2':  $s += 5;  break;
          case '3':  $s += 7;  break;
          case '4':  $s += 9;  break;
          case '5':  $s += 13;  break;
          case '6':  $s += 15;  break;
          case '7':  $s += 17;  break;
          case '8':  $s += 19;  break;
          case '9':  $s += 21;  break;
          case 'A':  $s += 1;  break;
          case 'B':  $s += 0;  break;
          case 'C':  $s += 5;  break;
          case 'D':  $s += 7;  break;
          case 'E':  $s += 9;  break;
          case 'F':  $s += 13;  break;
          case 'G':  $s += 15;  break;
          case 'H':  $s += 17;  break;
          case 'I':  $s += 19;  break;
          case 'J':  $s += 21;  break;
          case 'K':  $s += 2;  break;
          case 'L':  $s += 4;  break;
          case 'M':  $s += 18;  break;
          case 'N':  $s += 20;  break;
          case 'O':  $s += 11;  break;
          case 'P':  $s += 3;  break;
                case 'Q':  $s += 6;  break;
          case 'R':  $s += 8;  break;
          case 'S':  $s += 12;  break;
          case 'T':  $s += 14;  break;
          case 'U':  $s += 16;  break;
          case 'V':  $s += 10;  break;
          case 'W':  $s += 22;  break;
          case 'X':  $s += 25;  break;
          case 'Y':  $s += 24;  break;
          case 'Z':  $s += 23;  break;
    }
   }

  
    if( chr($s%26+ord('A'))!=$cf[15] ){
      return false;
    }
 

    return true;
  }

  function italianVat($variabile){

    if($variabile==''){
      return false;
    }
      
    if(strlen($variabile)!=11){
      return false;
    }

    if(!is_numeric ($variabile)){
      return false;
    }
      
    $primo=0;
    for($i=0; $i<=9; $i+=2){
      $primo+= ord($variabile[$i])-ord('0');
    }
            
  
    for($i=1; $i<=9; $i+=2 ){{
      $secondo=2*( ord($variabile[$i])-ord('0') );
    }
      

      if($secondo>9){
        $secondo=$secondo-9;
      }
        
      $primo+=$secondo;
  
    }
    if( (10-$primo%10)%10 != ord($variabile[10])-ord('0') ){
      return false;
    }
      
  
    return true;
  
  }
}