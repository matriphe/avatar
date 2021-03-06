<?php namespace Matriphe\Avatar;
/**
* Avatarco Class
*
* @package Avatarco
* @author Rodion Baskakov <rodion.baskakov@gmail.com>
* @version 0.1
*
* This class generates "random" PNG userpics like Gravatar. Details and settings for
* the picture are taken from users' data like email or any other string.
*
* @usage
*
* $av = new Avatarco;
* $av->init($string [, $size [, $sprites [, $alpha]]]);

*
* $av->savePicture($path);
*	or
* $av->showPicture();
*/
final class Avatarco
{
	/**
	* @static SPRITE_SIZE stores size of a single sprite to draw pattern on
	* @static MIN_SPRITES_PER_SIDE stores a minimum quantity of elements in square
	* @static MAX_SPRITES_PER_SIDE stores a maximum quantity of elements in square
	* @static MIN_PICTURE_SIZE the smallest size of output image
	*/
	const SPRITE_SIZE = 100;
	const MIN_SPRITES_PER_SIDE = 2;
	const MAX_SPRITES_PER_SIDE = 10;
	const MIN_PICTURE_SIZE = 10;

	private $_hash = '';
	private $_size = 100;
	private $_side = 2;
	private $_alpha = false;

	/**
	* @private $_shapesCenter stores arrays with coords of patterns to draw on squares in the middle area of userpic
	* @todo Generate more patterns. Up to 16.
	*/
	private $_shapesCenter	= Array(

		Array( 					// small square rotated
				0.2, 0.5,
				0.5, 0.2,
				0.8, 0.5,
				0.5, 0.8
		),
		Array(					//small star
				0.1, 0.5,
				0.4, 0.4,
				0.5, 0.1,
				0.6, 0.4,
				0.9, 0.5,
				0.6, 0.6,
				0.5, 0.9,
				0.4, 0.6
		),
		Array(					// small square
				0.33, 0.33,
				0.67, 0.33,
				0.67, 0.67,
				0.33, 0.67
		),
		Array(					// cross
				0.25, 0,
				0.75, 0,
				0.5, 0.5,
				1, 0.25,
				1, 0.75,
				0.5, 0.5,
				0.75, 1,
				0.25, 1,
				0.5, 0.5,
				0, 0.75,
				0, 0.25,
				0.5, 0.5
		),
		Array(					// empty square rotated
				0, 0,
				1, 0,
				1, 1,
				0, 1,
				0, 0.5,
				0.5, 1,
				1, 0.5,
				0.5, 0,
				0, 0.5
		),
		Array(				// filled square rotated
				0.5, 0,
				1, 0.5,
				0.5, 1,
				0, 0.5
		),
		Array(				// star rotated
				0, 0,
				0.5, 0.25,
				1, 0,
				0.75, 0.5,
				1, 1,
				0.5, 0.75,
				0, 1,
				0.25, 0.5
		),
		Array(					// bigger square
				0.2, 0.8,
				0.2, 0.2,
				0.8, 0.2,
				0.8, 0.8
		),
	);

	/**
	* @private $_shapesCenter stores arrays with coords of patterns to draw on squares in the corners of userpic
	* @todo Generate more patterns. Up to 16.
	*/
	private $_shapesCorner	= Array(
		Array(
				0, 0,
				0.5, 0,
				1, 0.5,
				0.5, 1,
				0, 1,
				0.5, 0.5
		),
		Array(
				0, 0.5,
				0.5, 0,
				1, 0.5,
				0.5, 1,
				0.5, 0.5
		),
		Array(
				0.5, 1,
				0, 0.5,
				0, 0,
				0.5, 0.5,
				1, 0,
				1, 0.5,
		),
		Array(
				0.5, 0,
				0.5, 0.5,
				1, 0.5,
				1, 1,
				0.5, 1,
				0.5, 0.5,
				0, 0.5
		),
		Array(
				0, 0.4,
				0.4, 0,
				1, 1,
				0.7, 0.4,
				0.4, 0.7,
				1, 1
		),
		Array(
				0.25, 0,
				0.75, 0,
				0.5, 0.5,
				1, 0.25,
				1, 0.75,
				0.5, 0.5,
				0.75, 1,
				0.25, 1,
				0.5, 0.5,
				0, 0.75,
				0, 0.25,
				0.5, 0.5
		)
	);

	/**
	* @private $_shapesCenter stores arrays with coords of patterns to draw on squares on the sides of userpic
	* @todo Generate more patterns. Up to 16.
	*/
	private $_shapesSide	= Array(
		Array(
			0.5, 0,
			0, 0.8,
			0, 1,
			0.2, 1,
			0.5, 0.1,
			0.8, 1,
			1, 1,
			1, 0.8
		),
		Array(
			0, 0.5,
			0.8, 1,
			1, 1,
			1, 0.8,
			0.1, 0.5,
			1, 0.2,
			1, 0,
			0.8, 0
		),
		Array(					// bigger square
				0.2, 0.8,
				0.2, 0.2,
				0.8, 0.2,
				0.8, 0.8
		),
		Array(					// small square
				0.33, 0.33,
				0.67, 0.33,
				0.67, 0.67,
				0.33, 0.67
		),
		Array( 					// small square rotated
				0.2, 0.5,
				0.5, 0.2,
				0.8, 0.5,
				0.5, 0.8
		),
		Array(					// empty square rotated
				0, 0.5,
				0, 0,
				0.5, 0,
				1, 0,
				1, 0.5,
				0.5, 0,
				0, 0.5,
				0.5, 1,
				1, 0.5,
				1, 1,
				0, 1
		)
	);

	private $_sideBgColor;
	private $_sideFgColor;

	private $_cornerFgColor;

	private $_centerBgColor;
	private $_centerFgColor;

	private $_avatarco;


	/**
	* Method used to put settings into class variables
	*
	* @name init
	* @access public
	*
	* @var string $string	- user's email or other person's personalized data
	* @var int $size	- size for the output picture in pixels
	* @var int sprites	- number of elements for each side of userpic
	* @var bool $alpha - if set to True, white color in created images is replaced with transparent
	*
	*/
	public function init($string, $size = 100, $sprites = 0, $alpha = false)
	{
		$this->_hash = md5($string);
		$this->_size = $size > 9 ? $size : self::MIN_PICTURE_SIZE;
		$this->_side = $sprites > 1 ? $sprites : hexdec(substr($this->_hash, 29, 1));
		$this->_side = $this->_side < self::MIN_SPRITES_PER_SIDE ? self::MIN_SPRITES_PER_SIDE : $this->_side;
		$this->_side = $this->_side > self::MAX_SPRITES_PER_SIDE ? self::MAX_SPRITES_PER_SIDE : $this->_side;
		$this->_alpha = $alpha;
	}

	private function CreatePicture()
	{
		$size = self::SPRITE_SIZE * $this->_side;

		$image = imageCreateTrueColor($size, $size);

		for($i = 1; $i <= 4; $i++) {

			$side = $this->CreateSide();
			$image = imageRotate($image, ($i - 1) * 90, 0);
			imagecopy($image, $side, 0, 0, 0, 0, imagesx($side), imagesy($side));
			imageDestroy($side);
		}

		if($this->_side > 2) {
			$center = $this->createCenter($this->_side - 2);
			imageCopy($image, $center, self::SPRITE_SIZE, self::SPRITE_SIZE, 0, 0, imagesx($center), imagesy($center));
			imageDestroy($center);

		}
		$this->_avatarco = imageCreateTrueColor($this->_size, $this->_size);
		imageCopyResampled($this->_avatarco, $image, 0, 0, 0, 0, $this->_size, $this->_size, $size, $size);

		if($this->_alpha) {
			$white = imageColorAllocate($this->_avatarco, 255, 255, 255);
			imagecolortransparent($this->_avatarco, $white);
		}

		return $this->_avatarco;
	}

	private function CreateCorner()
	{
		$this->_cornerFgColor = Array(
								hexdec(substr($this->_hash, 1, 2)),
								hexdec(substr($this->_hash, 3, 2)),
								hexdec(substr($this->_hash, 5, 2))
								);
		$corner = $this->CreateSquare(Array(255, 255, 255));
		$this->DrawPattern($corner, 'corner', $this->_cornerFgColor);
		return $corner;
	}

	private function CreateSide()
	{
		$this->_sideBgColor = Array(
								hexdec(substr($this->_hash, 6, 2)),
								hexdec(substr($this->_hash, 8, 2)),
								hexdec(substr($this->_hash, 10, 2))
								);

		$this->_sideFgColor = Array(
								hexdec(substr($this->_hash, 0, 2)),
								hexdec(substr($this->_hash, 2, 2)),
								hexdec(substr($this->_hash, 4, 2))
								);

		$side = imagecreatetruecolor(($this->_side - 1) * self::SPRITE_SIZE, self::SPRITE_SIZE);
		$corner = $this->CreateCorner();

		imageCopy($side, $corner, 0, 0, 0, 0, self::SPRITE_SIZE, self::SPRITE_SIZE);
		imageDestroy($corner);

		for($i = 1; $i <= $this->_side - 2; $i++)
		{
			$fgcolor = ($i % 2 == 0 && hexdec(substr($this->_hash, 27, 1)) % 2 == 0) ? $this->_sideBgColor : $this->_sideFgColor;
			$sprite = $this->CreateSquare(Array(255,255,255));
			$this->DrawPattern($sprite, 'side', $fgcolor);
			imageCopy($side, $sprite, $i * self::SPRITE_SIZE, 0, 0, 0, self::SPRITE_SIZE, self::SPRITE_SIZE);
			imagedestroy($sprite);
		}

		return $side;

	}

	private function CreateCenter($sqSide = 1)
	{

		$this->_centerBgColor = Array(
								hexdec(substr($this->_hash, 10, 2)),
								hexdec(substr($this->_hash, 12, 2)),
								hexdec(substr($this->_hash, 14, 2))
								);

		$this->_centerFgColor = Array(
								hexdec(substr($this->_hash, 16, 2)),
								hexdec(substr($this->_hash, 18, 2)),
								hexdec(substr($this->_hash, 20, 2))
								);


		if($sqSide == 1) {
			$sq = $this->CreateSquare(Array(255, 255, 255));
			$this->DrawPattern($sq, 'center', $this->_cornerFgColor);
			return $sq;
		}

		$sqSize = $sqSide * self::SPRITE_SIZE;
		$center = imagecreatetruecolor($sqSize, $sqSize);

		for($i = 1; $i <= 4; $i++) {
			$side = imagecreatetruecolor($sqSize - self::SPRITE_SIZE, self::SPRITE_SIZE);

			for($j = 1; $j <= $sqSide - 1; $j++) {
				$bgcolor = ($j == 1 && hexdec(substr($this->_hash, 25, 1)) % 2 == 0) ? $this->_centerFgColor : $this->_centerBgColor;
				$fgcolor = ($j == 1 && hexdec(substr($this->_hash, 25, 1)) % 2 == 0) ? $this->_centerBgColor : $this->_centerFgColor;
				$sprite = $this->CreateSquare(Array(255, 255, 255));
				$this->DrawPattern($sprite, 'center', $fgcolor);
				imageCopy($side, $sprite, ($j-1) * self::SPRITE_SIZE, 0, 0, 0, self::SPRITE_SIZE, self::SPRITE_SIZE);
				imagedestroy($sprite);
			}

			$center = imageRotate($center, ($i - 1) * 90, 0);
			imageCopy($center, $side, 0, 0, 0, 0, $sqSize - self::SPRITE_SIZE, self::SPRITE_SIZE);
			imageDestroy($side);

		}

		if($sqSide - 2 > 0) {
			$deeper = $this->CreateCenter($sqSide - 2);
			imageCopy($center, $deeper, self::SPRITE_SIZE, self::SPRITE_SIZE, 0, 0, imagesx($deeper), imagesy($deeper));
			imageDestroy($deeper);
		}

		return $center;

	}

	private function CreateSquare($colors = Array(255, 255, 255))
	{
		$square = imageCreateTrueColor(self::SPRITE_SIZE, self::SPRITE_SIZE);
		$background = imagecolorallocate($square, $colors[0], $colors[1], $colors[2]);
		imagefilledrectangle($square, 0, 0, self::SPRITE_SIZE, self::SPRITE_SIZE, $background);

		return $square;
	}

	private function DrawPattern($resourse = null, $type, $colors = Array(0, 0, 0))
	{
		$shape = $this->GetShape($type);
		$color = imagecolorallocate($resourse, $colors[0], $colors[1], $colors[2]);
		imagefilledpolygon($resourse, $shape, sizeof($shape) / 2, $color);
	}

	private function GetShape($type)
	{
		switch($type) {
			case 'side':
				$shape_id = hexdec(substr($this->_hash, 22, 1)) & (sizeof($this->_shapesSide) - 1);
				$shapes = $this->_shapesSide;
			break;
			case 'center':
				$shape_id = hexdec(substr($this->_hash, 23, 1)) & (sizeof($this->_shapesCenter) - 1);
				$shapes = $this->_shapesCenter;
			break;
			case 'corner':
				$shape_id = hexdec(substr($this->_hash, 24, 1)) & (sizeof($this->_shapesCorner) - 1);
				$shapes = $this->_shapesCorner;
			default:
			break;
		}
		$shape = $shapes[$shape_id];

		array_walk($shape, function(&$coord, $index, $mult) { $coord *= $mult; }, self::SPRITE_SIZE);
		return $shape;

	}

	public function ShowPicture()
	{
		header('Content-type: image/png');
		echo imagePng($this->CreatePicture());
		imageDestroy($this->_avatarco);
	}

	public function SavePicture($path)
	{
		if(!is_dir($path) || !is_writeable($path)) return false;

		$path = rtrim($path, '/') . '/'. $this->_hash .'.png';
		imagePng($this->CreatePicture(), $path, 0);
		imageDestroy($this->_avatarco);

		return $path;
	}
}

// $av = new Avatarco;
// $av->init(strtotime('now'), 100);
// $av->SavePicture('./');
// $av->ShowPicture();
?>