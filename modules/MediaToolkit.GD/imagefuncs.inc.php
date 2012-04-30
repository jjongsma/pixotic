<?php

	// A bunch of functions that should be in GD but aren't.

	if (!defined('imagecreatefrombmp')) {

		function imagecreatefrombmp($filename) {
			if (! $f1 = fopen($filename,"rb"))
				return FALSE;

			$FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1,14));
			if ($FILE['file_type'] != 19778)
				return FALSE;

			$BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel'.
						'/Vcompression/Vsize_bitmap/Vhoriz_resolution'.
						'/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1,40));
			$BMP['colors'] = pow(2,$BMP['bits_per_pixel']);
			if ($BMP['size_bitmap'] == 0)
				 $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
			$BMP['bytes_per_pixel'] = $BMP['bits_per_pixel']/8;
			$BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
			$BMP['decal'] = ($BMP['width']*$BMP['bytes_per_pixel']/4);
			$BMP['decal'] -= floor($BMP['width']*$BMP['bytes_per_pixel']/4);
			$BMP['decal'] = 4-(4*$BMP['decal']);
			if ($BMP['decal'] == 4) $BMP['decal'] = 0;

			$PALETTE = array();
			if ($BMP['colors'] < 16777216)
				$PALETTE = unpack('V'.$BMP['colors'], fread($f1,$BMP['colors']*4));

			// Create image
			$IMG = fread($f1,$BMP['size_bitmap']);
			$VIDE = chr(0);

			$res = imagecreatetruecolor($BMP['width'],$BMP['height']);
			$P = 0;
			$Y = $BMP['height']-1;
			while ($Y >= 0) {
				$X=0;
				while ($X < $BMP['width']) {
					if ($BMP['bits_per_pixel'] == 24)
						@$COLOR = unpack("V",substr($IMG,$P,3).$VIDE);
					elseif ($BMP['bits_per_pixel'] == 16) { 
						$COLOR = unpack("n",substr($IMG,$P,2));
						$COLOR[1] = $PALETTE[$COLOR[1]+1];
					} elseif ($BMP['bits_per_pixel'] == 8) { 
						$COLOR = unpack("n",$VIDE.substr($IMG,$P,1));
						$COLOR[1] = $PALETTE[$COLOR[1]+1];
					} elseif ($BMP['bits_per_pixel'] == 4) {
						$COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
						if (($P*2)%2 == 0) $COLOR[1] = ($COLOR[1] >> 4) ; else $COLOR[1] = ($COLOR[1] & 0x0F);
						$COLOR[1] = $PALETTE[$COLOR[1]+1];
					} elseif ($BMP['bits_per_pixel'] == 1) {
						$COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
						if    (($P*8)%8 == 0) $COLOR[1] =  $COLOR[1]        >>7;
						elseif (($P*8)%8 == 1) $COLOR[1] = ($COLOR[1] & 0x40)>>6;
						elseif (($P*8)%8 == 2) $COLOR[1] = ($COLOR[1] & 0x20)>>5;
						elseif (($P*8)%8 == 3) $COLOR[1] = ($COLOR[1] & 0x10)>>4;
						elseif (($P*8)%8 == 4) $COLOR[1] = ($COLOR[1] & 0x8)>>3;
						elseif (($P*8)%8 == 5) $COLOR[1] = ($COLOR[1] & 0x4)>>2;
						elseif (($P*8)%8 == 6) $COLOR[1] = ($COLOR[1] & 0x2)>>1;
						elseif (($P*8)%8 == 7) $COLOR[1] = ($COLOR[1] & 0x1);
						$COLOR[1] = $PALETTE[$COLOR[1]+1];
					} else
						return FALSE;
					imagesetpixel($res,$X,$Y,$COLOR[1]);
					$X++;
					$P += $BMP['bytes_per_pixel'];
				}
				$Y--;
				$P+=$BMP['decal'];
			}

			fclose($f1);

			return $res;
		}
	
	}

	if (!defined('imageflip')) {

		function imageflip(&$image) {

			$width  = imagesx($image);
			$height = imagesy($image);

			$flipped = null;
			// Truecolor provides better results, if possible.
			if (function_exists('imageistruecolor') && imageistruecolor($image)) {
				$flipped = imagecreatetruecolor($width, $height);
			} else {
				$flipped = imagecreate($width, $height);
			}

			for ($i = 0; $i < $width; $i++)
				imagecopy($flipped, $image, $width - $i, 0, $i, 0, 1, $height);

			return $flipped;

		}

	}

	if (!defined('imagecreatefromfile')) {

		function imagecreatefromfile($file, $type = null) {

			if ($type == null) {
				$imageData = getimagesize($file);
				if ($imageData)
					$type = $imageData[2];
			}

			if ($type) {

				switch($type) {

					case IMAGETYPE_GIF:
						return imagecreatefromgif($file);
					case IMAGETYPE_JPEG:
						return imagecreatefromjpeg($file);
					case IMAGETYPE_PNG:
						return imagecreatefrompng($file);
					case IMAGETYPE_BMP:
						return imagecreatefrombmp($file);
					case IMAGETYPE_WBMP:
						return imagecreatefromwbmp($file);
					case IMAGETYPE_XBM:
						return imagecreatefromxbm($file);
					case IMAGETYPE_SWF:
					case IMAGETYPE_PSD:
					case IMAGETYPE_TIFF_II:
					case IMAGETYPE_TIFF_MM:
					case IMAGETYPE_JPC:
					case IMAGETYPE_JP2:
					case IMAGETYPE_JPX:
					case IMAGETYPE_JB2:
					case IMAGETYPE_SWC:
					case IMAGETYPE_IFF:
					default:
						return false;
				}

			}

			return false;

		}

	}

	if (!defined('imagewrite')) {

		function imagewrite($image, $type, $file = null) {

			switch($type) {

				case IMAGETYPE_GIF:
					return imagegif($image, $file);
				case IMAGETYPE_JPEG:
					return imagejpeg($image, $file);
				case IMAGETYPE_PNG:
					return imagepng($image, $file);
				case IMAGETYPE_WBMP:
					return imagewbmp($image, $file);
				case IMAGETYPE_XBM:
				case IMAGETYPE_BMP:
				case IMAGETYPE_SWF:
				case IMAGETYPE_PSD:
				case IMAGETYPE_TIFF_II:
				case IMAGETYPE_TIFF_MM:
				case IMAGETYPE_JPC:
				case IMAGETYPE_JP2:
				case IMAGETYPE_JPX:
				case IMAGETYPE_JB2:
				case IMAGETYPE_SWC:
				case IMAGETYPE_IFF:
				default:
					return false;
			}

			return false;

		}

	}
