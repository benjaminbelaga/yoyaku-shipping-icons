<?php
// Patch pour exclure logo Delivengo du checkout seulement

// Lecture du fichier original
$content = file_get_contents("yoyaku-shipping-icons.php");

// Recherche de la ligne après le debug log
$search = "    ) );

    // — PATTERNS";

$replace = "    ) );

    // — EXCEPTION DELIVENGO : Logo uniquement dans simulator, PAS sur checkout
    \$is_on_checkout = is_checkout() || is_wc_endpoint_url(\"order-pay\");
    \$method_haystack_early = strtolower(\$method->method_id);
    \$is_delivengo_checkout_exclusion = \$is_on_checkout && (strpos(\$method_haystack_early, \"md_\") !== false);
    if (\$is_delivengo_checkout_exclusion) {
        return \$label; // Retourne le label original sans logo pour Delivengo sur checkout
    }

    // — PATTERNS";

// Remplacement
$content = str_replace($search, $replace, $content);

// Mise à jour version
$content = str_replace("Version:     1.6.1", "Version:     1.6.2", $content);

// Sauvegarde
file_put_contents("yoyaku-shipping-icons.php", $content);

echo "✅ Modification appliquée - v1.6.2\n";
