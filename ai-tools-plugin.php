<?php
/*
Plugin Name: Professional AI Directory Pro
Description: Complete AI Tools Directory with Search, Multi-Filters, Dot-Grid Background, and Image Uploads.
Version: 4.0
Author: Nuzhat
*/

// 1. Post Type & Taxonomy Setup
function aitdir_pro_setup() {
    register_post_type('ai_tools', array(
        'labels' => array('name' => 'AI Tools', 'singular_name' => 'Tool'),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'thumbnail'), // 'thumbnail' یہاں امیج کے لیے ضروری ہے
        'menu_icon' => 'dashicons-admin-tools',
    ));
    register_taxonomy('tool_cat', 'ai_tools', array(
        'label' => 'Categories',
        'hierarchical' => true,
        'show_admin_column' => true,
    ));
}
add_action('init', 'aitdir_pro_setup');

// 2. Meta Fields for URL & Price
function aitdir_pro_meta() {
    add_meta_box('aitdir_meta', 'Tool Details', 'aitdir_pro_meta_html', 'ai_tools');
}
add_action('add_meta_boxes', 'aitdir_pro_meta');

function aitdir_pro_meta_html($post) {
    $url = get_post_meta($post->ID, '_aitdir_url', true);
    $price = get_post_meta($post->ID, '_aitdir_price', true);
    ?>
    <p>Tool URL:<br><input type="url" name="aitdir_url" value="<?php echo esc_attr($url); ?>" style="width:100%"></p>
    <p>Pricing:<br>
    <select name="aitdir_price" style="width:100%">
        <option value="Free" <?php selected($price, 'Free'); ?>>Free</option>
        <option value="Paid" <?php selected($price, 'Paid'); ?>>Paid</option>
        <option value="Freemium" <?php selected($price, 'Freemium'); ?>>Freemium</option>
    </select></p>
    <?php
}
add_action('save_post', function($post_id){
    if (isset($_POST['aitdir_url'])) update_post_meta($post_id, '_aitdir_url', $_POST['aitdir_url']);
    if (isset($_POST['aitdir_price'])) update_post_meta($post_id, '_aitdir_price', $_POST['aitdir_price']);
});

// 3. Main Directory Shortcode
function aitdir_pro_shortcode() {
    $query = new WP_Query(array('post_type' => 'ai_tools', 'posts_per_page' => -1));
    $categories = get_terms(array('taxonomy' => 'tool_cat', 'hide_empty' => true));
    ob_start(); ?>

    <style>
        .ait-pro-wrapper {
            background-color: #ffffff;
            background-image: radial-gradient(#e5e7eb 1px, transparent 1px);
            background-size: 24px 24px;
            padding: 20px;
            font-family: 'Inter', sans-serif;
            border-radius: 20px;
        }
        .ait-pro-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 0; border-bottom: 1px solid #eee; margin-bottom: 40px; }
        .ait-pro-logo { font-size: 24px; font-weight: 900; color: #000; text-decoration: none; }
        .ait-pro-submit-btn { background: #7c3aed; color: white !important; padding: 10px 22px; border-radius: 50px; text-decoration: none; font-weight: 600; font-size: 14px; transition: 0.3s; }
        .ait-pro-submit-btn:hover { background: #6d28d9; transform: scale(1.05); }
        .ait-pro-hero { text-align: center; margin-bottom: 50px; }
        .ait-pro-hero h1 { font-size: 52px; font-weight: 900; margin-bottom: 15px; letter-spacing: -2px; }
        .ait-pro-search-container { max-width: 500px; margin: 0 auto 30px; }
        .ait-pro-search-input { width: 100%; padding: 15px 25px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); outline: none; }
        .ait-pro-filters { display: flex; flex-direction: column; align-items: center; gap: 12px; margin-bottom: 50px; }
        .ait-pro-pill-row { display: flex; justify-content: center; gap: 8px; flex-wrap: wrap; }
        .ait-pro-pill { padding: 7px 18px; border: 1px solid #e2e8f0; border-radius: 8px; background: white; cursor: pointer; font-size: 13px; font-weight: 500; color: #555; }
        .ait-pro-pill.active { background: #000; color: white; border-color: #000; }
        .ait-pro-pill-price.active { background: #7c3aed; border-color: #7c3aed; }
        .ait-pro-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
        .ait-pro-card { background: white; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; transition: 0.3s; text-align: left; position: relative; }
        .ait-pro-card:hover { transform: translateY(-5px); box-shadow: 0 20px 30px rgba(0,0,0,0.05); }
        .ait-pro-icon { width: 50px; height: 50px; border-radius: 12px; margin-bottom: 15px; border: 1px solid #eee; object-fit: cover; }
        .ait-pro-card h3 { font-size: 20px; font-weight: 800; margin: 0 0 10px; }
        .ait-pro-card p { font-size: 14px; color: #666; height: 45px; overflow: hidden; margin-bottom: 20px; }
        .ait-pro-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 15px; border-top: 1px solid #f8fafc; }
        .ait-pro-price { font-size: 10px; font-weight: 700; color: #7c3aed; background: #f5f3ff; padding: 4px 10px; border-radius: 6px; }
        .ait-pro-link { font-weight: 700; color: #000; text-decoration: none; font-size: 13px; }
    </style>

    <div class="ait-pro-wrapper">
        <div class="ait-pro-header">
            <a href="<?php echo home_url(); ?>" class="ait-pro-logo">COACHPROAI</a>
            <a href="<?php echo home_url('/submit-a-tool/'); ?>" class="ait-pro-submit-btn">+ Submit Tool</a>
        </div>
        <div class="ait-pro-hero">
            <h1>CUSTOM GPTs</h1>
            <p>ایکسیس کریں بہترین ٹولز اور ریسورسز کو ایک ہی جگہ پر</p>
            <div class="ait-pro-search-container">
                <input type="text" id="aitProSearch" class="ait-pro-search-input" placeholder="تلاش کریں...">
            </div>
            <div class="ait-pro-filters">
                <div class="ait-pro-pill-row">
                    <div class="ait-pro-pill active ait-pro-cat-pill" data-cat="all">All Tools</div>
                    <?php foreach($categories as $cat) echo '<div class="ait-pro-pill ait-pro-cat-pill" data-cat="'.$cat->slug.'">'.$cat->name.'</div>'; ?>
                </div>
                <div class="ait-pro-pill-row">
                    <div class="ait-pro-pill active ait-pro-price-pill" data-price="all">All Pricing</div>
                    <div class="ait-pro-pill ait-pro-price-pill" data-price="Free">Free</div>
                    <div class="ait-pro-pill ait-pro-price-pill" data-price="Freemium">Freemium</div>
                    <div class="ait-pro-pill ait-pro-price-pill" data-price="Paid">Paid</div>
                </div>
            </div>
        </div>

        <div class="ait-pro-grid" id="aitProGrid">
            <?php while ($query->have_posts()) : $query->the_post(); 
                $url = get_post_meta(get_the_ID(), '_aitdir_url', true);
                $price = get_post_meta(get_the_ID(), '_aitdir_price', true);
                $thumb = get_the_post_thumbnail_url(get_the_ID(), 'medium') ?: 'https://via.placeholder.com/50';
                $terms = get_the_terms(get_the_ID(), 'tool_cat');
                $cats = $terms ? implode(' ', wp_list_pluck($terms, 'slug')) : '';
            ?>
                <div class="ait-pro-card" data-name="<?php echo strtolower(get_the_title()); ?>" data-price="<?php echo $price; ?>" data-cats="<?php echo $cats; ?>">
                    <img src="<?php echo $thumb; ?>" class="ait-pro-icon">
                    <h3><?php the_title(); ?></h3>
                    <p><?php echo wp_trim_words(get_the_content(), 12); ?></p>
                    <div class="ait-pro-footer">
                        <span class="ait-pro-price"><?php echo $price; ?></span>
                        <a href="<?php echo esc_url($url); ?>" target="_blank" class="ait-pro-link">Visit Tool ↗</a>
                    </div>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById("aitProSearch");
        const catPills = document.querySelectorAll(".ait-pro-cat-pill");
        const pricePills = document.querySelectorAll(".ait-pro-price-pill");
        const cards = document.querySelectorAll(".ait-pro-card");
        function filter() {
            const s = searchInput.value.toLowerCase();
            const c = document.querySelector(".ait-pro-cat-pill.active").dataset.cat;
            const p = document.querySelector(".ait-pro-price-pill.active").dataset.price;
            cards.forEach(card => {
                const name = card.dataset.name;
                const price = card.dataset.price;
                const cats = card.dataset.cats.split(" ");
                const match = name.includes(s) && (c === "all" || cats.includes(c)) && (p === "all" || price === p);
                card.style.display = match ? "block" : "none";
            });
        }
        searchInput.addEventListener("input", filter);
        [...catPills, ...pricePills].forEach(pill => {
            pill.addEventListener("click", function() {
                this.parentElement.querySelectorAll(".ait-pro-pill").forEach(p => p.classList.remove("active"));
                this.classList.add("active");
                filter();
            });
        });
    });
    </script>
    <?php return ob_get_clean();
}
add_shortcode('display_ai_directory', 'aitdir_pro_shortcode');

// 4. Submit Tool Frontend Form Logic (CORRECTED WITH IMAGE UPLOAD)
function ait_pro_submit_form_shortcode() {
    if (isset($_POST['ait_submit_nonce']) && wp_verify_nonce($_POST['ait_submit_nonce'], 'ait_submit_action')) {
        
        $title    = sanitize_text_field($_POST['tool_name']);
        $url      = esc_url_raw($_POST['tool_url']);
        $price    = sanitize_text_field($_POST['tool_price']);
        $cat_id   = intval($_POST['tool_cat']);
        $desc     = sanitize_textarea_field($_POST['tool_desc']);

        $post_id = wp_insert_post(array(
            'post_title'   => $title,
            'post_content' => $desc,
            'post_status'  => 'pending',
            'post_type'    => 'ai_tools',
        ));

        if ($post_id) {
            update_post_meta($post_id, '_aitdir_url', $url);
            update_post_meta($post_id, '_aitdir_price', $price);
            wp_set_object_terms($post_id, $cat_id, 'tool_cat');

            // امیج اپ لوڈ کرنے کا فنکشن
            if (!empty($_FILES['tool_image']['name'])) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/media.php');

                $attachment_id = media_handle_upload('tool_image', $post_id);
                if (!is_wp_error($attachment_id)) {
                    set_post_thumbnail($post_id, $attachment_id);
                }
            }

            echo '<div style="background:#d4edda; color:#155724; padding:15px; border-radius:10px; margin-bottom:20px;">شکریہ! ٹول موصول ہو گیا، ریویو کے بعد پبلش ہو جائے گا۔</div>';
        }
    }

    $categories = get_terms(array('taxonomy' => 'tool_cat', 'hide_empty' => false));
    ob_start(); ?>
    <style>
        .ait-form-card { background: white; border: 1px solid #e2e8f0; padding: 40px; border-radius: 20px; max-width: 600px; margin: 0 auto; box-shadow: 0 10px 25px rgba(0,0,0,0.05); font-family: "Inter", sans-serif; }
        .ait-form-card h2 { margin-bottom: 25px; font-weight: 800; font-size: 28px; text-align: center; }
        .ait-form-group { margin-bottom: 20px; }
        .ait-form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #333; }
        .ait-form-group input, .ait-form-group select, .ait-form-group textarea { width: 100%; padding: 12px 15px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; transition: 0.3s; }
        .ait-submit-btn { width: 100%; background: #7c3aed; color: white; padding: 15px; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; font-size: 16px; transition: 0.3s; }
    </style>

    <div class="ait-form-card">
        <h2>Submit New AI Tool</h2>
        <form method="POST" enctype="multipart/form-data"> <?php wp_nonce_field('ait_submit_action', 'ait_submit_nonce'); ?>
            
            <div class="ait-form-group">
                <label>Tool Name</label>
                <input type="text" name="tool_name" required>
            </div>
            <div class="ait-form-group">
                <label>Tool URL</label>
                <input type="url" name="tool_url" required>
            </div>
            <div class="ait-form-group">
                <label>Tool Icon/Logo (Image)</label>
                <input type="file" name="tool_image" accept="image/*" required>
            </div>
            <div class="ait-form-group">
                <label>Category</label>
                <select name="tool_cat" required>
                    <?php foreach($categories as $cat) echo '<option value="'.$cat->term_id.'">'.$cat->name.'</option>'; ?>
                </select>
            </div>
            <div class="ait-form-group">
                <label>Pricing</label>
                <select name="tool_price">
                    <option value="Free">Free</option><option value="Freemium">Freemium</option><option value="Paid">Paid</option>
                </select>
            </div>
            <div class="ait-form-group">
                <label>Description</label>
                <textarea name="tool_desc" rows="4" required></textarea>
            </div>
            <button type="submit" class="ait-submit-btn">Submit Tool for Review</button>
        </form>
    </div>
    <?php return ob_get_clean();
}
add_shortcode('submit_ai_tool', 'ait_pro_submit_form_shortcode');

// ============================================
// 5. Prompt Generator Card - NEW FEATURE
// ============================================

// 5.1 Meta Box for Prompt Card Settings
function aitdir_prompt_card_meta() {
    add_meta_box('aitdir_prompt_card', 'Prompt Generator Card Settings', 'aitdir_prompt_card_html', 'ai_tools', 'normal', 'high');
}
add_action('add_meta_boxes', 'aitdir_prompt_card_meta');

function aitdir_prompt_card_html($post) {
    $enabled = get_post_meta($post->ID, '_aitdir_card_enabled', true);
    $card_title = get_post_meta($post->ID, '_aitdir_card_title', true);
    $gpt_url = get_post_meta($post->ID, '_aitdir_gpt_url', true);
    $prompt_template = get_post_meta($post->ID, '_aitdir_prompt_template', true);
    $options_json = get_post_meta($post->ID, '_aitdir_card_options', true);
    wp_nonce_field('aitdir_prompt_card_nonce', 'aitdir_prompt_card_nonce_field');
    ?>
    <style>
        .aitdir-meta-section { margin-bottom: 20px; padding: 15px; background: #f9f9f9; border-radius: 8px; }
        .aitdir-meta-section label { font-weight: bold; display: block; margin-bottom: 8px; }
        .aitdir-meta-section input, .aitdir-meta-section textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .aitdir-meta-section textarea { min-height: 100px; }
        .aitdir-help-text { font-size: 12px; color: #666; margin-top: 5px; }
    </style>

    <div class="aitdir-meta-section">
        <label><input type="checkbox" name="aitdir_card_enabled" value="1" <?php checked($enabled, '1'); ?>> Enable Prompt Generator Card</label>
    </div>

    <div class="aitdir-meta-section">
        <label>Card Title</label>
        <input type="text" name="aitdir_card_title" value="<?php echo esc_attr($card_title); ?>" placeholder="e.g., EduPlanner Pro">
    </div>

    <div class="aitdir-meta-section">
        <label>GPT/Tool URL (Opens after copy)</label>
        <input type="url" name="aitdir_gpt_url" value="<?php echo esc_attr($gpt_url); ?>" placeholder="https://chatgpt.com/g/...">
    </div>

    <div class="aitdir-meta-section">
        <label>Prompt Template</label>
        <textarea name="aitdir_prompt_template" placeholder="Generate a {plan_type} for {topic} following {board} curriculum..."><?php echo esc_textarea($prompt_template); ?></textarea>
        <p class="aitdir-help-text">Use {variable_name} for dynamic values. Example: {topic}, {board}, {plan_type}</p>
    </div>

    <div class="aitdir-meta-section">
        <label>Dropdown Options (JSON Format)</label>
        <textarea name="aitdir_card_options" placeholder='[{"name":"board","label":"Select Board","options":["Punjab Board","Federal Board","Cambridge"]}]'><?php echo esc_textarea($options_json); ?></textarea>
        <p class="aitdir-help-text">JSON format: [{"name":"field_name", "label":"Display Label", "options":["Option1","Option2"]}]</p>
    </div>
    <?php
}

// 5.2 Save Prompt Card Meta
add_action('save_post', function($post_id) {
    if (!isset($_POST['aitdir_prompt_card_nonce_field']) || !wp_verify_nonce($_POST['aitdir_prompt_card_nonce_field'], 'aitdir_prompt_card_nonce')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    $fields = array('_aitdir_card_enabled', '_aitdir_card_title', '_aitdir_gpt_url', '_aitdir_prompt_template', '_aitdir_card_options');
    $post_keys = array('aitdir_card_enabled', 'aitdir_card_title', 'aitdir_gpt_url', 'aitdir_prompt_template', 'aitdir_card_options');

    foreach ($fields as $i => $field) {
        if (isset($_POST[$post_keys[$i]])) {
            update_post_meta($post_id, $field, sanitize_textarea_field($_POST[$post_keys[$i]]));
        } else {
            delete_post_meta($post_id, $field);
        }
    }
});

// 5.3 Prompt Generator Card Shortcode
function aitdir_prompt_card_shortcode($atts) {
    $atts = shortcode_atts(array('id' => get_the_ID()), $atts);
    $post_id = intval($atts['id']);

    $enabled = get_post_meta($post_id, '_aitdir_card_enabled', true);
    if ($enabled !== '1') return '';

    $card_title = get_post_meta($post_id, '_aitdir_card_title', true) ?: get_the_title($post_id);
    $gpt_url = get_post_meta($post_id, '_aitdir_gpt_url', true);
    $prompt_template = get_post_meta($post_id, '_aitdir_prompt_template', true);
    $options_json = get_post_meta($post_id, '_aitdir_card_options', true);
    $options = json_decode($options_json, true) ?: array();

    $unique_id = 'ait-card-' . $post_id;

    ob_start();
    ?>
    <style>
        .ait-prompt-card { max-width: 500px; margin: 30px auto; padding: 30px; background: #f9f9f9; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-top: 5px solid #8A2BE2; font-family: Arial, sans-serif; }
        .ait-prompt-card h2 { color: #8A2BE2; text-align: center; margin: 0 0 25px; font-size: 1.8em; text-transform: uppercase; }
        .ait-prompt-section { margin-bottom: 20px; padding: 15px; background: #fff; border: 1px solid #D1C4E9; border-radius: 10px; }
        .ait-prompt-section label { display: block; margin-bottom: 8px; font-weight: bold; color: #6A1B9A; font-size: 1.1em; }
        .ait-prompt-section select, .ait-prompt-section input, .ait-prompt-section textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 1em; box-sizing: border-box; }
        .ait-prompt-section select:focus, .ait-prompt-section input:focus, .ait-prompt-section textarea:focus { outline: none; border-color: #8A2BE2; box-shadow: 0 0 0 3px rgba(138,43,226,0.2); }
        .ait-prompt-display { width: 100%; padding: 20px; border: 1px solid #8A2BE2; border-radius: 8px; background: #F3E5F5; color: #6A1B9A; min-height: 80px; font-family: monospace; white-space: pre-wrap; word-wrap: break-word; }
        .ait-prompt-cursor { display: inline-block; background: #6A1B9A; width: 3px; height: 1em; margin-left: 2px; animation: aitBlink 1s infinite; }
        @keyframes aitBlink { 50% { background: transparent; } }
        .ait-copy-btn { width: 100%; padding: 18px; margin-top: 15px; border: 3px solid #8A2BE2; border-radius: 8px; background: transparent; color: #8A2BE2; font-size: 1.3em; font-weight: bold; cursor: pointer; transition: 0.3s; text-transform: uppercase; }
        .ait-copy-btn:hover { background: #8A2BE2; color: #fff; }
    </style>

    <div class="ait-prompt-card" id="<?php echo $unique_id; ?>">
        <h2><?php echo esc_html($card_title); ?></h2>

        <div class="ait-prompt-section">
            <label>1. Main Input (Topic/Details)</label>
            <textarea class="ait-main-input" placeholder="Enter your topic or details here..." rows="2"></textarea>
        </div>

        <?php
        $counter = 2;
        foreach ($options as $option):
            if (!isset($option['name'], $option['label'], $option['options'])) continue;
        ?>
        <div class="ait-prompt-section">
            <label><?php echo $counter . '. ' . esc_html($option['label']); ?></label>
            <select class="ait-select-field" data-name="<?php echo esc_attr($option['name']); ?>">
                <?php foreach ($option['options'] as $opt): ?>
                    <option value="<?php echo esc_attr($opt); ?>"><?php echo esc_html($opt); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php $counter++; endforeach; ?>

        <div class="ait-prompt-section">
            <label>Generated Prompt</label>
            <div class="ait-prompt-display"><span class="ait-prompt-text"></span><span class="ait-prompt-cursor"></span></div>
        </div>

        <button type="button" class="ait-copy-btn" data-gpt-url="<?php echo esc_url($gpt_url); ?>">Copy Prompt & Open Tool</button>
    </div>

    <script>
    (function() {
        const card = document.getElementById('<?php echo $unique_id; ?>');
        const template = <?php echo json_encode($prompt_template); ?>;
        const gptUrl = <?php echo json_encode($gpt_url); ?>;

        const mainInput = card.querySelector('.ait-main-input');
        const selects = card.querySelectorAll('.ait-select-field');
        const promptText = card.querySelector('.ait-prompt-text');
        const copyBtn = card.querySelector('.ait-copy-btn');

        let typeTimeout;

        function generatePrompt() {
            let prompt = template;
            const mainValue = mainInput.value.trim() || '[Enter your topic]';
            prompt = prompt.replace(/{topic}|{input}|{main}/gi, mainValue);

            selects.forEach(select => {
                const name = select.dataset.name;
                const value = select.value;
                const regex = new RegExp('{' + name + '}', 'gi');
                prompt = regex.test(prompt) ? prompt.replace(regex, value) : prompt;
            });

            return prompt;
        }

        function typeWriter(text, speed = 15) {
            clearTimeout(typeTimeout);
            promptText.innerHTML = '';
            let i = 0;
            function type() {
                if (i < text.length) {
                    promptText.innerHTML += text.charAt(i);
                    i++;
                    typeTimeout = setTimeout(type, speed);
                }
            }
            type();
        }

        function updatePrompt() {
            typeWriter(generatePrompt());
        }

        mainInput.addEventListener('input', updatePrompt);
        selects.forEach(s => s.addEventListener('change', updatePrompt));

        copyBtn.addEventListener('click', function() {
            const text = generatePrompt();
            navigator.clipboard.writeText(text).then(() => {
                copyBtn.textContent = 'Copied! Opening...';
                setTimeout(() => { copyBtn.textContent = 'Copy Prompt & Open Tool'; }, 2000);
                if (gptUrl) window.open(gptUrl, '_blank');
            }).catch(() => alert('Copy failed. Please copy manually.'));
        });

        updatePrompt();
    })();
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('prompt_card', 'aitdir_prompt_card_shortcode');

// 5.4 Auto-display card in single tool content
add_filter('the_content', function($content) {
    if (is_singular('ai_tools')) {
        $enabled = get_post_meta(get_the_ID(), '_aitdir_card_enabled', true);
        if ($enabled === '1') {
            $content .= do_shortcode('[prompt_card id="' . get_the_ID() . '"]');
        }
    }
    return $content;
});
