<?php
namespace ElementorPro\Modules\HoverBox;

use ElementorPro\Base\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Module extends Module_Base {

	public function get_widgets() {
		return [
			'Hover_Box',
		];
	}

	public function get_name() {
		return 'hover-box';
	}
}
