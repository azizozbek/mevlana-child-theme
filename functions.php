<?php
/**
 * Mevlana Child Theme — functions.php
 *
 * @package Mevlana Child
 */

// ─────────────────────────────────────────────────────────────────────────────
// 1. Enqueue parent + child stylesheets
// ─────────────────────────────────────────────────────────────────────────────

add_action('wp_enqueue_scripts', function () {
    // Parent theme stylesheet
    wp_enqueue_style(
        'twentytwentyfour-style',
        get_template_directory_uri() . '/style.css',
        [],
        wp_get_theme(get_template())->get('Version')
    );

    // Child theme stylesheet (for any custom CSS overrides)
    wp_enqueue_style(
        'mevlana-child-style',
        get_stylesheet_uri(),
        ['twentytwentyfour-style'],
        wp_get_theme()->get('Version')
    );
});


// ─────────────────────────────────────────────────────────────────────────────
// 2. Theme setup
// ─────────────────────────────────────────────────────────────────────────────

add_action('after_setup_theme', function () {
    add_theme_support('post-formats', [
        'aside', 'audio', 'chat', 'gallery',
        'image', 'link', 'quote', 'status', 'video',
    ]);
    add_theme_support('menus');
});


// ─────────────────────────────────────────────────────────────────────────────
// 3. Pattern helper — loads a PHP pattern file and returns its output
// ─────────────────────────────────────────────────────────────────────────────

function mytheme_get_pattern_content(string $pattern_name): string {
    $file = get_stylesheet_directory() . '/patterns/' . $pattern_name . '.php';

    if (!file_exists($file)) {
        return '<!-- pattern file not found: ' . esc_html($pattern_name) . ' -->';
    }

    ob_start();
    include $file;
    return ob_get_clean();
}


// ─────────────────────────────────────────────────────────────────────────────
// 4. Register custom block patterns
// ─────────────────────────────────────────────────────────────────────────────

add_action('init', function () {
    register_block_pattern('mevlana-child/language-nav', [
        'title'      => __('Language Navigation', 'mevlana-child'),
        'categories' => ['navigation'],
        'content'    => mytheme_get_pattern_content('language-nav'),
    ]);
});


// ─────────────────────────────────────────────────────────────────────────────
// 5. Register custom pattern category (optional, for the editor sidebar)
// ─────────────────────────────────────────────────────────────────────────────

add_action('init', function () {
    register_block_pattern_category('mevlana', [
        'label'       => _x('Mevlana', 'Block pattern category', 'mevlana-child'),
        'description' => __('Multilang navi.', 'mevlana-child'),
    ]);
});
