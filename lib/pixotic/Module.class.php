<?php

/**
 * Core interface for Pixotic modules.
 */
interface pixotic_Module {

	/**
	 * Activate the plugin with the given Pixotic instance.
	 * @param Pixotic $pixotic
	 */
	public function activate(&$pixotic);

	/**
	 * Deactivate the plugin.
	 */
	public function deactivate();

}
