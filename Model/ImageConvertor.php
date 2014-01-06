<?php
namespace Gratheon\CMS\Model;

class ImageConvertor {

	protected $original_image_resource;
	protected $original_image_info;
	protected $resized_image_resource;
    protected $crop_position = array(0,0,0,0);


    public function getOriginalSize(){
        return array(
            $this->original_image_info[0],
            $this->original_image_info[1]
        );
    }


    public function setCropPosition($x,$y, $x2, $y2){
        $this->crop_position = array($x,$y, $x2, $y2);
    }

	/**
	 * Cut Square From Rectangular Image
	 *
	 * @param int $intPosition
	 * @param int $intSquareSize
	 *
	 */
	public function cutSquare($intSquareSize = 120, $intPosition = 2) {
		list($orig_width, $orig_height, $fileExt) = $this->original_image_info;

		if ($orig_width > $orig_height) {
			$intSmallPart = $orig_height;
		}
		else {
			$intSmallPart = $orig_width;
		}

		$image_p = imagecreatetruecolor($intSquareSize, $intSquareSize);

		$this->loadTransparency($this->original_image_resource, $image_p, $this->original_image_info[2]);

		if ($orig_width > $orig_height) {
			imagecopyresampled($image_p, $this->original_image_resource, 0, 0, $orig_width / 2 - $intSmallPart / 2, 0, $intSquareSize, $intSquareSize, $intSmallPart, $intSmallPart);
		}
		else {
			imagecopyresampled($image_p, $this->original_image_resource, 0, 0, 0, $orig_height / 2 - $intSmallPart / 2, $intSquareSize, $intSquareSize, $intSmallPart, $intSmallPart);
		}

		$this->resized_image_resource = $image_p;
	}


	/**
	 * Put a watermark over an image, overwrite it
	 *
	 * @param string $strFile :original path
	 * @param string $strWatermark :watermark path
	 * @param integer $intTransparency : Transparency percent
	 * @param int $intPositionX
	 * @param int $intPositionY
	 *
	 * @return int
	 */
	public function addWatermark($strFile, $strWatermark, $intTransparency = 20, $intPositionX = 0, $intPositionY = 0) {
		list($orig_width, $orig_height, $orig_ext) = getimagesize($strFile);
		list($water_width, $water_height, $water_ext) = getimagesize($strWatermark);
		if (!$intPositionX) {
			$intPositionX = round($orig_width / 2 - $water_width / 2);
		}
		if (!$intPositionY) {
			$intPositionY = round($orig_height / 2 - $water_height / 2);
		}
		//$watermark = imagecreatefrompng($strWatermark);

		switch ($orig_ext) {
			case 1  :
				$image = imagecreatefromgif($strFile);
				break;
			case 2  :
				$image = imagecreatefromjpeg($strFile);
				break;
			case 3  :
				$image = imagecreatefrompng($strFile);
				break;
			default:
				return 0;
		}
		switch ($water_ext) {
			case 1  :
				$water = imagecreatefromgif($strWatermark);
				break;
			case 2  :
				$water = imagecreatefromjpeg($strWatermark);
				break;
			case 3  :
				$water = imagecreatefrompng($strWatermark);
				break;
			default:
				return 0;
		}
		imagecopymerge($image, $water, $intPositionX, $intPositionY, 0, 0, $water_width, $water_height, $intTransparency);
		imagejpeg($image, $strFile, 100);

		imagedestroy($image);
		imagedestroy($water);
	}


	public function loadImage($file) {
/*        if(is_file($file)){
		    chmod($file, 0755);
        }
*/
		$this->original_image_info = \getimagesize($file);

		switch ($this->original_image_info[2]) {
			case IMAGETYPE_GIF:
				$this->original_image_resource = \imagecreatefromgif($file);
				break;

			case IMAGETYPE_JPEG:
				$this->original_image_resource = \imagecreatefromjpeg($file);
				break;

			case IMAGETYPE_PNG:
				$this->original_image_resource = \imagecreatefrompng($file);
				break;

			default:
                return false;
                //print_r($this->original_image_info);
				//throw new Exception('unsupported format:'. $this->original_image_info[2]);
		}

        return true;
	}


	public function resizeRectangle($width = 0, $height = 0, $upscale = false, $keep_proportions=true) {

		if ($height <= 0 && $width <= 0) {
			throw new \Exception('invalid image dimensions');
		}

		$image      = $this->original_image_resource;
		$width_old  = $this->original_image_info[0];
		$height_old = $this->original_image_info[1];
		$type_old   = $this->original_image_info[2];

        if($keep_proportions){
            if ($width == 0) {
                $factor = $height / $height_old;
				$final_width  = round($width_old * $factor);
				$final_height = $height;
            }
            elseif ($height == 0) {
                $factor = $width / $width_old;
				$final_height  = round($height_old * $factor);
				$final_width = $width;
            }
            else{
                $factor = min($width / $width_old, $height / $height_old);
				$final_width  = round($width_old * $factor);
				$final_height = round($height_old * $factor);
            }

            if (($height!=0 && $width!=0) && !$upscale && ($height < $final_height || $width < $final_width)) {
                $final_width  = $width_old;
                $final_height = $height_old;
            }
        }
        else{
            $final_width = $width;
            $final_height = $height;
        }

		$image_resized = \imagecreatetruecolor($final_width, $final_height);

		$this->loadTransparency($image, $image_resized, $type_old);

		\imagecopyresampled(
            $image_resized, $image,
            0, 0,
            $this->crop_position[0], $this->crop_position[1],
            $final_width, $final_height,
            $this->crop_position[2] ? ($this->crop_position[2]-$this->crop_position[0]) : $width_old,
            $this->crop_position[3] ? ($this->crop_position[3]-$this->crop_position[1]) : $height_old
        );

		$this->resized_image_resource = $image_resized;

		return true;
	}


	public function loadTransparency($image, &$image_resized, $type_old) {
		if (($type_old == IMAGETYPE_GIF) || ($type_old == IMAGETYPE_PNG)) {
			$trnprt_indx = \imagecolortransparent($image);

			// If we have a specific transparent color
			if ($trnprt_indx >= 0) {

				// Get the original image's transparent color's RGB values
				$trnprt_color = \imagecolorsforindex($image, $trnprt_indx);

				// Allocate the same color in the new image resource
				$trnprt_indx = \imagecolorallocate($image_resized, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);

				// Completely fill the background of the new image with allocated color.
				\imagefill($image_resized, 0, 0, $trnprt_indx);

				// Set the background color for new image to transparent
				\imagecolortransparent($image_resized, $trnprt_indx);


			}
			// Always make a transparent background color for PNGs that don't have one allocated already
			elseif ($type_old == IMAGETYPE_PNG) {

				// Turn off transparency blending (temporarily)
				\imagealphablending($image_resized, false);

				// Create a new transparent color for image
				$color = \imagecolorallocate($image_resized, 255, 255, 255); //imagecolorallocatealpha($image_resized, 0, 0, 0, 127);

				// Completely fill the background of the new image with allocated color.
				\imagefill($image_resized, 0, 0, $color);

				// Restore transparency blending
				\imagesavealpha($image_resized, true);
			}
		}
	}


    public function deleteOriginal($file, $use_linux_commands = false) {
		if ($use_linux_commands) {
			\exec('rm ' . $file);
		}
		else
		{
			@unlink($file);
		}

	}


    public function outputImage($output) {
		switch ($output) {
			case 'browser':
				$mime = \image_type_to_mime_type($this->original_image_info[2]);
				\header("Content-type: $mime");
				$output = NULL;
				break;

			case 'return':
				return $this->resized_image_resource;
				break;
		}

        if(\file_exists($output)){
            \unlink($output);
        }

		switch ($this->original_image_info[2]) {
			case IMAGETYPE_GIF:
				\imagegif($this->resized_image_resource, $output);
				break;

			case IMAGETYPE_JPEG:
				\imagejpeg($this->resized_image_resource, $output, 100);
				break;

			case IMAGETYPE_PNG:
				\imagepng($this->resized_image_resource, $output);
				break;
		}
		return true;
	}


	/**
	 * Image Comparing Function (C)2011 Robert Lerner, All Rights Reserved
	 * @param $image1
	 * @param $image2
	 * @param int $RTolerance Red Integer Color Deviation before channel flag thrown
	 * @param int $GTolerance Green Integer Color Deviation before channel flag thrown
	 * @param int $BTolerance Blue Integer Color Deviation before channel flag thrown
	 * @param int $WarningTolerance Percentage of channel differences before warning returned
	 * @param int $ErrorTolerance Percentage of channel difference before error returned
	 *
	 * @return array
	 */
	public function imageCompare($image1, $image2, $RTolerance=0, $GTolerance=0, $BTolerance=0, $WarningTolerance=1, $ErrorTolerance=5)
	     {
	     if (is_resource($image1))
	          $im = $image1;
		 elseif(strlen($image1)>200){
			 $im = \imagecreatefromstring($image1);
		 }
	     else
	          if (!$im = \imagecreatefrompng($image1))
	               trigger_error("Image 1 could not be opened",E_USER_ERROR);

	     if (is_resource($image2))
	          $im2 = $image2;
	     elseif(strlen($image2)>200){
             $im2 = \imagecreatefromstring($image2);
         }
	     else
	          if (!$im2 = \imagecreatefrompng($image2))
	               trigger_error("Image 2 could not be opened",E_USER_ERROR);



	     $OutOfSpec = 0;

	     if (imagesx($im)!=imagesx($im2))
	          die("Width does not match.");
	     if (imagesy($im)!=imagesy($im2))
	          die("Height does not match.");


	     //By columns
	     for ($width=0;$width<=imagesx($im)-1;$width++)
	          {
	          for ($height=0;$height<=imagesy($im)-1;$height++)
	               {
	               $rgb = \imagecolorat($im, $width, $height);
	               $r1 = ($rgb >> 16) & 0xFF;
	               $g1 = ($rgb >> 8) & 0xFF;
	               $b1 = $rgb & 0xFF;

	               $rgb = \imagecolorat($im2, $width, $height);
	               $r2 = ($rgb >> 16) & 0xFF;
	               $g2 = ($rgb >> 8) & 0xFF;
	               $b2 = $rgb & 0xFF;

	               if (!($r1>=$r2-$RTolerance && $r1<=$r2+$RTolerance))
	                    $OutOfSpec++;

	               if (!($g1>=$g2-$GTolerance && $g1<=$g2+$GTolerance))
	                    $OutOfSpec++;

	               if (!($b1>=$b2-$BTolerance && $b1<=$b2+$BTolerance))
	                    $OutOfSpec++;


	               }
	          }
	     $TotalPixelsWithColors = (imagesx($im)*imagesy($im))*3;

	     $RET['PixelsByColors'] = $TotalPixelsWithColors;
	     $RET['PixelsOutOfSpec'] = $OutOfSpec;

	     if ($OutOfSpec!=0 && $TotalPixelsWithColors!=0)
	          {
	          $PercentOut = ($OutOfSpec/$TotalPixelsWithColors)*100;
	          $RET['PercentDifference'] = $PercentOut;
				  if($PercentOut >= $WarningTolerance) //difference triggers WARNINGTOLERANCE%
	               $RET['WarningLevel']=TRUE;
	          if ($PercentOut>=$ErrorTolerance) //difference triggers ERRORTOLERANCE%
	               $RET['ErrorLevel']=TRUE;
	          }

	     RETURN $RET;
     }
}
