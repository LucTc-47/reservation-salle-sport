<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../connexion.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}