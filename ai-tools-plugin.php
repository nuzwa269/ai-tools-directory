<?php
/*
Plugin Name: Professional AI Directory Pro
Description: Complete AI Tools Directory with Search, Multi-Filters, Dot-Grid Background, and Submit Button.
Version: 3.0
Author: Nuzhat
*/

// 1. Post Type & Taxonomy Setup
function aitdir_pro_setup() {
    register_post_type('ai_tools', array(
        'labels' => array('name' => 'AI Tools', 'singular_name' => 'Tool'),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'thumbnail'),
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
        /* Header & Submit Button */
        .ait-pro-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid #eee;
            margin-bottom: 40px;
        }
        .ait-pro-logo { font-size: 24px; font-weight: 900; color: #000; text-decoration: none; }
        .ait-pro-submit-btn {
            background: #7c3aed; color: white !important; padding: 10px 22px;
            border-radius: 50px; text-decoration: none; font-weight: 600;
            font-size: 14px; transition: 0.3s;
        }
        .ait-pro-submit-btn:hover { background: #6d28d9; transform: scale(1.05); }

        /* Hero & Search */
        .ait-pro-hero { text-align: center; margin-bottom: 50px; }
        .ait-pro-hero h1 { font-size: 52px; font-weight: 900; margin-bottom: 15px; letter-spacing: -2px; }
        .ait-pro-search-container { max-width: 500px; margin: 0 auto 30px; }
        .ait-pro-search-input {
            width: 100%; padding: 15px 25px; border-radius: 12px; border: 1px solid #e2e8f0;
            font-size: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); outline: none;
        }

        /* Filter System */
        .ait-pro-filters { display: flex; flex-direction: column; align-items: center; gap: 12px; margin-bottom: 50px; }
        .ait-pro-pill-row { display: flex; justify-content: center; gap: 8px; flex-wrap: wrap; }
        .ait-pro-pill {
            padding: 7px 18px; border: 1px solid #e2e8f0; border-radius: 8px;
            background: white; cursor: pointer; font-size: 13px; font-weight: 500; color: #555;
        }
        .ait-pro-pill.active { background: #000; color: white; border-color: #000; }
        .ait-pro-pill-price.active { background: #7c3aed; border-color: #7c3aed; }

        /* Grid & Cards */
        .ait-pro-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
        .ait-pro-card {
            background: white; border: 1px solid #e2e8f0; border-radius: 16px;
            padding: 24px; transition: 0.3s; text-align: left; position: relative;
        }
        .ait-pro-card:hover { transform: translateY(-5px); box-shadow: 0 20px 30px rgba(0,0,0,0.05); }
        .ait-pro-icon { width: 50px; height: 50px; border-radius: 12px; margin-bottom: 15px; border: 1px solid #eee; }
        .ait-pro-card h3 { font-size: 20px; font-weight: 800; margin: 0 0 10px; }
        .ait-pro-card p { font-size: 14px; color: #666; height: 45px; overflow: hidden; margin-bottom: 20px; }
        .ait-pro-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 15px; border-top: 1px solid #f8fafc; }
        .ait-pro-price { font-size: 10px; font-weight: 700; color: #7c3aed; background: #f5f3ff; padding: 4px 10px; border-radius: 6px; }
        .ait-pro-link { font-weight: 700; color: #000; text-decoration: none; font-size: 13px; }
    </style>

    <div class="ait-pro-wrapper">
        <div class="ait-pro-header">
            <a href="#" class="ait-pro-logo">COACHPROAI</a>
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
                $thumb = get_the_post_thumbnail_url() ?: 'https://via.placeholder.com/50';
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
// 4. Submit Tool Frontend Form Logic
function ait_pro_submit_form_shortcode() {
    // اگر فارم سبمٹ ہوا ہو تو ڈیٹا پروسیس کریں
    if (isset($_POST['ait_submit_nonce']) && wp_verify_nonce($_POST['ait_submit_nonce'], 'ait_submit_action')) {
        
        $title    = sanitize_text_field($_POST['tool_name']);
        $url      = esc_url_raw($_POST['tool_url']);
        $price    = sanitize_text_field($_POST['tool_price']);
        $cat_id   = intval($_POST['tool_cat']);
        $desc     = sanitize_textarea_field($_POST['tool_desc']);

        // نیا پوسٹ (Tool) بنانا
        $new_tool = array(
            'post_title'   => $title,
            'post_content' => $desc,
            'post_status'  => 'pending', // ایڈمن کی منظوری تک پبلش نہیں ہوگا
            'post_type'    => 'ai_tools',
        );

        $post_id = wp_insert_post($new_tool);

        if ($post_id) {
            update_post_meta($post_id, '_aitdir_url', $url);
            update_post_meta($post_id, '_aitdir_price', $price);
            wp_set_object_terms($post_id, $cat_id, 'tool_cat');

            echo '<div style="background:#d4edda; color:#155724; padding:15px; border-radius:10px; margin-bottom:20px;">شکریہ! آپ کا ٹول موصول ہو گیا ہے اور ریویو کے بعد پبلش کر دیا جائے گا۔</div>';
        }
    }

    // فارم کا HTML اور CSS
    $categories = get_terms(array('taxonomy' => 'tool_cat', 'hide_empty' => false));
    
    ob_start(); ?>
    <style>
        .ait-form-card { background: white; border: 1px solid #e2e8f0; padding: 40px; border-radius: 20px; max-width: 600px; margin: 0 auto; box-shadow: 0 10px 25px rgba(0,0,0,0.05); font-family: "Inter", sans-serif; }
        .ait-form-card h2 { margin-bottom: 25px; font-weight: 800; font-size: 28px; text-align: center; }
        .ait-form-group { margin-bottom: 20px; }
        .ait-form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #333; }
        .ait-form-group input, .ait-form-group select, .ait-form-group textarea { 
            width: 100%; padding: 12px 15px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; transition: 0.3s;
        }
        .ait-form-group input:focus { border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1); }
        .ait-submit-btn { 
            width: 100%; background: #7c3aed; color: white; padding: 15px; border: none; border-radius: 10px; 
            font-weight: 700; cursor: pointer; font-size: 16px; transition: 0.3s;
        }
        .ait-submit-btn:hover { background: #6d28d9; }
    </style>

    <div class="ait-form-card">
        <h2>Submit New AI Tool</h2>
        <form method="POST">
            <?php wp_nonce_field('ait_submit_action', 'ait_submit_nonce'); ?>
            
            <div class="ait-form-group">
                <label>ٹول کا نام (Tool Name)</label>
                <input type="text" name="tool_name" required placeholder="e.g. ChatGPT">
            </div>

            <div class="ait-form-group">
                <label>ویب سائٹ لنک (Tool URL)</label>
                <input type="url" name="tool_url" required placeholder="https://example.com">
            </div>

            <div class="ait-form-group">
                <label>کیٹیگری (Category)</label>
                <select name="tool_cat" required>
                    <?php foreach($categories as $cat) echo '<option value="'.$cat->term_id.'">'.$cat->name.'</option>'; ?>
                </select>
            </div>

            <div class="ait-form-group">
                <label>قیمت (Pricing Type)</label>
                <select name="tool_price">
                    <option value="Free">Free</option>
                    <option value="Freemium">Freemium</option>
                    <option value="Paid">Paid</option>
                </select>
            </div>

            <div class="ait-form-group">
                <label>مختصر تفصیل (Description)</label>
                <textarea name="tool_desc" rows="4" required placeholder="ٹول کے بارے میں کچھ لکھیں..."></textarea>
            </div>

            <button type="submit" class="ait-submit-btn">Submit Tool for Review</button>
        </form>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('submit_ai_tool', 'ait_pro_submit_form_shortcode');
