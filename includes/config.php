<?php
// includes/config.php

/**
 * URL de base du service Plats & Utilisateurs.
 */
// URL de base des services mockes.
const API_BASE_PLATS_UTILISATEURS = 'http://localhost:3003';
/**
 * URL de base du service Menus.
 */
const API_BASE_MENUS = 'http://localhost:3004';
/**
 * URL de base du service Commandes.
 */
const API_BASE_COMMANDES = 'http://localhost:3005';

// Endpoints utilises par l'IHM.
/**
 * Endpoint de consultation des plats.
 */
const API_URL_PLATS = API_BASE_PLATS_UTILISATEURS . '/plats';
/**
 * Endpoint de consultation/creation des menus.
 */
const API_URL_MENUS = API_BASE_MENUS . '/menus';
/**
 * Endpoint de creation des commandes.
 */
const API_URL_COMMANDES = API_BASE_COMMANDES . '/commandes';

