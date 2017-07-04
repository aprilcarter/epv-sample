<?php

/**
 * Register all actions and filters for the plugin
 *
 * @package    Extend_Product_Vendors
 * @subpackage Extend_Product_Vendors/includes
 */

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Extend_Product_Vendors
 * @subpackage Extend_Product_Vendors/includes
 * @author     April Carter <aprilddev@gmail.com>
 */
class Extend_Product_Vendors_Loader {

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
	 */
	protected $actions;

	/**
	 * The array of shortcodes to be registered with Wordpress
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $shortcodes
	 */
	protected $shortcodes;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
	 */
	protected $filters;

	/**
	 * The array of actions that need to be removed from registration with Wordpress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $rm_actions    The filters registered with WordPress to fire when the plugin loads.
	 */
	protected $rm_actions;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->actions = array();
		$this->filters = array();
		$this->shortcodes = array();
		$this->rm_actions = array();

	}

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             The name of the WordPress action that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the action is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. he priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             The name of the WordPress action that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the action is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. he priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 */
	public function remove_action( $hook, $callback, $priority = 10 ) {
		$this->rm_actions = $this->remove( $this->rm_actions, $hook, $callback, $priority );
	}


	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. he priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a shortcode to the list of shortcodes that need to be registered with WordPress
	 *
	 * @param string $shortcode_name The string that will go between the [] when the shortcode is used.
	 * @param object $component The class instance that the callback belongs to
	 * @param string $callback The name of the callback function that defines the content that will replace the shortcode on the website.
	 * @return return type
	 */
	public function add_shortcode($shortcode_name, $component, $callback)
	{
		$this->shortcodes = $this->add($this->shortcodes, $shortcode_name, $component, $callback, "", "", true);
	}

	/**
	 * A utility function that is used to register the actions and hooks into a single
	 * collection.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array                $hooks            The collection of hooks that is being registered (that is, actions or filters).
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         The priority at which the function should be fired.
	 * @param    int                  $accepted_args    The number of arguments that should be passed to the $callback.
	 * @param    bool                 $is_shortcode     Whether or not we are adding a shortcode with this function.
	 * @return   array                                  The collection of actions and filters registered with WordPress.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args, $is_shortcode = false) {
		if ($is_shortcode) {
			$hooks[] = array(
				'name' => $hook,
				'component' => $component,
				'callback' => $callback
			);
		} else {
			$hooks[] = array(
				'hook'          => $hook,
				'component'     => $component,
				'callback'      => $callback,
				'priority'      => $priority,
				'accepted_args' => $accepted_args
			);
		}

		return $hooks;

	}

	/**
	 * undocumented function summary
	 *
	 * Undocumented function long description
	 *
	 * @param array $hooks
	 * @param string $hook
	 * @param string $callback
	 * @param int $priority
	 * @return array
	 */
	private function remove($hooks, $hook, $callback, $priority = 10) {
		$hooks[] = array(
			'hook' => $hook,
			'callback' => $callback,
			'priority' => $priority
		);

		return $hooks;

	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {

		foreach ($this->shortcodes as $shortcode) {
			add_shortcode($shortcode['name'], array($shortcode['component'], $shortcode['callback']));
		}

		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ($this->rm_actions as $hook) {
			remove_action( $hook['hook'], $hook['callback'], $hook['priority'] );
		}

	}

}
