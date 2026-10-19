<?php

namespace Devnet\FSL\Includes;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Design presets for the linear Progress Bar (Pro) and the Product Label (a few Free).
 *
 * A preset is a starting point: applying it fills the existing design settings with its values (the whole look,
 * animations included; the merchant can change any of them afterwards), and the frontend adds a
 * `fsl-preset-{slug}` class for what the settings can't express (assets/src/presets/). A preset designed without
 * indicator icons turns them off, and still sets their look for a merchant who turns them back on.
 * The label's presets work the same way, with a `fsl-label-preset-{slug}` class (assets/src/label-presets/).
 *
 * @since	4.0.0
 */
class Presets {
    /**
     * Theme Match: a block theme's palette, else the brand color of Astra or Kadence, else the theme's text color.
     */
    const THEME_COLOR = 'var(--wp--preset--color--primary, var(--ast-global-color-0, var(--global-palette1, var(--wp--preset--color--contrast, currentColor))))';

    /**
     * Slug => label, whether its CSS uses the accent color, an optional hint for the picker, whether it shows a
     * progress marker (one goal), and the values it writes into the design settings; for the circular bar the values
     * it writes over them (`circular`), what its ring shows (`ring`), or that it's for the circular bar only.
     */
    public static function all() {
        static $presets = null;
        if ( null !== $presets ) {
            return $presets;
        }
        $theme_color = self::THEME_COLOR;
        $presets = [
            'minimal'   => [
                'label'  => __( 'Minimal', 'free-shipping-label' ),
                'accent' => false,
                'values' => [
                    'bar_inner_color'         => '#111827',
                    'bar_bg_color'            => '#E5E7EB',
                    'bar_border_color'        => 'rgba(0,0,0,0)',
                    'bar_height'              => 4,
                    'bar_radius'              => 4,
                    'box_bg_color'            => '',
                    'text_color'              => '',
                    'text_size'               => 'medium',
                    'center_text'             => 0,
                    'hide_border_shadow'      => 1,
                    'remove_bar_stripes'      => 1,
                    'disable_animation'       => 1,
                    'disabled_animations'     => 'all',
                    'indicator_icon'          => 0,
                    'indicator_icon_size'     => 14,
                    'indicator_icon_shape'    => 'round',
                    'indicator_icon_bg_color' => '#111827',
                    'icon_color'              => '#FFFFFF',
                ],
            ],
            'soft'      => [
                'label'  => __( 'Soft Pill', 'free-shipping-label' ),
                'accent' => false,
                'values' => [
                    'bar_inner_color'         => '#7C5CFC',
                    'bar_bg_color'            => '#EDE9FE',
                    'bar_border_color'        => 'rgba(0,0,0,0)',
                    'bar_height'              => 12,
                    'bar_radius'              => 999,
                    'box_bg_color'            => '#FFFFFF',
                    'text_color'              => '#1E1B4B',
                    'text_size'               => 'medium',
                    'center_text'             => 1,
                    'hide_border_shadow'      => 0,
                    'remove_bar_stripes'      => 1,
                    'disable_animation'       => 0,
                    'disabled_animations'     => 'all',
                    'indicator_icon'          => 0,
                    'indicator_icon_size'     => 22,
                    'indicator_icon_shape'    => 'round',
                    'indicator_icon_bg_color' => '#7C5CFC',
                    'icon_color'              => '#FFFFFF',
                ],
            ],
            'aurora'    => [
                'label'  => __( 'Aurora', 'free-shipping-label' ),
                'accent' => true,
                'values' => [
                    'bar_inner_color'         => '#6366F1',
                    'bar_accent_color'        => '#EC4899',
                    'bar_bg_color'            => '#EEF2FF',
                    'bar_border_color'        => 'rgba(0,0,0,0)',
                    'bar_height'              => 10,
                    'bar_radius'              => 999,
                    'box_bg_color'            => '#FFFFFF',
                    'text_color'              => '#111827',
                    'text_size'               => 'medium',
                    'center_text'             => 1,
                    'hide_border_shadow'      => 0,
                    'remove_bar_stripes'      => 0,
                    'disable_animation'       => 1,
                    'disabled_animations'     => 'shine',
                    'indicator_icon'          => 0,
                    'indicator_icon_size'     => 22,
                    'indicator_icon_shape'    => 'round',
                    'indicator_icon_bg_color' => '#6366F1',
                    'icon_color'              => '#FFFFFF',
                ],
            ],
            'glass'     => [
                'label'  => __( 'Glass', 'free-shipping-label' ),
                'accent' => false,
                'hint'   => __( 'Best over colors or images', 'free-shipping-label' ),
                'values' => [
                    'bar_inner_color'         => '#4F46E5',
                    'bar_bg_color'            => 'rgba(15,23,42,0.1)',
                    'bar_border_color'        => 'rgba(0,0,0,0)',
                    'bar_height'              => 10,
                    'bar_radius'              => 999,
                    'box_bg_color'            => 'rgba(255,255,255,0.55)',
                    'text_color'              => '#0F172A',
                    'text_size'               => 'medium',
                    'center_text'             => 1,
                    'hide_border_shadow'      => 0,
                    'remove_bar_stripes'      => 1,
                    'disable_animation'       => 1,
                    'disabled_animations'     => 'all',
                    'indicator_icon'          => 0,
                    'indicator_icon_size'     => 22,
                    'indicator_icon_shape'    => 'round',
                    'indicator_icon_bg_color' => 'rgba(255,255,255,0.9)',
                    'icon_color'              => '#4F46E5',
                ],
            ],
            'neon'      => [
                'label'  => __( 'Neon Night', 'free-shipping-label' ),
                'accent' => true,
                'values' => [
                    'bar_inner_color'         => '#22D3EE',
                    'bar_accent_color'        => '#A855F7',
                    'bar_bg_color'            => '#1E293B',
                    'bar_border_color'        => 'rgba(0,0,0,0)',
                    'bar_height'              => 8,
                    'bar_radius'              => 999,
                    'box_bg_color'            => '#0B1120',
                    'text_color'              => '#E2E8F0',
                    'text_size'               => 'medium',
                    'center_text'             => 1,
                    'hide_border_shadow'      => 0,
                    'remove_bar_stripes'      => 0,
                    'disable_animation'       => 1,
                    'disabled_animations'     => 'shine',
                    'indicator_icon'          => 0,
                    'indicator_icon_size'     => 22,
                    'indicator_icon_shape'    => 'round',
                    'indicator_icon_bg_color' => '#0B1120',
                    'icon_color'              => '#22D3EE',
                ],
            ],
            'steps'     => [
                'label'  => __( 'Steps', 'free-shipping-label' ),
                'accent' => false,
                'values' => [
                    'bar_inner_color'         => '#10B981',
                    'bar_bg_color'            => '#D1FAE5',
                    'bar_border_color'        => 'rgba(0,0,0,0)',
                    'bar_height'              => 14,
                    'bar_radius'              => 4,
                    'box_bg_color'            => '#FFFFFF',
                    'text_color'              => '#064E3B',
                    'text_size'               => 'medium',
                    'center_text'             => 1,
                    'hide_border_shadow'      => 0,
                    'remove_bar_stripes'      => 0,
                    'disable_animation'       => 1,
                    'disabled_animations'     => 'all',
                    'indicator_icon'          => 0,
                    'indicator_icon_size'     => 22,
                    'indicator_icon_shape'    => 'rounded',
                    'indicator_icon_bg_color' => '#10B981',
                    'icon_color'              => '#FFFFFF',
                ],
            ],
            'bold'      => [
                'label'  => __( 'Bold', 'free-shipping-label' ),
                'accent' => false,
                'values' => [
                    'bar_inner_color'         => '#FACC15',
                    'bar_bg_color'            => '#FFFFFF',
                    'bar_border_color'        => '#111111',
                    'bar_height'              => 20,
                    'bar_radius'              => 0,
                    'box_bg_color'            => '#FFFBEB',
                    'text_color'              => '#111111',
                    'text_size'               => 'medium',
                    'center_text'             => 0,
                    'hide_border_shadow'      => 0,
                    'remove_bar_stripes'      => 1,
                    'disable_animation'       => 1,
                    'disabled_animations'     => 'all',
                    'indicator_icon'          => 0,
                    'indicator_icon_size'     => 24,
                    'indicator_icon_shape'    => 'square',
                    'indicator_icon_bg_color' => '#FACC15',
                    'icon_color'              => '#111111',
                ],
            ],
            'soft-ui'   => [
                'label'  => __( 'Soft UI', 'free-shipping-label' ),
                'accent' => false,
                'hint'   => __( 'Set the box color to your page color', 'free-shipping-label' ),
                'values' => [
                    'bar_inner_color'         => '#6C8CFF',
                    'bar_bg_color'            => '#E6EBF2',
                    'bar_border_color'        => 'rgba(0,0,0,0)',
                    'bar_height'              => 12,
                    'bar_radius'              => 999,
                    'box_bg_color'            => '#E6EBF2',
                    'text_color'              => '#475569',
                    'text_size'               => 'medium',
                    'center_text'             => 1,
                    'hide_border_shadow'      => 0,
                    'remove_bar_stripes'      => 1,
                    'disable_animation'       => 1,
                    'disabled_animations'     => 'all',
                    'indicator_icon'          => 0,
                    'indicator_icon_size'     => 22,
                    'indicator_icon_shape'    => 'round',
                    'indicator_icon_bg_color' => '#E6EBF2',
                    'icon_color'              => '#6C8CFF',
                ],
            ],
            'shimmer'   => [
                'label'  => __( 'Shimmer', 'free-shipping-label' ),
                'accent' => false,
                'values' => [
                    'bar_inner_color'         => '#0EA5E9',
                    'bar_bg_color'            => '#E0F2FE',
                    'bar_border_color'        => 'rgba(0,0,0,0)',
                    'bar_height'              => 10,
                    'bar_radius'              => 999,
                    'box_bg_color'            => '#FFFFFF',
                    'text_color'              => '#0C4A6E',
                    'text_size'               => 'medium',
                    'center_text'             => 1,
                    'hide_border_shadow'      => 0,
                    'remove_bar_stripes'      => 0,
                    'disable_animation'       => 0,
                    'disabled_animations'     => 'all',
                    'indicator_icon'          => 0,
                    'indicator_icon_size'     => 22,
                    'indicator_icon_shape'    => 'round',
                    'indicator_icon_bg_color' => '#0EA5E9',
                    'icon_color'              => '#FFFFFF',
                ],
            ],
            'candy'     => [
                'label'  => __( 'Candy', 'free-shipping-label' ),
                'accent' => false,
                'values' => [
                    'bar_inner_color'         => '#EC4899',
                    'bar_bg_color'            => '#FCE7F3',
                    'bar_border_color'        => 'rgba(0,0,0,0)',
                    'bar_height'              => 14,
                    'bar_radius'              => 999,
                    'box_bg_color'            => '#FFFFFF',
                    'text_color'              => '#831843',
                    'text_size'               => 'medium',
                    'center_text'             => 1,
                    'hide_border_shadow'      => 0,
                    'remove_bar_stripes'      => 0,
                    'disable_animation'       => 1,
                    'disabled_animations'     => 'shine',
                    'indicator_icon'          => 0,
                    'indicator_icon_size'     => 22,
                    'indicator_icon_shape'    => 'round',
                    'indicator_icon_bg_color' => '#EC4899',
                    'icon_color'              => '#FFFFFF',
                ],
            ],
            'road'      => [
                'label'  => __( 'Delivery Road', 'free-shipping-label' ),
                'accent' => false,
                'hint'   => __( 'Best with one goal', 'free-shipping-label' ),
                'marker' => true,
                'values' => [
                    'bar_inner_color'         => '#F97316',
                    'bar_bg_color'            => '#334155',
                    'bar_border_color'        => 'rgba(0,0,0,0)',
                    'bar_height'              => 14,
                    'bar_radius'              => 999,
                    'box_bg_color'            => '#FFFFFF',
                    'text_color'              => '#0F172A',
                    'text_size'               => 'medium',
                    'center_text'             => 1,
                    'hide_border_shadow'      => 0,
                    'remove_bar_stripes'      => 1,
                    'disable_animation'       => 1,
                    'disabled_animations'     => 'all',
                    'indicator_icon'          => 0,
                    'indicator_icon_size'     => 16,
                    'indicator_icon_shape'    => 'round',
                    'indicator_icon_bg_color' => '#FFFFFF',
                    'icon_color'              => '#F97316',
                ],
            ],
            'underline' => [
                'label'  => __( 'Underline', 'free-shipping-label' ),
                'accent' => false,
                'hint'   => __( 'Best with one goal', 'free-shipping-label' ),
                'values' => [
                    'bar_inner_color'         => '#111827',
                    'bar_bg_color'            => '#E5E7EB',
                    'bar_border_color'        => 'rgba(0,0,0,0)',
                    'bar_height'              => 5,
                    'bar_radius'              => 0,
                    'box_bg_color'            => '#FFFFFF',
                    'text_color'              => '#111827',
                    'text_size'               => 'medium',
                    'center_text'             => 0,
                    'hide_border_shadow'      => 0,
                    'remove_bar_stripes'      => 1,
                    'disable_animation'       => 1,
                    'disabled_animations'     => 'all',
                    'indicator_icon'          => 0,
                    'indicator_icon_size'     => 22,
                    'indicator_icon_shape'    => 'round',
                    'indicator_icon_bg_color' => '#111827',
                    'icon_color'              => '#FFFFFF',
                ],
            ],
            'theme'     => [
                'label'  => __( 'Theme Match', 'free-shipping-label' ),
                'accent' => false,
                'values' => [
                    'bar_inner_color'         => $theme_color,
                    'bar_bg_color'            => 'color-mix(in srgb, currentColor 15%, transparent)',
                    'bar_border_color'        => 'rgba(0,0,0,0)',
                    'bar_height'              => 10,
                    'bar_radius'              => 999,
                    'box_bg_color'            => '',
                    'text_color'              => '',
                    'text_size'               => 'medium',
                    'center_text'             => 1,
                    'hide_border_shadow'      => 0,
                    'remove_bar_stripes'      => 1,
                    'disable_animation'       => 1,
                    'disabled_animations'     => 'all',
                    'indicator_icon'          => 0,
                    'indicator_icon_size'     => 22,
                    'indicator_icon_shape'    => 'round',
                    'indicator_icon_bg_color' => $theme_color,
                    'icon_color'              => 'var(--wp--preset--color--base, #FFFFFF)',
                ],
            ],
            'gauge'     => [
                'label'         => __( 'Gauge', 'free-shipping-label' ),
                'accent'        => false,
                'hint'          => __( 'A half circle', 'free-shipping-label' ),
                'circular_only' => true,
                'values'        => [
                    'bar_inner_color'         => '#111827',
                    'bar_bg_color'            => '#E5E7EB',
                    'bar_border_color'        => 'rgba(0,0,0,0)',
                    'bar_height'              => 6,
                    'bar_radius'              => 999,
                    'box_bg_color'            => '#FFFFFF',
                    'text_color'              => '#111827',
                    'text_size'               => 'medium',
                    'center_text'             => 1,
                    'hide_border_shadow'      => 0,
                    'remove_bar_stripes'      => 1,
                    'disable_animation'       => 1,
                    'disabled_animations'     => 'all',
                    'indicator_icon'          => 0,
                    'indicator_icon_size'     => 22,
                    'indicator_icon_shape'    => 'round',
                    'indicator_icon_bg_color' => '#111827',
                    'icon_color'              => '#FFFFFF',
                ],
            ],
        ];
        // The circular bar (4.0.0): what a preset writes over its values while the bar is circular, the circle's center,
        // the ring's thickness (in percent of the circle size) and the icon in the center (on the center, not on the
        // ring as the indicator icons are), which shows the icon. Underline has no circular look.
        $circular = [
            'minimal' => ['', 4.5, '#111827'],
            'soft'    => ['', 9, '#7C5CFC'],
            'aurora'  => ['', 8.5, '#6366F1'],
            'glass'   => ['', 8, '#4F46E5'],
            'neon'    => ['', 7.5, '#22D3EE'],
            'steps'   => ['', 10, '#10B981'],
            'bold'    => ['', 12, '#111111'],
            'soft-ui' => ['', 8, '#6C8CFF'],
            'shimmer' => ['', 8.5, '#0EA5E9'],
            'candy'   => ['', 10, '#EC4899'],
            'road'    => ['', 14, '#F97316'],
            'theme'   => ['', 8, $theme_color],
            'gauge'   => ['', 9, '#111827'],
        ];
        foreach ( $circular as $slug => [$circle_bg_color, $ring_thickness, $icon_color] ) {
            $presets[$slug]['circular'] = [
                'circle_bg_color' => $circle_bg_color,
                'ring_thickness'  => $ring_thickness,
                'icon_color'      => $icon_color,
                'inside_circle'   => 'icon',
            ];
        }
        // Each preset's icon (line icons for the light ones, solid for the bold ones), and the texts it's designed
        // with: a preset sets the whole look, the merchant turns parts off afterwards.
        $icons = [
            'minimal'   => 'delivery-truck-10',
            'soft'      => 'delivery-truck-4',
            'aurora'    => 'delivery-truck-8',
            'glass'     => 'delivery-truck-7',
            'neon'      => 'delivery-truck-9',
            'steps'     => 'delivery-truck-1',
            'bold'      => 'delivery-truck-3',
            'soft-ui'   => 'delivery-truck-2',
            'shimmer'   => 'delivery-truck-8',
            'candy'     => 'delivery-truck-6',
            'road'      => 'delivery-truck-4',
            'underline' => 'delivery-truck-10',
            'theme'     => 'delivery-truck-5',
            'gauge'     => 'delivery-truck-3',
        ];
        foreach ( $icons as $slug => $icon ) {
            $presets[$slug]['values'] += [
                'icon'                   => $icon,
                'show_title'             => 1,
                'show_description'       => 1,
                'show_qualified_message' => 1,
            ];
        }
        // What the ring shows besides its colors: Delivery Road's icon riding along, Gauge's half circle.
        $presets['road']['ring'] = [
            'rider' => true,
        ];
        $presets['gauge']['ring'] = [
            'shape' => 'gauge',
        ];
        return $presets;
    }

    /**
     * The slug when it names a preset, '' otherwise (an empty or unknown value means "Custom").
     */
    public static function sanitize( $slug ) {
        $slug = sanitize_key( (string) $slug );
        return ( isset( self::all()[$slug] ) ? $slug : '' );
    }

    /**
     * Whether a preset has a look for this bar type: the ones with circular values for the circular bar, all but the
     * circular only ones (Gauge) for the linear bar.
     *
     * @since	4.0.0
     */
    public static function supports( $slug, $bar_type ) {
        $preset = self::all()[$slug] ?? null;
        if ( !$preset ) {
            return false;
        }
        return ( 'circular' === $bar_type ? isset( $preset['circular'] ) : empty( $preset['circular_only'] ) );
    }

    /**
     * Product Label presets: slug => name, whether Free can choose it, the picker's filters that show it, an
     * optional hint, and the colors it writes into the label settings. Classic ('') is the label without a preset and
     * keeps the merchant's colors.
     *
     * @since	4.0.0
     */
    public static function labels() {
        static $presets = null;
        if ( null !== $presets ) {
            return $presets;
        }
        $preset = function (
            $name,
            $free,
            $tags,
            $text_color,
            $bg_color,
            $hint = ''
        ) {
            return [
                'name'   => $name,
                'free'   => $free,
                'tags'   => $tags,
                'hint'   => $hint,
                'values' => [
                    'text_color'         => $text_color,
                    'bg_color'           => $bg_color,
                    'hide_border_shadow' => 0,
                ],
            ];
        };
        // Where white text sits on a color, the color is dark enough for small text (4.5:1).
        $presets = [
            ''          => [
                'name'   => __( 'Classic', 'free-shipping-label' ),
                'free'   => true,
                'tags'   => ['simple'],
                'hint'   => __( 'Your colors', 'free-shipping-label' ),
                'values' => [],
            ],
            'minimal'   => $preset(
                __( 'Minimal', 'free-shipping-label' ),
                true,
                ['simple'],
                '',
                '',
                __( 'No box, the theme\'s text color', 'free-shipping-label' )
            ),
            'soft'      => $preset(
                __( 'Soft Pill', 'free-shipping-label' ),
                true,
                ['simple'],
                '#5B21B6',
                '#EDE9FE'
            ),
            'outline'   => $preset(
                __( 'Outline', 'free-shipping-label' ),
                true,
                ['simple'],
                '#15803D',
                ''
            ),
            'underline' => $preset(
                __( 'Underline', 'free-shipping-label' ),
                false,
                ['simple'],
                '#111827',
                '#FFFFFF'
            ),
            'mint'      => $preset(
                __( 'Mint', 'free-shipping-label' ),
                false,
                ['simple'],
                '#064E3B',
                '#D1FAE5'
            ),
            'soft-ui'   => $preset(
                __( 'Soft UI', 'free-shipping-label' ),
                false,
                ['simple'],
                '#475569',
                '#E6EBF2',
                __( 'Best on light gray pages', 'free-shipping-label' )
            ),
            'theme'     => $preset(
                __( 'Theme Match', 'free-shipping-label' ),
                false,
                ['simple'],
                self::THEME_COLOR,
                '',
                __( 'The theme\'s color', 'free-shipping-label' )
            ),
            'aurora'    => $preset(
                __( 'Aurora', 'free-shipping-label' ),
                false,
                ['colorful'],
                '#FFFFFF',
                '#4F46E5'
            ),
            'neon'      => $preset(
                __( 'Neon Night', 'free-shipping-label' ),
                false,
                ['colorful'],
                '#22D3EE',
                '#0B1120'
            ),
            'bold'      => $preset(
                __( 'Bold', 'free-shipping-label' ),
                false,
                ['colorful'],
                '#111111',
                '#FACC15'
            ),
            'road'      => $preset(
                __( 'Delivery Road', 'free-shipping-label' ),
                false,
                ['colorful'],
                '#FFFFFF',
                '#334155'
            ),
            'shimmer'   => $preset(
                __( 'Shimmer', 'free-shipping-label' ),
                false,
                ['colorful', 'animated'],
                '#FFFFFF',
                '#0369A1'
            ),
            'candy'     => $preset(
                __( 'Candy', 'free-shipping-label' ),
                false,
                ['colorful', 'animated'],
                '#FFFFFF',
                '#BE185D'
            ),
            'glass'     => $preset(
                __( 'Glass', 'free-shipping-label' ),
                false,
                ['images'],
                '#0F172A',
                'rgba(255,255,255,0.55)',
                __( 'Best over images', 'free-shipping-label' )
            ),
            'tag'       => $preset(
                __( 'Tag', 'free-shipping-label' ),
                false,
                ['shapes'],
                '#FFFFFF',
                '#15803D'
            ),
            'ribbon'    => $preset(
                __( 'Ribbon', 'free-shipping-label' ),
                false,
                ['shapes', 'images'],
                '#FFFFFF',
                '#1D4ED8',
                __( 'Over images, on the edge', 'free-shipping-label' )
            ),
            'seal'      => $preset(
                __( 'Seal', 'free-shipping-label' ),
                false,
                ['shapes', 'images'],
                '#FFFFFF',
                '#B45309',
                __( 'Round over images', 'free-shipping-label' )
            ),
        ];
        return $presets;
    }

    /**
     * A label preset the site can use, '' (Classic) otherwise: an unknown one, or a Pro one without Pro.
     *
     * @since	4.0.0
     */
    public static function sanitize_label( $slug ) {
        $slug = sanitize_key( (string) $slug );
        $presets = self::labels();
        if ( !isset( $presets[$slug] ) ) {
            return '';
        }
        return ( $presets[$slug]['free'] ? $slug : '' );
    }

}
