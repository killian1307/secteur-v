<?php
// TeamManager.php
require_once 'db.php';

class TeamManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Récupère l'équipe
    public function getOrCreateTeam($userId) {
        // Si l'équipe existe
        $stmt = $this->pdo->prepare("SELECT id FROM teams WHERE user_id = ?");
        $stmt->execute([$userId]);
        $team = $stmt->fetch();

        if (!$team) {
            // Si non, la crée
            $stmt = $this->pdo->prepare("INSERT INTO teams (user_id) VALUES (?)");
            $stmt->execute([$userId]);
        }

        // Retourne l'équipe
        return $this->getTeamDetails($userId);
    }

    // Requête sql pour récupérer les détails de l'équipe (coach + joueurs)
    private function getTeamDetails($userId) {
        $sql = "
        SELECT 
            t.id as team_id, t.formation,
            
            -- Le Coach
            coach.id as coach_id, coach.name_en as coach_name, coach.image_webp as coach_img,
            
            -- Joueur 1 (GK)
            p1.id as slot_1_id, p1.name_en as slot_1_name, p1.image_webp as slot_1_img,
            -- Joueur 2
            p2.id as slot_2_id, p2.name_en as slot_2_name, p2.image_webp as slot_2_img,
            -- Joueur 3
            p3.id as slot_3_id, p3.name_en as slot_3_name, p3.image_webp as slot_3_img,
            -- Joueur 4
            p4.id as slot_4_id, p4.name_en as slot_4_name, p4.image_webp as slot_4_img,
            -- Joueur 5
            p5.id as slot_5_id, p5.name_en as slot_5_name, p5.image_webp as slot_5_img,
            -- Joueur 6
            p6.id as slot_6_id, p6.name_en as slot_6_name, p6.image_webp as slot_6_img,
            -- Joueur 7
            p7.id as slot_7_id, p7.name_en as slot_7_name, p7.image_webp as slot_7_img,
            -- Joueur 8
            p8.id as slot_8_id, p8.name_en as slot_8_name, p8.image_webp as slot_8_img,
            -- Joueur 9
            p9.id as slot_9_id, p9.name_en as slot_9_name, p9.image_webp as slot_9_img,
            -- Joueur 10
            p10.id as slot_10_id, p10.name_en as slot_10_name, p10.image_webp as slot_10_img,
            -- Joueur 11
            p11.id as slot_11_id, p11.name_en as slot_11_name, p11.image_webp as slot_11_img

        FROM teams t
        LEFT JOIN players coach ON t.coach_id = coach.id
        LEFT JOIN players p1 ON t.slot_1 = p1.id
        LEFT JOIN players p2 ON t.slot_2 = p2.id
        LEFT JOIN players p3 ON t.slot_3 = p3.id
        LEFT JOIN players p4 ON t.slot_4 = p4.id
        LEFT JOIN players p5 ON t.slot_5 = p5.id
        LEFT JOIN players p6 ON t.slot_6 = p6.id
        LEFT JOIN players p7 ON t.slot_7 = p7.id
        LEFT JOIN players p8 ON t.slot_8 = p8.id
        LEFT JOIN players p9 ON t.slot_9 = p9.id
        LEFT JOIN players p10 ON t.slot_10 = p10.id
        LEFT JOIN players p11 ON t.slot_11 = p11.id
        WHERE t.user_id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    // Met à jour les slots
    public function updateSlot($userId, $slotName, $playerId) {
        // Vérifie le slot
        $allowedSlots = [
            'coach_id', 'slot_1', 'slot_2', 'slot_3', 'slot_4', 
            'slot_5', 'slot_6', 'slot_7', 'slot_8', 'slot_9', 
            'slot_10', 'slot_11'
        ];

        if (!in_array($slotName, $allowedSlots)) {
            return ['success' => false, 'message' => 'Slot invalide'];
        }

        // Requête dynamique
        $sql = "UPDATE teams SET $slotName = ? WHERE user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        
        // Si pas de joueur
        $val = ($playerId > 0) ? $playerId : null;
        
        if($stmt->execute([$val, $userId])) {
            return $this->getPlayerInfo($val);
        }
        return ['success' => false];
    }

    private function getPlayerInfo($id) {
        if(!$id) return null;
        $stmt = $this->pdo->prepare("SELECT id, name_en, image_webp FROM players WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Rechercher des joueurs par nom
    public function searchPlayers($term) {
        $term = "%$term%";
        $stmt = $this->pdo->prepare("
            SELECT id, name_en, position, element, image_webp 
            FROM players 
            WHERE name_en LIKE ? OR name_jp LIKE ? 
            LIMIT 20
        ");
        $stmt->execute([$term, $term]);
        return $stmt->fetchAll();
    }

    // Mise à jour de la formation
    public function updateFormation($userId, $formation) {
        $stmt = $this->pdo->prepare("UPDATE teams SET formation = ? WHERE user_id = ?");
        return $stmt->execute([$formation, $userId]);
    }
}
?>