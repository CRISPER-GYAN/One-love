<?php
// Secure password handling for all user roles
function create_hashed_password($plainPassword) {
    return password_hash($plainPassword, PASSWORD_DEFAULT);
}

function verify_password($plainPassword, $hashedPassword) {
    // Try password_verify first
    if (password_verify($plainPassword, $hashedPassword)) {
        return true;
    }
    // Fallback for legacy md5/sha1 hashes
    if (strlen($hashedPassword) === 32 && md5($plainPassword) === $hashedPassword) {
        return 'upgrade';
    }
    if (strlen($hashedPassword) === 40 && sha1($plainPassword) === $hashedPassword) {
        return 'upgrade';
    }
    return false;
}
