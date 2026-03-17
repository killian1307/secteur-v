<?php
require 'db.php';

// Charge le .env
try {
    loadEnv(__DIR__ . '/.env');
} catch (Exception $e) {
    die("Erreur de configuration : " . $e->getMessage());
}

$appId = $_ENV['DISCORD_CLIENT_ID'];
$botToken = $_ENV['DISCORD_BOT_TOKEN'];

$commandData = [
    "name" => "resoudre-litige",
    "description" => "Résout un litige de match et donne les points au vainqueur.",
    "default_member_permissions" => "0",
    "options" => [
        [
            "name" => "match_id",
            "description" => "L'ID du match (le numéro dans le nom du salon)",
            "type" => 4, // Type 4 = Integer
            "required" => true
        ],
        [
            "name" => "vainqueur",
            "description" => "Le joueur qui a gagné",
            "type" => 6, // Type 6 = User
            "required" => true
        ],
        [
            "name" => "perdant",
            "description" => "Le joueur qui a perdu",
            "type" => 6, // Type 6 = User
            "required" => true
        ],

        [
            "name" => "score_gagnant",
            "description" => "Le score final du joueur qui a gagné",
            "type" => 4, // Type 4 = Integer
            "required" => true
        ],

        [
            "name" => "score_perdant",
            "description" => "Le score final du joueur qui a perdu",
            "type" => 4, // Type 4 = Integer
            "required" => true
        ]
    ]
];

$ch = curl_init("https://discord.com/api/v10/applications/$appId/commands");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($commandData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bot $botToken",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
curl_close($ch);

echo "Commande créée ! Réponse de Discord : <br>";
echo "<pre>" . print_r(json_decode($response, true), true) . "</pre>";
?>