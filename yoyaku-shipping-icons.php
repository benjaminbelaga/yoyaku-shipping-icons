<?php
/**
 * Plugin Name: Yoyaku Shipping Icons
 * Plugin URI:  https://github.com/benjaminbelaga/yoyaku-shipping-icons
 * Description: Injecte automatiquement un logo devant chaque méthode de livraison WooCommerce (Chronopost, Colissimo, Spring GDS, UPS, FedEx).
 * Version:     1.7.0
 * Author:      Benjamin Belaga
 * Author URI:  https://github.com/benjaminbelaga
 * License:     GPL2+
 * Text Domain: yoyaku-shipping-icons
 */

if ( ! defined( "ABSPATH" ) ) {
    exit; // Exit if accessed directly
}

/**
 * ysl_debug_and_icon()
 *
 * Logge chaque méthode de livraison et injecte un logo adapté devant le label.
 *
 * @param string           $label  Le libellé original rendu par WooCommerce.
 * @param WC_Shipping_Rate $method L\objet méthode de livraison.
 * @return string
 */
function ysl_debug_and_icon( $label, $method ) {
    // — DEBUG : journalise chaque appel de méthode
    error_log( sprintf("[ShippingLabel] label='%s' | id=%s", $label, $method->id) );
    error_log( sprintf(
        "[ShippingMethod] id=%s | title=%s",
        $method->id,
        $method->method_title
    ) );

    // — PATTERNS : mot-clé à repérer dans le titre → image PNG (hauteur 50px)
    // Note: L'ordre est important - les patterns plus spécifiques doivent être en premier
    $patterns = array(
        // FedEx patterns (ajouté v1.7.0)
        "fedex priority express"    => "fedex-logo.png",
        "fedex priority"            => "fedex-logo.png",
        "fedex first"               => "fedex-logo.png",
        "fedex international"       => "fedex-logo.png",
        "fedex regional"            => "fedex-logo.png",
        "fedex ground"              => "fedex-logo.png",
        "fedex express"             => "fedex-logo.png",
        "fedex®"                    => "fedex-logo.png",
        "fedex"                     => "fedex-logo.png",
        // Chronopost & Colissimo
        "chronopost"                => "chronopost-logo.png",
        "colissimo"                 => "colissimo-logo.png",
        // Spring GDS
        "spring gds"                => "spring-gds.png",
        // UPS patterns (spécifiques d'abord)
        "ups worldwide economy"     => "ups-wwe.png",
        "ups worldwide saver"       => "ups-logo.png",
        "ups express"               => "ups-logo.png",
        "ups standard"              => "ups-logo.png",
        "ups"                       => "ups-logo.png",
    );

    // — Normalisation du label : strip_tags, decode entités (dont &nbsp;), unifie tous les blancs, passe en minuscules
    $haystack = strtolower(
        preg_replace(
            "/\s+/u",
            " ",
            html_entity_decode(
                strip_tags( $label ),
                ENT_QUOTES,
                "UTF-8"
            )
        )
    );

    // — Parcours des motifs et injection du logo dès qu\il y a correspondance
    foreach ( $patterns as $needle => $file ) {
        if ( strpos( $haystack, $needle ) !== false ) {
            $img_url = plugin_dir_url( __FILE__ ) . "assets/" . $file;
            $icon    = sprintf(
                "<img src=\"%s\" alt=\"\" style=\"height:50px;margin-right:8px;vertical-align:middle;\">",
                esc_url( $img_url )
            );
            return $icon . $label;
        }
    }

    // Aucun logo trouvé → on renvoie le label sans modification
    return $label;
}

/**
 * ysl_sort_shipping_rates()
 *
 * Trie les méthodes de livraison par prix croissant, garde Pick up en dernier.
 * MODIFICATION: Ne trie QUE si aucune sélection utilisateur n'est active.
 *
 * @param array $rates Tableau des méthodes de livraison.
 * @return array
 */
function ysl_sort_shipping_rates( $rates ) {
    if ( empty( $rates ) ) {
        return $rates;
    }

    // NOUVEAU: Vérifier s'il y a une sélection utilisateur active
    $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
    $is_user_selection_active = false;

    if ( ! empty( $chosen_methods ) ) {
        foreach ( $chosen_methods as $chosen_method ) {
            if ( ! empty( $chosen_method ) && isset( $rates[ $chosen_method ] ) ) {
                $is_user_selection_active = true;
                error_log("[SHIPPING SORT] Sélection utilisateur détectée: " . $chosen_method . " - PAS DE TRI");
                break;
            }
        }
    }

    // Si l'utilisateur a fait une sélection, on ne trie pas
    if ( $is_user_selection_active ) {
        return $rates;
    }

    // DEBUG: Log tous les rates avant tri
    error_log("[SHIPPING SORT] Aucune sélection utilisateur - tri automatique activé");
    error_log("[SHIPPING SORT] Avant tri - " . count($rates) . " méthodes:");
    foreach ( $rates as $id => $rate ) {
        error_log("[SHIPPING SORT] " . $id . " => " . $rate->label . " (cost: " . $rate->cost . ")");
    }

    $pickup = array();
    $others = array();

    foreach ( $rates as $id => $rate ) {
        // Recherche plus flexible pour "Pick up"
        $label_lower = strtolower($rate->label);
        if ( strpos( $label_lower, "pick up" ) !== false || strpos( $label_lower, "pickup" ) !== false || strpos( $label_lower, "retrait" ) !== false ) {
            $pickup[ $id ] = $rate;
            error_log("[SHIPPING SORT] PICKUP trouvé: " . $rate->label);
        } else {
            $others[ $id ] = $rate;
            error_log("[SHIPPING SORT] AUTRE: " . $rate->label . " (cost: " . $rate->cost . ")");
        }
    }

    // Tri par prix croissant
    uasort( $others, function( $a, $b ) {
        return $a->cost <=> $b->cost;
    } );

    // DEBUG: Log le résultat final
    $result = array_merge( $others, $pickup );
    error_log("[SHIPPING SORT] Après tri - ordre final:");
    $i = 1;
    foreach ( $result as $id => $rate ) {
        error_log("[SHIPPING SORT] " . $i . ". " . $rate->label . " (cost: " . $rate->cost . ")");
        $i++;
    }

    // Retourne les méthodes triées + pickup à la fin
    return $result;
}

// On applique la fonction au rendu des méthodes en panier et checkout (divers hooks)
add_filter( "woocommerce_cart_shipping_method_full_label",     "ysl_debug_and_icon", 10, 2 );
add_filter( "woocommerce_checkout_shipping_method_full_label", "ysl_debug_and_icon", 10, 2 );
add_filter( "woocommerce_cart_shipping_method_label",          "ysl_debug_and_icon", 10, 2 );
add_filter( "woocommerce_checkout_shipping_method_label",      "ysl_debug_and_icon", 10, 2 );

// Tri automatique des méthodes de livraison par prix - priorité MAXIMUM
// MODIFICATION: Réduit la priorité pour permettre aux autres plugins de s'exécuter
add_filter( "woocommerce_package_rates", "ysl_sort_shipping_rates", 30 );

// Hook supplémentaire pour forcer le tri au moment du calcul des shipping
// MODIFICATION: Réduit la priorité et ajoute la même logique de préservation
add_filter( "woocommerce_cart_shipping_packages", function($packages) {
    // Vérifier s'il y a une sélection utilisateur active
    $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
    $is_user_selection_active = false;

    if ( ! empty( $chosen_methods ) ) {
        foreach ( $chosen_methods as $chosen_method ) {
            if ( ! empty( $chosen_method ) ) {
                $is_user_selection_active = true;
                break;
            }
        }
    }

    // Si l'utilisateur a fait une sélection, on ne modifie rien
    if ( $is_user_selection_active ) {
        return $packages;
    }

    foreach ($packages as $package_key => $package) {
        if (isset($package["rates"])) {
            $packages[$package_key]["rates"] = ysl_sort_shipping_rates($package["rates"]);
        }
    }
    return $packages;
}, 30 );
