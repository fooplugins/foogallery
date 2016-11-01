<?php

class WP_Thumb_Image_Editor_GD extends WP_Image_Editor_GD {

	public function get_image() {
		return $this->image;
	}

	public function update_image( $image ) {
		$this->image = $image;
	}

	public function update_size( $width = null, $height = null ) {
		return parent::update_size( $width, $height );
	}

	public static function supports_mime_type( $mime_type ) {
		$image_types = imagetypes();
		switch( $mime_type ) {
			case 'image/jpeg':
			case 'image/jpg':
				return ($image_types & IMG_JPG) != 0;
			case 'image/png':
				return ($image_types & IMG_PNG) != 0;
			case 'image/gif':
				return ($image_types & IMG_GIF) != 0;
		}

		return false;
	}
}

class WP_Thumb_Image_Editor_Imagick extends WP_Image_Editor_Imagick {

	public function get_image() {
		return $this->image;
	}

	public function update_image( $image ) {
		$this->image = $image;
	}

	public function update_size( $width = null, $height = null ) {
		return parent::update_size( $width, $height );
	}
}
