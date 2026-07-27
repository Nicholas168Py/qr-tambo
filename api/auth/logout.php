<?php
/**
 * QR Tambo - Logout API
 */
require_once __DIR__ . '/../../config/init.php';

session_unset();
session_destroy();

jsonResponse(['success' => true, 'message' => 'Sesión cerrada']);
