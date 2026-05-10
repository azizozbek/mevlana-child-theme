<?php

$lang = 'de'; // default
if (function_exists('pll_current_language')) {
    $lang = pll_current_language('slug'); // e.g. 'de' or 'tr'
}

// 2. Resolve the menu by slug with language suffix ----------------------------
//    Menus are named: menu_de, menu_tr, menu_en, …
//    To add a new language just add its menu in Appearance → Menus named menu_{slug}.

$menu_slug = 'menu_' . $lang;
$menu      = wp_get_nav_menu_object($menu_slug);

// 3. Bail gracefully if menu doesn't exist ------------------------------------
if (!$menu) {
    echo '<!-- Language nav: menu "' . esc_html($menu_slug) . '" not found -->';
    return;
}

// 4. Fetch all menu items (flat list, WordPress handles order & depth) ---------
$all_items = wp_get_nav_menu_items($menu->term_id);

if (!$all_items) {
    echo '<!-- Language nav: menu "' . esc_html($menu_slug) . '" has no items -->';
    return;
}

usort($all_items, fn($a, $b) => $a->menu_order - $b->menu_order);


// 5. Build a parent→children map ----------------------------------------------
//    $tree[0]         = top-level items (menu_item_parent === '0')
//    $tree[$parent_id] = direct children of $parent_id

$tree = [];
foreach ($all_items as $item) {
    $tree[$item->menu_item_parent][] = $item;
}

// 6. Recursive renderer -------------------------------------------------------
/**
 * Renders a list of nav items as WP Navigation Link / Submenu blocks.
 *
 * @param WP_Post[] $items     Items at the current depth.
 * @param array     $tree      Full parent→children map.
 * @param int       $depth     Current recursion depth (0 = top level).
 * @return string              Block markup.
 */
function mytheme_render_nav_items(array $items, array $tree, int $depth = 0): string {
    $output = '';

    foreach ($items as $item) {
        $id       = (int) $item->ID;
        $label    = esc_attr($item->title);
        $url      = esc_url($item->url);
        $children = $tree[$id] ?? [];

        if (!empty($children)) {
            // ── Item WITH submenu ──────────────────────────────────────────
            // core/navigation-submenu wraps the parent link + child list.
            $output .= sprintf(
                '<!-- wp:navigation-submenu {"label":"%s","url":"%s","kind":"custom","isTopLevelLink":%s} -->',
                $label,
                $url,
                $depth === 0 ? 'true' : 'false'
            );

            // Recurse into children
            $output .= mytheme_render_nav_items($children, $tree, $depth + 1);

            $output .= '<!-- /wp:navigation-submenu -->';

        } else {
            // ── Leaf item (no children) ────────────────────────────────────
            $output .= sprintf(
                '<!-- wp:navigation-link {"label":"%s","url":"%s","kind":"custom","isTopLevelLink":%s} /-->',
                $label,
                $url,
                $depth === 0 ? 'true' : 'false'
            );
        }
    }

    return $output;
}

// 7. Render top-level items (parent ID = '0') ----------------------------------
$top_level_items = $tree['0'] ?? [];
$inner_blocks    = mytheme_render_nav_items($top_level_items, $tree, 0);

// 8. Output the Navigation block ----------------------------------------------
?>
<!-- wp:navigation {"overlayMenu":"mobile","layout":{"type":"flex","justifyContent":"right","flexWrap":"wrap"}} -->
<?php echo $inner_blocks; ?>
<!-- /wp:navigation -->