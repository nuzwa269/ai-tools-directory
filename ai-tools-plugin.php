<?php
/*
Plugin Name: AI Tools Directory Manager
Description: AI ٹولز کو مینیج کرنے اور شارٹ کوڈ کے ذریعے دکھانے کے لیے کسٹم پلگ ان
Version: 1.0
Author: Your Name
*/

// 1. کسٹم پوسٹ ٹائپ اور کیٹیگری رجسٹر کرنا
function aitools_register_post_type() {
    // ٹولز کے لیے پوسٹ ٹائپ
    register_post_type('ai_tools', array(
        'labels' => array('name' => 'AI Tools', 'singular_name' => 'Tool'),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'thumbnail'),
        'menu_icon' => 'dashicons-admin-tools',
    ));

    // ٹولز کے لیے کیٹیگری (Taxonomy)
    register_taxonomy('tool_cat', 'ai_tools', array(
        'label' => 'Categories',
        'hierarchical' => true,
        'show_admin_column' => true,
    ));
}
add_action('init', 'aitools_register_post_type');

// 2. کسٹم فیلڈز بنانا (URL اور Pricing کے لیے)
function aitools_add_custom_box() {
    add_meta_box('aitools_details', 'Tool Details', 'aitools_box_html', 'ai_tools');
}
add_action('add_meta_boxes', 'aitools_add_custom_box');

function aitools_box_html($post) {
    $url = get_post_meta($post->ID, '_tool_url', true);
    $price = get_post_meta($post->ID, '_tool_price', true);
    ?>
    <label>Tool URL:</label>
    <input type="url" name="tool_url" value="<?php echo esc_attr($url); ?>" style="width:100%"><br><br>
    <label>Pricing Type:</label>
    <select name="tool_price" style="width:100%">
        <option value="Free" <?php selected($price, 'Free'); ?>>Free</option>
        <option value="Paid" <?php selected($price, 'Paid'); ?>>Paid</option>
        <option value="Freemium" <?php selected($price, 'Freemium'); ?>>Freemium</option>
    </select>
    <?php
}

// ڈیٹا سیو کرنا
function aitools_save_postdata($post_id) {
    if (array_key_exists('tool_url', $_POST)) {
        update_post_meta($post_id, '_tool_url', $_POST['tool_url']);
    }
    if (array_key_exists('tool_price', $_POST)) {
        update_post_meta($post_id, '_tool_price', $_POST['tool_price']);
    }
}
add_action('save_post', 'aitools_save_postdata');

// 3. شارٹ کوڈ بنانا [display_tools]
function aitools_shortcode() {
    $query = new WP_Query(array('post_type' => 'ai_tools', 'posts_per_page' => -1));
    $output = '<div class="tools-container" style="display: grid; grid-template-columns: repeat(3, 1分fr); gap: 20px;">';

    while ($query->have_posts()) {
        $query->the_post();
        $url = get_post_meta(get_the_ID(), '_tool_url', true);
        $price = get_post_meta(get_the_ID(), '_tool_price', true);
        $thumb = get_the_post_thumbnail_url();

        $output .= '<div class="tool-card" style="border:1px solid #ddd; padding:15px; border-radius:10px; text-align:center;">';
        if($thumb) $output .= '<img src="'.$thumb.'" style="max-width:80px; margin-bottom:10px;">';
        $output .= '<h3>' . get_the_title() . '</h3>';
        $output .= '<p>' . wp_trim_words(get_the_content(), 15) . '</p>';
        $output .= '<span style="background:#eee; padding:3px 8px; border-radius:5px;">' . $price . '</span><br><br>';
        $output .= '<a href="'.$url.'" target="_blank" style="background:#0073aa; color:white; padding:8px 15px; text-decoration:none; border-radius:5px;">Visit Tool</a>';
        $output .= '</div>';
    }
    wp_reset_postdata();
    $output .= '</div>';
    return $output;
}
add_shortcode('display_tools', 'aitools_shortcode');
