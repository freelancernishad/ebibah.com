<?php

function calculateAge($dateOfBirth)
{
    if (!$dateOfBirth) {
        return null;
    }

    $birthDate = new \DateTime($dateOfBirth);
    $today = new \DateTime();
    return $today->diff($birthDate)->y;
}
