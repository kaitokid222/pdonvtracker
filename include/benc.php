<?php

/*
// +--------------------------------------------------------------------------+
// | Project:    pdonvtracker - NetVision BitTorrent Tracker 2017             |
// +--------------------------------------------------------------------------+
// | This file is part of pdonvtracker. NVTracker is based on BTSource,       |
// | originally by RedBeard of TorrentBits, extensively modified by           |
// | Gartenzwerg.                                                             |
// +--------------------------------------------------------------------------+
// | Obige Zeilen dürfen nicht entfernt werden!    Do not remove above lines! |
// +--------------------------------------------------------------------------+
 */

class BEncode
{

	function makeSorted($array){
		$i = 0;

		if (empty($array))
			return $array;

		foreach($array as $key => $value)
			$keys[$i++] = stripslashes($key);
		sort($keys);
		for ($i=0 ; isset($keys[$i]); $i++)
			$return[addslashes($keys[$i])] = $array[addslashes($keys[$i])];
		return $return;
	}

	function encodeEntry($entry, &$fd, $unstrip = false){
		if (is_bool($entry)){
			$fd .= "de";
			return;
		}
		if (is_int($entry) || is_float($entry)){
			$fd .= "i".$entry."e";
			return;
		}
		if ($unstrip)
			$myentry = stripslashes($entry);
		else
			$myentry = $entry;
		$length = strlen($myentry);
		$fd .= $length.":".$myentry;
		return;
	}

	function encodeList($array, &$fd){
		$fd .= "l";

		if (empty($array)){
			$fd .= "e";
			return;
		}
		for ($i = 0; isset($array[$i]); $i++)
			$this->decideEncode($array[$i], $fd);
		$fd .= "e";
	}

	function decideEncode($unknown, &$fd){
		if (is_array($unknown)){
			if (isset($unknown[0]) || empty($unknown))
				return $this->encodeList($unknown, $fd);
			else
				return $this->encodeDict($unknown, $fd);
		}
		$this->encodeEntry($unknown, $fd);
	}

	function encodeDict($array, &$fd){
		$fd .= "d";
		if (is_bool($array)){
			$fd .= "e";
			return;
		}

		$newarray = $this->makeSorted($array);
		foreach($newarray as $left => $right){
			$this->encodeEntry($left, $fd, true);
			$this->decideEncode($right, $fd);
		}
		$fd .= "e";
		return;
	}

} // eof BEncode

function benc($array){
	$string = "";
	$encoder = new BEncode;
	$encoder->decideEncode($array, $string);
	return $string;
}

function benc_str($s){
	return strlen($s) . ":$s";
}

function benc_int($i){
	return "i" . $i . "e";
}


class BDecode
{

	function numberdecode($wholefile, $start){
		$ret[0] = 0;
		$offset = $start;

		$negative = false;
		if ($wholefile[$offset] == '-'){
			$negative = true;
			$offset++;
		}
		
		if ($wholefile[$offset] == '0'){
			$offset++;
			if ($negative)
				return array(false);
			if ($wholefile[$offset] == ':' || $wholefile[$offset] == 'e'){
				$offset++;
				$ret[0] = 0;
				$ret[1] = $offset;
				return $ret;
			}
			return array(false);
		}
		
		while (true){
			if ($wholefile[$offset] >= '0' && $wholefile[$offset] <= '9'){
				$ret[0] *= 10;
				$ret[0] += ord($wholefile[$offset]) - ord("0");
				$offset++;
			}else if ($wholefile[$offset] == 'e' || $wholefile[$offset] == ':'){
				$ret[1] = $offset+1;
				if ($negative){
					if ($ret[0] == 0)
						return array(false);
					$ret[0] = - $ret[0];
				}
				return $ret;
			}else
				return array(false);
		}

	}

	function decodeEntry($wholefile, $offset=0)
	{
		if ($wholefile[$offset] == 'd')
			return $this->decodeDict($wholefile, $offset);
		if ($wholefile[$offset] == 'l')
			return $this->decodelist($wholefile, $offset);
		if ($wholefile[$offset] == "i"){
			$offset++;
			return $this->numberdecode($wholefile, $offset);
		}
		$info = $this->numberdecode($wholefile, $offset);
		if ($info[0] === false)
			return array(false);
		$ret[0] = substr($wholefile, $info[1], $info[0]);
		$ret[1] = $info[1]+strlen($ret[0]);
		return $ret;
	}

	function decodeList($wholefile, $start){
		$offset = $start+1;
		$i = 0;
		if ($wholefile[$start] != 'l')
			return array(false);
		$ret = array();
		while (true){
			if ($wholefile[$offset] == 'e')
				break;
			$value = $this->decodeEntry($wholefile, $offset);
			if ($value[0] === false)
				return array(false);
			$ret[$i] = $value[0];
			$offset = $value[1];
			$i ++;
		}

		$final[0] = $ret;
		$final[1] = $offset+1;
		return $final;
	}

	function decodeDict($wholefile, $start=0){
		$offset = $start;
		if ($wholefile[$offset] == 'l')
			return $this->decodeList($wholefile, $start);
		if ($wholefile[$offset] != 'd')
			return false;
		$ret = array();
		$offset++;
		while (true){	
			if ($wholefile[$offset] == 'e'){
				$offset++;
				break;
			}
			$left = $this->decodeEntry($wholefile, $offset);
			if (!$left[0])
				return false;
			$offset = $left[1];
			if ($wholefile[$offset] == 'd'){
				$value = $this->decodedict($wholefile, $offset);
				if (!$value[0])
					return false;
				$ret[addslashes($left[0])] = $value[0];
				$offset= $value[1];
				continue;
			}else if ($wholefile[$offset] == 'l'){
				$value = $this->decodeList($wholefile, $offset);
				if (!$value[0] && is_bool($value[0]))
					return false;
				$ret[addslashes($left[0])] = $value[0];
				$offset = $value[1];
			}else{
				$value = $this->decodeEntry($wholefile, $offset);
				if ($value[0] === false)
					return false;
				$ret[addslashes($left[0])] = $value[0];
				$offset = $value[1];
			}
		}
		if (empty($ret))
			$final[0] = true;
		else
			$final[0] = $ret;
		$final[1] = $offset;
		return $final;
	}

}

function bdec($wholefile){
	$decoder = new BDecode;
	$return = $decoder->decodeEntry($wholefile);
	return $return[0];
}

function bdec_file($f){
	$nf = file_get_contents($f);
	$decoded = bdec($nf);
	return $decoded;
}

function err($msg) {
   return benc_resp_raw("d".benc_str("failure reason").benc_str($msg)."e");
}

function benc_resp($d){
    benc_resp_raw(benc(array("type" => "dictionary", "value" => $d)));
}

function benc_resp_raw($x){
    header("Content-Type: text/plain");
    header("Pragma: no-cache");
    print($x);
}
?>