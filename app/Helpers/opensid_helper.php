<?php
/** CI4 port tipis - query OpenSID lewat OpensidModel, bukan mysqli global. */
if (! function_exists('opensid_available')) {
    function opensid_available(): bool {
        try {
            $db = \Config\Database::connect('opensid');
            $db->query('SELECT 1');
            return true;
        } catch (\Throwable $e) { return false; }
    }
}
