<?php
namespace PPerf_Analysis\Services;

class Colorize {


	public static function color_from_string( $str ) {
		$hash = md5( $str );
		// Convert hash to rgb
		$r  = substr( $hash, 0, 1 );
		$r  = ord( $r );
		$up = substr( $r, 0, 1 ) % 2 === 0;
		if ( $up ) {
			$r = $r < 100 ? $r + 100 : $r;
		} else {
			$r = $r > 50 ? $r - 50 : $r;
		}

		$g = substr( $hash, 6, 1 );
		$g = ord( $g );
		if ( $up ) {
			$g = $g > 90 ? $g - 60 : $g;
		} else {
			$g = $g < 100 ? $g + 120 : $g;
		}

		$b = substr( $hash, - 1, 1 );
		$b = ord( $b );
		if ( $up ) {
			$b = $b < 100 ? $b + 100 : $b;
		} else {
			$b = $b > 30 ? $b - 30 : $b;
		}

		return [ round( $r ), round( $g ), round( $b ) ];
	}

	public static function hue2rgb($p, $q, $t) {
		if($t < 0) $t += 1;
		if($t > 1) $t -= 1;
		if($t < 1/6) return $p + ($q - $p) * 6 * $t;
		if($t < 1/2) return $q;
		if($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
		return $p;
	}
	public static function hslToRgb($h, $s, $l) {
		$r = 0; $g= 0; $b = 0;
		if ($s === 0) {
			$r = $g = $b = $l;
		} else {
			$q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
			$p = 2 * $l - $q;
			$r = self::hue2rgb($p, $q, $h + 1/3);
			$g = self::hue2rgb($p, $q, $h);
			$b = self::hue2rgb($p, $q, $h - 1/3);
		}

		return [round($r * 255), round($g * 255), round($b * 255)];
	}
	public static function color_from_string_two($input) {
		$hash = $input;
		$chara = substr($hash,10,1);
		$char_int = ord($chara);
		if($char_int % 2 ===0) {
			$charb = substr($hash,2,1);
			$char_int += ord($charb);
		}
		$up = isset($charb);
		$result = 0;
		$sat =  ord(substr($hash,3,1)) / 2;
		$lum =  ord(substr($hash,4,1)) / 2;
		foreach(str_split($hash) as $i) {
			$result += ord($i)/16;
			if($up) {
				$sat += ord($i) / 16;
				$lum += ord($i) / 16;
			}
			if($chara === 'a') {
				break;
			}
			if($result > $char_int) {
				break;
			}
			if($sat > 80) {
				break;
			}
			if($lum > 70) {
				break;
			}
		}
		if($lum < 50) {
			$lum += 30;
		}
		/*		$rgb = self::hslToRgb($result, $sat, $lum);
				$bright = sqrt( 0.299 * pow($rgb[0], 2) + 0.587 * pow($rgb[1], 2) + 0.114 * pow($rgb[2], 2) );
				if ($bright >= 200) {
					$sat = 60;
				}*/

		return [$result,$sat,$lum];
	}
}