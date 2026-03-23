<?php
// assets/grades.php

/* Configuration visuelle d'un grade */
function get_grade_config($grade) {
    // Liste de tous les grades du site
    $grades = [
        'Membre' => [
            'color' => 'var(--text-secondary)',
            'icon'  => '',
            'class' => 'grade-membre'
        ],
        'Vétéran' => [
            // Couleur 
            'color' => '#f36c12', // Orange
            'icon'  => '<i class="fas fa-medal" style="margin-right: 5px;"></i>',
            'class' => 'grade-vip'
        ],
        'VIP' => [
            'color' => '#9b59b6', // Violet
            'icon'  => '<i class="fas fa-star" style="margin-right: 5px;"></i>',
            'class' => 'grade-vip'
        ],
        'Partenaire' => [
            'color' => '#3498db', // Bleu
            'icon'  => '<i class="fas fa-handshake" style="margin-right: 5px;"></i>',
            'class' => 'grade-partenaire'
        ],
        'Modérateur' => [
            'color' => '#2ecc71', // Vert
            'icon'  => '<i class="fas fa-shield-alt" style="margin-right: 5px;"></i>',
            'class' => 'grade-moderateur'
        ],
        'Administrateur' => [
            'color' => '#e74c3c', // Rouge
            'icon'  => '<i class="fas fa-hammer" style="margin-right: 5px;"></i>',
            'class' => 'grade-admin'
        ],
        'Créateur' => [
            'color' => '#f1c40f', // Or
            'icon'  => '<i class="fas fa-crown" style="margin-right: 5px;"></i>',
            'class' => 'grade-createur'
        ]
    ];

    // Membre par défaut
    return $grades[$grade] ?? $grades['Membre'];
}

/* Affiche le pseudo stylisé avec ou sans l'icône */
function display_username($username, $grade, $show_icon = false) {
    $config = get_grade_config($grade);
    
    $html = '<span class="pseudo-stylised ' . $config['class'] . '" style="color: ' . $config['color'] . ';">';
    
    if ($show_icon && !empty($config['icon'])) {
        $html .= $config['icon'];
    }
    
    $html .= htmlspecialchars($username);
    $html .= '</span>';
    
    return $html;
}

/* Badge */
function display_grade_badge($grade) {
    $config = get_grade_config($grade);
    
    return '<span class="badge-stylised ' . $config['class'] . '-badge" style="border: 1px solid ' . $config['color'] . '; color: ' . $config['color'] . ';">' . $config['icon'] . __(htmlspecialchars($grade)) . '</span>';
}
?>