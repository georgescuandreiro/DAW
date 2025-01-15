<?php
// Pornește sesiunea pentru a accesa sau manipula variabilele existente
session_start();
// Șterge toate variabilele de sesiune, curățând datele salvate
session_unset();
// Distruge sesiunea curentă, eliminând complet datele stocate și terminând sesiunea
session_destroy();
// Redirecționează utilizatorul către pagina de login
header("Location: login");
exit();
