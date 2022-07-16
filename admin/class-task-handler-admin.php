<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       www.de
 * @since      1.0.0
 *
 * @package    Task_Handler
 * @subpackage Task_Handler/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Task_Handler
 * @subpackage Task_Handler/admin
 * @author     HP Diesing <www.de>
 */
class Task_Handler_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**
     * Twig instance
     *
     * @var \Twig\Environment
     */
    private $twig;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        $loader = new \Twig\Loader\FilesystemLoader(WP_PLUGIN_DIR . '/task-handler/admin/views');
        $this->twig = new \Twig\Environment($loader);

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/task-handler-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/task-handler-admin.js', array( 'jquery' ), $this->version, false );
	}

    public function admin_menu() {
//        acf_add_options_page([
//            'page_title' 	=> 'Theme General Settings',
//            'menu_title'	=> 'Theme Settings',
//            'menu_slug' 	=> 'theme-general-settings',
//            'capability'	=> 'edit_posts',
//            'redirect'		=> false
//            ]
//        );

        add_submenu_page(
            'options-general.php',
            'Email List',
            'Email List',
            'manage_options',
            'email-list',
            [$this, 'menu_page_callback'],
            1
        );


//        add_options_page( 'Task manager', 'Task manager', 'manage_options', 'task-manager', [$this, 'menu_page_callback'] );
    }

    private function translate_list ($target_dir, &$emailList) {
        $file_content = file_get_contents($target_dir);

        $contacts = explode('BEGIN:VCARD', $file_content);

        $data = [];

        foreach ($contacts as $contact) {
            if(!empty($contact)) {

                $name = explode('FN:', $contact);
                if(array_key_exists(1, $name)) {
                    $name = $name[1];
                    $name = explode(PHP_EOL, $name)[0];
                    if(count(explode(' ', $name)) > 1) {
                        $name = explode(' ', $name)[0] . ' ' . explode(' ', $name)[1];

                    }
                    $name = trim($name);
                } else {
                    $name = 'undefined';
                }

                $email = explode('item1.EMAIL;type=INTERNET;type=pref:', $contact);
                if(array_key_exists(1, $email)) {
                    $email = $email[1];
                    $email = explode(PHP_EOL, $email)[0];
                    $email = trim($email);
                } else {
                    $email = 'undefined';
                }

                $phone = explode('TEL;type=pref:', $contact);
                if(array_key_exists(1, $phone)) {
                    $phone = $phone[1];
                    $phone = explode(PHP_EOL, $phone)[0];
                    $phone = trim($phone);
                } else {
                    $phone = 'undefined';
                }

                $uid = explode('UID:', $contact);
                if(array_key_exists(1, $uid)) {
                    $uid = $uid[1];
                    $uid = explode(PHP_EOL, $uid)[0];
                    $uid = trim($uid);
                } else {
                    $uid = hash('sha1', $email);
                }

                $data[$uid] = [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone
                ];

                $emailList->addData([
                    $uid => [
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone
                    ]
                ]);
            }
        }
    }

    public function menu_page_callback() {
        $template_vars = [];
        $emailList = new EmailList(WP_PLUGIN_DIR . '/task-handler/data/EmailList.json');

        if(filter_input(INPUT_POST, 'import-submit')) {
            if($_FILES['importFile']) {
                $tmp_name = $_FILES['importFile']['tmp_name'];
                $filename =  basename($_FILES["importFile"]["name"]);
                $extention = strtolower(pathinfo($filename,PATHINFO_EXTENSION));

                if($extention == 'vcf') {
                    $target_dir = WP_PLUGIN_DIR . '/task-handler/uploads/' . $filename;
                    move_uploaded_file($_FILES["importFile"]["tmp_name"], $target_dir);
                    $this->translate_list($target_dir, $emailList);
                } else {

                }
            }
        }

        $template_vars['emailList'] = $emailList->getEmailList();

//        echo '<pre>';
//        die(var_dump($template_vars));
//        echo '</pre>';
        echo $this->twig->render('EmailList.html', $template_vars);
    }
}
