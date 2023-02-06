<?php

/*
    Plugin Name: Post Statistics
    Description: Post statistics
    Version: 1.0
    Author: David Heckel
    Author URI: https://davidhecke.dev
    Text Domain: pspdomain
    Domain Path: /languages
*/

class PostStatsPlugin {
  function __construct() {
    add_action('admin_menu', array($this, 'adminPage'));
    add_action('admin_init', array($this, 'settings'));
    add_filter('the_content', array($this, 'ifWrap'));
    add_action('init', array($this, 'languages'));
  }

  function languages() {
    load_plugin_textdomain('pspdomain', false, dirname(plugin_basename( __FILE__ )) . '/languages');
  }

  function ifWrap($content) {
    if (is_main_query() AND is_single() AND 
    (
        get_option('psp_wordcount', '1') OR 
        get_option( 'psp_charcount', '1') OR 
        get_option('psp_readtime', '1')
    )) {
        return $this->createHTML($content);
    }
    return $content;
  }
  
  function createHTML($content) {
    $html = '
    <h3>' . esc_html(get_option('psp_headline', 'Post Statistics')) . '</h3><p>';
    if (get_option('psp_wordcount', '1') OR get_option('psp_readtime', '1')) {
      $wordCount = str_word_count(strip_tags($content));
    }
    
      if (get_option('psp_wordcount', '1')) {
        $html .= esc_html__('This post has', 'pspdomain') . ' ' . $wordCount . ' ' . __('words', 'pspdomain') . '.<br>';
      }
    
      if (get_option('psp_charcount', '1')) {
        $html .= esc_html__('This post has', 'pspdomain') . ' ' . strlen(strip_tags($content)) . ' ' . __('characters', 'pspdomain') . '.<br>';
      }
    
    if (get_option('psp_readtime', '1')) {
      $html .= esc_html__('The read time is about', 'pspdomain') . ' ' . round($wordCount/255) . ' ' . __('minute(s)', 'pspdomain') . '.<br>';
    }

    $html .= '</p>';

    if (get_option( 'psp_location', '0') == '0') {
      return $html . $content;
    }

    return $content . $html;
  }

  function settings() {
    add_settings_section('psp_first_section', null, null, 'post-stats-settings');

    add_settings_field('psp_location', 'Display Location', array($this, 'locationHTML'), 'post-stats-settings', 'psp_first_section');
    register_setting('poststatsplugin', 'psp_location', array('sanitize_callback' => array($this, 'sanitizeLocation'), 'default' => '0'));
    
    add_settings_field('psp_headline', 'Headline Text', array($this, 'headlineHTML'), 'post-stats-settings', 'psp_first_section');
    register_setting('poststatsplugin', 'psp_headline', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'Post Statistics'));
    
    add_settings_field('psp_wordcount', 'Word Count', array($this, 'checkboxHTML'), 'post-stats-settings', 'psp_first_section', array('theName' => 'psp_wordcount'));
    register_setting('poststatsplugin', 'psp_wordcount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));
    
    add_settings_field('psp_charcount', 'Character Count', array($this, 'checkboxHTML'), 'post-stats-settings', 'psp_first_section', array('theName' => 'psp_charcount'));
    register_setting('poststatsplugin', 'psp_charcount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));
    
    add_settings_field('psp_readtime', 'Read Time', array($this, 'checkboxHTML'), 'post-stats-settings', 'psp_first_section', array('theName' => 'psp_readtime'));
    register_setting('poststatsplugin', 'psp_readtime', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));
  }
  
  function sanitizeLocation($input) {
    if($input != '0' AND $input != '1') {
        add_settings_error('psp_location', 'psp_location_error', 'Display location must be either Beginning or End!');
        return get_option('psp_location');
    }
    return $input;
  }

  function locationHTML() { ?>
    <select name="psp_location">
        <option value="0" <?php selected( get_option('psp_location'), '0') ?>>Beginning of post</option>
        <option value="1" <?php selected( get_option('psp_location'), '1') ?>>End of post</option>
    </select>
  <?php }
  
  function headlineHTML() { ?>
    <input type="text" name="psp_headline" value="<?php echo esc_attr(get_option('psp_headline')) ?>" >
  <?php }

  function checkboxHTML($args) { ?>
    <input type="checkbox" name="<?php echo $args['theName'] ?>" value="1" <?php checked(get_option($args['theName']), '1') ?>>
  <?php }

  function adminPage() {
    add_options_page('Post Statistics', __('Post Statistics', 'pspdomain'), 'manage_options', 'post-stats-settings', array($this, 'adminPageHTML'));
  }

  function adminPageHTML() { ?>
    <div class="wrap">
      <h1>Post Statistics Settings</h1>
      <form action="options.php" method="POST">
        <?php
          settings_fields('poststatsplugin');
          do_settings_sections('post-stats-settings');
          submit_button();
        ?>
      </form>
    </div>
  <?php }
}

$postStatsPlugin = new PostStatsPlugin();

