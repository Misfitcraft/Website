<?php 
  $hostname="170.130.139.221";
  $port=25565;
  
  //Varints are little endian, this is -1
  $handshake_ver=array(chr(0xff), chr(0xff), chr(0xff), chr(0xff), chr(0x0f));
  
  function encode_int($int)
  {
    $val = $int;
    $firstbyte = true;
    $bytestr = array();
    /*derived from wiki.vg pseudocode*/
    do
    {
      $b = chr($val & 0b01111111);
      $val >>= 7;
      
      if($firstbyte && $int < 0)
      {
        $val &= 0b01111111111111111111111111111111; /*We don't want to preserve the sign bit (copy) and PHP will do that in it's bitshifts*/
        $firstbyte = false;
      }
      
      if ($val != 0)
      {
          $b |= 0b10000000;
      }
      
      $bytestr[] = $b;
    } 
    while ($val != 0);
    
    return $bytestr;
  }
  
  function read_int($sock)
  {
    /*copied af from wiki.vg pseudocode*/
    
    $num_read = 0;
    $result = 0;
    $read = 0;
    
    do
    {
        $read = ord(fread($sock, 1));
        $value = ($read & 0b01111111);
        $result |= ($value << (7 * $num_read));

        $num_read++;
        
        if ($num_read > 5) 
        {
            exit("Malformed response");
        }
    }
    while (($read & 0b10000000) != 0);

    return $result;
  }
  
  /*Takes the hostname string and turns it into an mc format bytestring*/
  function serialize_hostname($str)
  {
    $utf_string = utf8_encode($str);
    
    $len = strlen($utf_string);
    
    $result = encode_int($len);
    
    for ($i=0; $i < strlen($utf_string); $i++)
    { 
      $result[] = $utf_string[$i];
    }
    
    return $result;
  }
  
  /*Splits a short into it's most and least significant byte and returns it as a bytestring in big-endian form*/
  function serialize_port($portnum)
  {
      //short is big endian
      $msB = chr($portnum >> 8);
      $lsB = chr($portnum & 0b11111111);
      
      return array($msB, $lsB);
  }
  
  /*Open a socket to the server*/
  $sock = fsockopen($hostname, $port, $errno, $errstr, 3);
  
  if(!$sock)
  {
    exit("Server down");
  }
  
  $handshake_payload = array_merge($handshake_ver, serialize_hostname($hostname), serialize_port($port));
  $handshake_payload[] = chr(0x01);
  $handshake_header = encode_int(count($handshake_payload) + 1);
  $handshake_header[] = chr(0x00);
  
  $handshake_payload = array_merge($handshake_header, $handshake_payload);
  
  fwrite($sock, implode($handshake_payload));
  
  fwrite($sock, pack("C*", 0x01, 0x00)); //request packet
  
  $responselen = read_int($sock);
  
  if(ord(fread($sock, 1)) != 0)
  {
    exit("malformed response");
  }
  
  $jsonlen = read_int($sock);
  $jsondata_utf8 = fread($sock, $jsonlen);
  $jsondata = utf8_decode($jsondata_utf8);
  
  fwrite($sock, pack("C*", 0x09, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00)); //We don't want to keep the server waiting for our ping
  fclose($sock);
  
  $parsed_json = json_decode($jsondata);
  
  echo $parsed_json->description->text . "|" . $parsed_json->players->max . "|" . $parsed_json->players->online . "|" . $parsed_json->version->name . (property_exists($parsed_json, "favicon") ? $parsed_json->favicon : "");
 ?>
