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

 function generateTrxId()
{
    return strtoupper(uniqid(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 3) . rand(10000, 99999)));
}
