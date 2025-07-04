<?php
require_once "config/db.php";

$nom = "Luc";
$email = "lucadmin@gmail.com";
$password = "sport2025";
$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (nom, email, password, role) VALUES (?, ?, ?, 'admin')");
$stmt->execute([$nom, $email, $hashed]);

echo "✅ Administrateur Luc créé avec succès.";

